<?php
/**
 * @since 1.0
 */
class WPBA_BulkAction_ChangePostType extends WPBA_BulkAction_Abstract {

	/**
	 * Post type the items should be changed to in this bulk action
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $post_type;

	/**
	 * Constructor. Initialize bulk action properties
	 *
	 * @since 1.0
	 *
	 * @param $post_type string Post type to change the current post to
	 */
	public function __construct( $post_type ) {
		$this->label = sprintf( __( 'Change post type to "%s"', 'wpba' ), get_post_type_object( $post_type )->labels->singular_name );
		$this->action = 'change-post-type-' . $post_type;
		$this->post_type = $post_type;
	}

	/**
	 * @see WPBA_BulkAction_Abstract::notices()
	 * @since 1.0
	 */
	public function notices( $params ) {
		if ( ! isset( $params['updated'] ) || ! isset( $params['locked'] ) ) {
			return;
		}

		$post_type_label = get_post_type_object( $this->post_type )->labels->singular_name;

		$messages = array();
		$messages[] = sprintf( _n( 'Post type of %d item changed to "%s".', 'Post type of %d items changed to "%s".', $params['updated'], 'wpba' ), $params['updated'], $post_type_label );

		if ( $params['locked'] ) {
			$messages[] = sprintf( _n( "Post type of %d item not updated, somebody is editing it.", "Post type of %d items not updated, somebody is editing them.", $params['locked'], 'wpba' ), $params['locked'], $post_type_label );
		}

		echo '<div id="message" class="updated notice is-dismissible"><p>' . implode( ' ', $messages ) . '</p></div>';
	}

	/**
	 * @see WPBA_BulkAction_Abstract::handle()
	 * @since 1.0
	 */
	public function handle( $items ) {
		$updated = 0;
		$locked = 0;

		foreach ( (array) $items as $item ) {
			if ( ! current_user_can( 'edit_post', $item ) )
				wp_die( __( 'Sorry, you are not allowed to edit this item.' ) );

			if ( wp_check_post_lock( $item ) ) {
				$locked++;
				continue;
			}

			if ( ! wp_update_post( array( 'ID' => $item, 'post_type' => $this->post_type ) ) ) {
				wp_die( __( 'Error in updating the post type.' ) );
			}

			$updated++;
		}

		return array(
			'updated' => $updated,
			'ids' => implode( ',', $items ),
			'locked' => $locked
		);
	}

}