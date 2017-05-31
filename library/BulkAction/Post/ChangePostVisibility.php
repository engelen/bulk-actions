<?php
/**
 * @since 1.0
 */
class WPBA_BulkAction_Post_ChangePostVisibility extends WPBA_BulkAction_Abstract {

	/**
	 * Constructor. Initialize bulk action properties
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->label = __( 'Change post visibility...', 'wpba' );
		$this->action = 'change-post-visibility';
	}

	/**
	 * @see WPBA_BulkAction_Abstract::notices()
	 * @since 1.0
	 */
	public function notices( $params ) {
		if ( ! isset( $params['updated'], $params['locked'], $params['post_visibility'] ) ) {
			return;
		}

		$visibility_options = $this->get_visibility_options();
		$post_visibility_label = $visibility_options[ $params['post_visibility'] ];

		$messages = array();
		$messages[] = sprintf( _n( 'Post visibility of %d item changed to "%s".', 'Post visibility of %d items changed to "%s".', $params['updated'], 'wpba' ), $params['updated'], $post_visibility_label );

		if ( $params['locked'] ) {
			$messages[] = sprintf( _n( "Post visibility of %d item not updated, somebody is editing it.", "Post visibility of %d items not updated, somebody is editing them.", $params['locked'], 'wpba' ), $params['locked'], $post_visibility_label );
		}

		echo '<div id="message" class="updated notice is-dismissible"><p>' . implode( ' ', $messages ) . '</p></div>';
	}

	/**
	 * The parameter $additional_params should contain the key "post_visibility" which holds the visibility status which the selected posts should be updated
	 *
	 * @see WPBA_BulkAction_Abstract::handle()
	 * @since 1.0
	 */
	public function handle( $items, $additional_params = array() ) {
		$updated = 0;
		$locked = 0;

		// Verify that a valid post visibility was selected
		$valid_options = $this->get_visibility_options();

		if ( empty( $additional_params['post_visibility'] ) || ! in_array( $additional_params['post_visibility'], array_keys( $valid_options ) ) ) {
			wp_die( __( 'No valid post visibility selected in bulk update.', 'wpba' ) );
		}

		if ( $additional_params['post_visibility'] == 'password' && empty( $additional_params['post_password'] ) ) {
			wp_die( __( 'No valid post password selected in bulk update.', 'wpba' ) );
		}

		foreach ( (array) $items as $item ) {
			if ( ! current_user_can( 'edit_post', $item ) )
				wp_die( __( 'Sorry, you are not allowed to edit this item.' ) );

			if ( wp_check_post_lock( $item ) ) {
				$locked++;
				continue;
			}

			$post_data = array( 'ID' => $item );

			switch ( $additional_params['post_visibility'] ) {
				case 'private':
					$post_data['visibility'] = 'private';
					$post_data['post_status'] = 'private';
					$post_data['sticky'] = false;
					break;
				case 'password':
					$post_data['visibility'] = 'password';
					$post_data['post_password'] = $additional_params['post_password'];
					$post_data['sticky'] = false;
					break;
				case 'public':
					$post_data['visibility'] = 'public';
					$post_data['post_status'] = 'publish';
					$post_data['post_password'] = '';
					break;
			}

			if ( ! wp_update_post( $post_data ) ) {
				wp_die( __( 'Error in updating the post visibility.' ) );
			}

			$updated++;
		}

		return array(
			'updated' => $updated,
			'ids' => implode( ',', $items ),
			'locked' => $locked,
			'post_visibility' => $additional_params['post_visibility']
		);
	}

	/**
	 * Get a list of valid post visibility options
	 *
	 * @since 1.0
	 *
	 * @return array Array of valid post visibility options ([key] => [label])
	 */
	public function get_visibility_options() {
		return array(
			'public' => __( 'Public' ),
			'password' => __( 'Password protected' ),
			'private' => __( 'Private' )
		);
	}

	/**
	 * @since 1.0
	 * @see WPBA_BulkAction_Abstract::template()
	 */
	public function template() {
		$options = $this->get_visibility_options();
		?>
		<select name="<?php echo esc_attr( $this->get_form_element_name( 'post_visibility' ) ); ?>" class="wpba-conditional required highlightable" data-wpba-conditional-group="wpba-post-visibility">
			<option value=""><?php _e( 'None' ); ?></option>
			<?php foreach( $options as $value => $label ): ?>
				<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="text" name="<?php echo esc_attr( $this->get_form_element_name( 'post_password' ) ); ?>" value="" placeholder="<?php esc_attr_e( 'Password' ); ?>" data-wpba-conditional-group="wpba-post-visibility" data-wpba-conditional-value="password" />
		<?php
	}

}