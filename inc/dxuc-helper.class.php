<?php
/**
 * Helper class for the plugin
 * 
 * @since	1.0
 */
class DXUC_Helper {

	/**
	 * Hooked from filter_only_non_replied_comments() in the main class
	 * Get the user ids
	 * 
	 * @since	1.0
	 */
	public static function get_internal_user_ids_list() {
		// Read users list from the database
		$internal_users = get_option( 'dxuc_authors_list', 'admin' );

		// Load internal user IDs
		$internal_user_ids = DXUC_Helper::load_user_ids( $internal_users );

		// Setup the internal users ID list, 0 by default
		if ( ! empty( $internal_user_ids ) ) {
			$internal_user_ids_list = implode( ',', $internal_user_ids );
		}
		if ( empty( $internal_user_ids_list ) ) {
			$internal_user_ids_list = '';
		}

		return $internal_user_ids_list;
	}

	/**
	 * SQL statement for the hook we used
	 * 
	 * @since	1.0
	 */
	public static function get_non_replied_comments( $internal_user_ids_list ) {
		global $wpdb;
		// Get commented IDs where admin has commented
		if ( empty( $internal_user_ids_list ) ) {
			return array();
		}

		$query = "SELECT comment_parent from {$wpdb->prefix}comments WHERE user_id IN ({$internal_user_ids_list}) AND comment_parent != 0";
		$get_comment_parents_by_admin = $wpdb->get_col( $query );
		if ( empty( $get_comment_parents_by_admin ) ) {
			$query = "SELECT comment_parent from {$wpdb->prefix}comments WHERE user_id NOT IN ({$internal_user_ids_list}) AND comment_parent = 0";
		}

		$get_comment_parents_by_admin = $wpdb->get_col( $query );
		if ( empty( $get_comment_parents_by_admin ) ) {
			return array();
		}

		$get_comment_parents_by_admin = apply_filters( 'dxuc_filter_comment_parents_by_admin', $get_comment_parents_by_admin );

		$replied_comments = implode( ',', $get_comment_parents_by_admin );

		// Get all comments that haven't been answered
		$not_spam             = "comment_approved != 'spam'";
		$non_replied_comments = $wpdb->get_col(
			"SELECT comment_ID from {$wpdb->prefix}comments where comment_ID NOT IN($replied_comments) " .
			"AND $not_spam AND user_id NOT IN({$internal_user_ids_list})"
		);

		if ( empty( $non_replied_comments ) ) {
			return array();
		}

		$meta_key = "marked_as_replied";
		foreach( $non_replied_comments as $key => $comment_id ) {

			if ( get_comment_meta( $comment_id, $meta_key, true ) == 1 ) {
				unset($non_replied_comments[$key]);
			}
		}

		return $non_replied_comments;
	}

	/**
	 * Load the user id's after we pulled them
	 * 
	 * @since	1.0
	 */
	public static function load_user_ids( $internal_users ) {
		$user_ids = array();

		$users = explode( ',', $internal_users );

		if ( ! $users ) {
			return $user_ids;
		}

		foreach ( $users as $username ) {
			$user = get_user_by( 'slug', trim( $username ) );
			if ( false !== $user ) {
				$user_ids[] = $user->ID;
			}
		}

		return $user_ids;
	}

	/**
	 * Filter where clause for getting proper comments
	 * 
	 * @since	1.0
	 */
	public static function filter_comments_and_top_sql( $where, $top_level, $non_replied_comments_list, $internal_user_ids_list ) {
		$non_replied_comments_list = apply_filters( 'dxuc_filter_allowed_comment_ids', $non_replied_comments_list );
		$not_spam                  = "comment_approved != 'spam'";

		/**
		 * Make sure that we ilter the email as well
		 * Some comments that has no author email deemed as illegitimate
		 * 
		 * @since	1.5
		 */
		$where .= "$not_spam AND comment_type != 'pingback' AND comment_author_email != '' AND comment_ID IN($non_replied_comments_list) AND user_id NOT IN ({$internal_user_ids_list})";

		if ( $top_level ) {
			$where .= ' AND comment_parent = 0';
		}

		if ( ! empty( $_GET['dxuc_post_id'] ) ) {
			$post_id = (int) $_GET['dxuc_post_id'];

			$where .= " AND comment_post_ID = $post_id";
		}

		$where = apply_filters( 'dxuc_comment_count_top_filter_where', $where );

		return $where;
	}
}
