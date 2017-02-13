<?php
/**
 * @since 1.0
 */
class WPBA_BulkAction_Post_ChangeFeaturedImage extends WPBA_BulkAction_Abstract {

	/**
	 * Constructor. Initialize bulk action properties
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->label = __( 'Change featured image...', 'wpba' );
		$this->action = 'change-featured-image';
	}

	/**
	 * @see WPBA_BulkAction_Abstract::notices()
	 * @since 1.0
	 */
	public function notices( $params ) {
		if ( ! isset( $params['updated'], $params['locked'], $params['featured_image'] ) ) {
			return;
		}

		// Display featured image or, if it cannot be found for some reason, the original input (i.e. the attachment ID)
		$image_src = wp_get_attachment_image_src( $params['featured_image'] );

		if ( $image_src ) {
			$featured_image_element = '<span class="wpba-featured-image"><img src="' . esc_attr( $image_src[0] ) . '" /></span>';
		}
		else {
			$featured_image_element = esc_html( $params['featured_image'] );
		}

		$messages = array();
		$messages[] = sprintf( _n( 'Featured image of %d item changed to: %s', 'Featured image of %d items changed to: %s', $params['updated'], 'wpba' ), $params['updated'], $featured_image_element );

		if ( $params['locked'] ) {
			$messages[] = sprintf( _n( "Featured image of %d item not updated, somebody is editing it.", "Featured image of %d items not updated, somebody is editing them.", $params['locked'], 'wpba' ), $params['locked'] );
		}

		echo '<div id="message" class="updated notice is-dismissible"><p>' . implode( ' ', $messages ) . '</p></div>';
	}

	/**
	 * The parameter $additional_params should contain the key "featured_image" which holds the featured image ID which should be assigned to the selected posts
	 *
	 * @see WPBA_BulkAction_Abstract::handle()
	 * @since 1.0
	 */
	public function handle( $items, $additional_params = array() ) {
		$updated = 0;
		$locked = 0;

		if ( empty( $additional_params['featured_image'] ) ) {
			wp_die( __( 'No featured image selected in bulk update.', 'wpba' ) );
		}

		foreach ( (array) $items as $item ) {
			if ( ! current_user_can( 'edit_post', $item ) )
				wp_die( __( 'Sorry, you are not allowed to edit this item.' ) );

			if ( wp_check_post_lock( $item ) ) {
				$locked++;
				continue;
			}

			// Update the featured image. If this returns false, it might be the case that the old thumbnail ID is the same as the new thumbnail ID, due
			// to update_metadata, which is called indirectly from set_post_thumbnail, returning boolean false if the new thumbnail ID is the same as the
			// old thumbnail ID. https://core.trac.wordpress.org/ticket/21900
			if ( set_post_thumbnail( $item, $additional_params['featured_image'] ) === false ) {
				$current_thumbnail = intval( get_post_thumbnail_id( $item ) );

				// Only show an error if something really went wrong (and not in the case described above)
				if ( ! $current_thumbnail || $current_thumbnail != $additional_params['featured_image'] ) {
					wp_die( __( 'Error in updating the featured image.' ) );
				}
			}

			$updated++;
		}

		return array(
			'updated' => $updated,
			'ids' => implode( ',', $items ),
			'locked' => $locked,
			'featured_image' => $additional_params['featured_image']
		);
	}

	/**
	 * @since 1.0
	 * @see WPBA_BulkAction_Abstract::template()
	 */
	public function template() {
		?>
		<a href="#" class="wpba-select-image button highlightable"><?php _e( 'Select image', 'wpba' ); ?></a>
		<div class="wpba-current-image"></div>
		<input type="hidden" name="<?php echo esc_attr( $this->get_form_element_name( 'featured_image' ) ); ?>" class="wpba-input" value="" />
		<?php
	}

}