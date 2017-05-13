<?php
/**
 * Admin class for gathering feedback from users.
 *
 * @since 1.1.1
 */
class WPBA_AdminFeedback {

	/**
	 * Constructor. Register actions
	 *
	 * @since 1.1.1
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'feedback_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'wp_ajax_wpba/dismiss_notice', array( $this, 'ajax_dismiss_notice' ) );
	}

	/**
	 * Check whether the feedback notice should be displayed
	 *
	 * @since 1.1.1
	 *
	 * @return bool Whether the feedback notice should be displayed
	 */
	public function is_feedback_notice_active() {
		$plugin_installed = get_option( 'wpba_installed_timestamp' );

		if ( $plugin_installed && ( time() - $plugin_installed ) <= 60 * 60 * 24 * 7 ) {
			return false;
		}

		return ! get_user_meta( get_current_user_id(), 'wpba/notice_disabled/feedback', true );
	}

	/**
	 * If the user has been using the plugin long enough, and the notice hasn't been disabled by the
	 * user already, display a notice for leaving feedback.
	 *
	 * @since 1.1.1
	 */
	public function feedback_notice() {
		if ( ! $this->is_feedback_notice_active() ) {
			return;
		}

		$username = get_user_meta( get_current_user_id(), 'first_name', true );
		?>
		<div id="wpba-notice-feedback" class="notice notice-info is-dismissible">
			<p>
				<?php printf( __( "Hey%s! We're just a small plugin, and we need your help! You've been using the <em>Advanced Bulk Actions</em> plugin for some time now, and we were wondering whether you had any suggestions for bulk actions to add. We'd really appreciate it if you could send us you're feedback!", 'wpba' ), ( $username ? ( ' ' . $username ) : '' ) ); ?>
			</p>
			<p>
				<a href="https://wordpress.org/support/plugin/bulk-actions#new-post" title="<?php esc_attr_e( 'Send us your feedback!', 'wpba' ); ?>" class="button button-secondary" target="_blank"><?php _e( 'Send us your feedback!', 'wpba' ); ?></a> <?php _e( '(opens in a new tab)', 'wpba' ); ?> <?php printf( __( 'or %s.', 'wpba' ), '<a href="#" class="hide">' . __( 'hide this message forever', 'wpba' ) . '</a>' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Register and enqueue admin scripts
	 *
	 * @since 1.1.1
	 */
	public function scripts() {
		if ( $this->is_feedback_notice_active() ) {
			wp_enqueue_script( 'wpba-feedback', WPBA_PLUGIN_URL . 'assets/js/feedback.js', array( 'jquery' ) );
		}
	}

	/**
	 * Handle the AJAX request for permanently dismissing the feedback notice
	 *
	 * @since 1.1.1
	 */
	public function ajax_dismiss_notice() {
		$notice = $_REQUEST['notice_id'];

		if ( $notice === 'feedback' ) {
			update_user_meta( get_current_user_id(), 'wpba/notice_disabled/feedback', true );
		}

		wp_die();
	}

}
