<?php
/**
 * @since 1.0
 */
class WPBA_BulkAction_Post_ChangePostType extends WPBA_BulkAction_Abstract {

	/**
	 * Constructor. Initialize bulk action properties
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->label = __( 'Change post type...', 'wpba' );
		$this->action = 'change-post-type';
	}

	/**
	 * @see WPBA_BulkAction_Abstract::notices()
	 * @since 1.0
	 */
	public function notices( $params ) {
		if ( ! isset( $params['updated'], $params['locked'], $params['post_type'] ) ) {
			return;
		}

		$post_type_label = get_post_type_object( $params['post_type'] )->labels->singular_name;

		$messages = array();
		$messages[] = sprintf( _n( 'Post type of %d item changed to "%s".', 'Post type of %d items changed to "%s".', $params['updated'], 'wpba' ), $params['updated'], $post_type_label );

		if ( $params['locked'] ) {
			$messages[] = sprintf( _n( "Post type of %d item not updated, somebody is editing it.", "Post type of %d items not updated, somebody is editing them.", $params['locked'], 'wpba' ), $params['locked'], $post_type_label );
		}

		echo '<div id="message" class="updated notice is-dismissible"><p>' . implode( ' ', $messages ) . '</p></div>';
	}

	/**
	 * The parameter $additional_params should contain the key "post_type" which holds the name of the post type to which the selected posts should be updated
	 *
	 * @see WPBA_BulkAction_Abstract::handle()
	 * @since 1.0
	 */
	public function handle( $items, $additional_params = array() ) {
		$updated = 0;
		$locked = 0;

		if ( empty( $additional_params['post_type'] ) ) {
			wp_die( __( 'No post type selected in bulk update.', 'wpba' ) );
		}

		foreach ( (array) $items as $item ) {
			if ( ! current_user_can( 'edit_post', $item ) )
				wp_die( __( 'Sorry, you are not allowed to edit this item.' ) );

			if ( wp_check_post_lock( $item ) ) {
				$locked++;
				continue;
			}

			if ( ! wp_update_post( array( 'ID' => $item, 'post_type' => $additional_params['post_type'] ) ) ) {
				wp_die( __( 'Error in updating the post type.' ) );
			}

			$updated++;
		}

		return array(
			'updated' => $updated,
			'ids' => implode( ',', $items ),
			'locked' => $locked,
			'post_type' => $additional_params['post_type']
		);
	}

	/**
	 * @since 1.0
	 * @see WPBA_BulkAction_Abstract::template()
	 */
	public function template() {
		// Populate post type options
		$post_types = get_post_types( array( '_builtin' => false, 'show_ui' => true ) );
		$post_types[] = 'post';
		$post_types[] = 'page';

		foreach ( $post_types as $post_type ) {
			// Don't add the option to change the post type to the current post type
			if ( $post_type == get_post_type() ) {
				continue;
			}

			$options[ $post_type ] = get_post_type_object( $post_type )->labels->singular_name;
		}

		?>
		<select name="<?php echo esc_attr( $this->get_form_element_name( 'post_type' ) ); ?>" class="required highlightable">
			<option value=""><?php _e( 'None' ); ?></option>
			<?php foreach( $options as $value => $label ): ?>
				<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

}