<?php
/**
 * A settings page content for the administration menu
 * 
 * @since	1.0
 */
?>
<div class="wrap">
	<?php
		$authors_list  = get_option( 'dxuc_authors_list', 'admin' );
		$comment_count = get_option( 'dxuc_comment_count', false );

	if ( ! empty( $_POST ) ) {
		if ( ! empty( $_POST['dxuc_authors'] ) ) {
			$authors_list = esc_html( $_POST['dxuc_authors'] );

			if ( empty( $authors_list ) ) {
				$authors_list = 'admin';
			}

			update_option( 'dxuc_authors_list', $authors_list );
		}
		if ( ! empty( $_POST['dxuc_comment_count'] ) ) {
			$comment_count = true;
			update_option( 'dxuc_comment_count', 1 );
		} else {
			$comment_count = false;
			update_option( 'dxuc_comment_count', 0 );
		}
	}

	?>

	<h2><?php _e( 'DX Unanswered Comments', 'dx-unanswered-comments' ); ?></h2>

	<p><?php _e( 'Enter the username of the WordPress user who is supposed to reply to commenters.', 'dx-unanswered-comments' ); ?></p>
	<p><?php _e( 'You can several usernames separated by commas.', 'dx-unanswered-comments' ); ?></p>
	<p><?php _e( 'Enable comment count in the top links comment filters if you need it.', 'dx-unanswered-comments' ); ?></p>

	<form method="POST">
		<p>
			<label id="dxuc-authors-label" for="dxuc-authors"><?php _e( 'Authors List', 'dx-unanswered-comments' ); ?></label>
			<input type="text" id="dxuc-authors" name="dxuc_authors" value="<?php echo esc_attr( $authors_list ); ?>" />
		</p>
		<p>
			<label id="dxuc-comment-count-label" for="dxuc-comment-count"><?php _e( 'Comment Count (if enabled adds some extra database load)', 'dx-unanswered-comments' ); ?></label>
			<input type="checkbox" id="dxuc-comment-count" name="dxuc_comment_count" <?php checked( $comment_count, true, true ); ?> />
		</p>
		<p>
			<input type="submit" value="<?php _e( 'Save Users', 'dx-unanswered-comments' ); ?>" />
		</p>
	</form>
</div>
