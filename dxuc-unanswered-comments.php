<?php
/**
 * Plugin Name: DX Unanswered Comments
 * Description: Filter your admin comments that have not received a reply by internal user yet.
 * Author: nofearinc
 * Author URI: http://devwp.eu/
 * Version: 1.6
 * License: GPL2+
 * Text Domain: dx-unanswered-comments
 *
 */

/**
 * Main class for our plugin
 * 
 * @since	1.0
 */
class DX_Unanswered_Comments {

	/**
	 * Class constructor
	 * All action and filter hook will based as early as constructor class initiated
	 * 
	 * @since	1.0
	 */
	public function __construct() {
		$this->setup();
		add_action( 'admin_enqueue_scripts', array( $this, 'add_top_active_link_script' ) );
		add_action( 'admin_menu', array( $this, 'add_non_replied_comments_plugin_page' ) );
		add_filter( 'views_edit-comments', array( $this, 'filter_comment_top_links' ) );
		add_filter( 'comments_clauses', array( $this, 'filter_only_non_replied_comments' ) );
		add_filter( 'init', array( $this, 'load_textdomain' ) );
		add_filter( 'manage_edit-comments_columns', array( $this, 'add_comments_column' ) );
		add_filter( 'manage_comments_custom_column', array( $this, 'add_comments_button' ), 10, 2 );
		add_action( 'wp_ajax_mark_comment_as_replied', array( $this, 'mark_comment_as_replied' ) );
		add_action( 'wp_ajax_nopriv_mark_comment_as_replied', array( $this, 'mark_comment_as_replied' ) );
		add_action( 'wp_ajax_mark_comment_as_non_replied', array( $this, 'mark_comment_as_non_replied' ) );
		add_action( 'wp_ajax_nopriv_mark_comment_as_non_replied', array( $this, 'mark_comment_as_non_replied' ) );
	}

	/**
	 * Load the text domain for plugin's translation
	 * 
	 * @since	1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'dx-unanswered-comments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Initial setup class
	 * 
	 * @since	1.0
	 */
	public function setup() {
		include_once plugin_dir_path( __FILE__ ) . '/inc/dxuc-helper.class.php';
	}

	/**
	 * Hook into the top links of the Comment page
	 * 
	 * @since	1.0
	 */
	public function filter_comment_top_links( $views ) {
		$dxuc_comment_count = get_option( 'dxuc_comment_count', false );

		if ( ! empty( $dxuc_comment_count ) ) {
			include_once plugin_dir_path( __FILE__ ) . '/inc/dxuc-add-comment-count-top.php';
		}
		$non_replied_text = apply_filters( 'dxuc_non_replied_text', __( 'Non-replied', 'dx-unanswered-comments' ) );
		$non_replied_root = apply_filters( 'dxuc_non_replied_top_level', __( 'Non-replied - Top Level', 'dx-unanswered-comments' ) );

		$views['non-replied']      = "<a href='edit-comments.php?comment_status=non_replied'>$non_replied_text</a>";
		$views['non-replied-root'] = "<a href='edit-comments.php?comment_status=non_replied&top_level=true'>$non_replied_root</a>";

		return $views;
	}

	/**
	 * Hooking on the comment clause
	 * This will hook to the SQL in pulling the comments data
	 * 
	 * @since	1.0
	 */
	public function filter_only_non_replied_comments( $clauses ) {
		global $current_user;

		if ( is_admin() && ! empty( $_GET['comment_status'] ) && $_GET['comment_status'] === 'non_replied' ) {
			// get all needed posts (as comment__in but it doesn't exist yet)
			global $wpdb;

			// Get the IDs for admin users that are supposed to reply
			$internal_user_ids_list = DXUC_Helper::get_internal_user_ids_list();
			if ( empty( $internal_user_ids_list ) ) {
				return $clauses;
			}

			// Get non-replied comment IDs array
			$non_replied_comments = DXUC_Helper::get_non_replied_comments( $internal_user_ids_list );

			if ( empty( $non_replied_comments ) ) {
				$clauses['where'] = '1 = 0'; // $clauses;
				return $clauses;
			}

			$non_replied_comments_list = implode( ',', $non_replied_comments );
			// add it to the where clauses
			$where = $clauses['where'];

			if ( ! empty( $where ) ) {
				$where .= ' AND ';
			}

			$top_level = false;
			if ( ! empty( $_GET['top_level'] ) && 'true' === $_GET['top_level'] ) {
				$top_level = true;
			}

			// Filter where clause for getting proper comments
			$where = DXUC_Helper::filter_comments_and_top_sql( $where, $top_level, $non_replied_comments_list, $internal_user_ids_list );

			$clauses['where'] = apply_filters( 'dxuc_comments_filter_where', $where );
		}

		return $clauses;
	}

	/**
	 * Hook a new custo page on the administrator menu
	 * 
	 * @since	1.0
	 */
	public function add_non_replied_comments_plugin_page() {
		add_submenu_page(
			'options-general.php', __( 'DX Unanswered Comments', 'dx-unanswered-comments' ),
			__( 'DX Unanswered Comments', 'dx-unanswered-comments' ), 'manage_options', 'dx-unanswered-comments',
			array( $this, 'add_plugin_menu_page_callback' )
		);
	}

	/**
	 * A callback function for the submenu page
	 * This is where we display the content of `DX Unanswered Comments` settings page
	 * 
	 * @since	1.0
	 */
	public function add_plugin_menu_page_callback() {
		include_once 'dxuc-unanswered-comments-admin-page.php';
	}

	/**
	 * Assets for the administrator page
	 * This will be on the edit comment page
	 * 
	 * @since	1.0
	 */
	public function add_top_active_link_script( $hook ) {
		if ( 'edit-comments.php' === $hook ) {
			wp_enqueue_script( 'dxuc-script', plugin_dir_url( __FILE__ ) . '/js/dxuc-script.js' );
			wp_enqueue_script( 'dxuc-comments', plugin_dir_url( __FILE__ ) . '/js/dxuc-comments.js', array( 'jquery' ) );
			wp_enqueue_style( 'dxuc-style', plugin_dir_url( __FILE__ ) . '/css/dxuc-style.css' );
		}
	}

	function add_comments_column( $columns ) {
		$columns['mark_as_replied_column'] = __( 'Mark as replied' );
		return $columns;
	}

	function add_comments_button( $column, $comment_ID ) {
		$meta_key = "marked_as_replied";
		
		if ( 'mark_as_replied_column' == $column ) {
			if ( get_comment_meta( $comment_ID, $meta_key, true ) == 1 ) {
				echo '<a class="mark_as_non_replied" href="#" data-value="' . $comment_ID . '" >Mark as non-replied</a>';
			} else {
				echo '<a class="mark_as_replied" href="#" data-value="' . $comment_ID . '" >Mark as replied</a>';
			}
		}
	}

	public function mark_comment_as_replied() {
		$comment_id = sanitize_text_field( $_POST['selected_comment_id'] );
		$meta_key = "marked_as_replied";
		
		if ( get_comment_meta( $comment_id, $meta_key, true ) == 0) {
			update_comment_meta( $comment_id, $meta_key, 1);
		} elseif ( empty( get_comment_meta( $comment_id, $meta_key, true ) ) ) {
			add_comment_meta( $comment_id, $meta_key, 1 );
		}

		wp_die();
	}

	public function mark_comment_as_non_replied() {
		$comment_id = sanitize_text_field( $_POST['selected_comment_id'] );
		$meta_key = "marked_as_replied";

		if ( get_comment_meta( $comment_id, $meta_key, true ) == 1) {
			update_comment_meta( $comment_id, $meta_key, 0);
		}

		wp_die();
	}
}

new DX_Unanswered_Comments();
