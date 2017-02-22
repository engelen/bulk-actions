<?php
/**
 * @since 1.0
 */
class WPBA_BulkAction_Post_ChangePostStatus extends WPBA_BulkAction_Abstract {

	/**
	 * Constructor. Initialize bulk action properties
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->label = __( 'Change post status...', 'wpba' );
		$this->action = 'change-post-status';
	}

	/**
	 * @see WPBA_BulkAction_Abstract::notices()
	 * @since 1.0
	 */
	public function notices( $params ) {
		if ( ! isset( $params['updated'], $params['locked'], $params['post_status'] ) ) {
			return;
		}

		$post_status_label = get_post_status_object( $params['post_status'] )->label;

		$messages = array();
		$messages[] = sprintf( _n( 'Post status of %d item changed to "%s".', 'Post status of %d items changed to "%s".', $params['updated'], 'wpba' ), $params['updated'], $post_status_label );

		if ( $params['locked'] ) {
			$messages[] = sprintf( _n( "Post status of %d item not updated, somebody is editing it.", "Post status of %d items not updated, somebody is editing them.", $params['locked'], 'wpba' ), $params['locked'], $post_status_label );
		}

		echo '<div id="message" class="updated notice is-dismissible"><p>' . implode( ' ', $messages ) . '</p></div>';
	}

	/**
	 * The parameter $additional_params should contain the key "post_status" which holds the name of the post status to which the selected posts should be updated
	 *
	 * @see WPBA_BulkAction_Abstract::handle()
	 * @since 1.0
	 */
	public function handle( $items, $additional_params = array() ) {
		$updated = 0;
		$locked = 0;

		if ( empty( $additional_params['post_status'] ) ) {
			wp_die( __( 'No post status selected in bulk update.', 'wpba' ) );
		}

		foreach ( (array) $items as $item ) {
			if ( ! current_user_can( 'edit_post', $item ) )
				wp_die( __( 'Sorry, you are not allowed to edit this item.' ) );

			if ( wp_check_post_lock( $item ) ) {
				$locked++;
				continue;
			}

			if ( ! wp_update_post( array( 'ID' => $item, 'post_status' => $additional_params['post_status'] ) ) ) {
				wp_die( __( 'Error in updating the post status.' ) );
			}

			$updated++;
		}

		return array(
			'updated' => $updated,
			'ids' => implode( ',', $items ),
			'locked' => $locked,
			'post_status' => $additional_params['post_status']
		);
	}

	/**
	 * @since 1.0
	 * @see WPBA_BulkAction_Abstract::template()
	 */
	public function template() {
		// Populate post status options
		$post_statuses = get_post_stati( array( 'internal' => false, 'private' => false ), 'objects' );
		unset( $post_statuses['future'] );

		foreach ( $post_statuses as $post_status => $post_status_object ) {
			$options[ $post_status ] = $post_status_object->label;
		}

		?>
		<select name="<?php echo esc_attr( $this->get_form_element_name( 'post_status' ) ); ?>" class="required highlightable">
			<option value=""><?php _e( 'None' ); ?></option>
			<?php foreach( $options as $value => $label ): ?>
				<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

}