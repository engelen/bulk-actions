<?php
/**
 * @since 1.0
 */
abstract class WPBA_ListScreen_Abstract {

	/**
	 * Name of the screen ID to which this class is linked
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $screen;

	/**
	 * Bulk action objects
	 *
	 * @var array[WPBA_BulkAction_Abstract]
	 * @access private
	 * @since 1.0
	 */
	private $_bulk_actions = array();

	/**
	 * Register BAs default bulk actions for this list screen
	 * @since 1.0
	 */
	abstract protected function register_default_bulk_actions();

	/**
	 * Attach the class to the list table class, i.e. add the relevant filters
	 *
	 * @since 1.0
	 */
	public function attach() {
		$this->register_default_bulk_actions();

		// Hooks
		add_filter( "bulk_actions-{$this->screen}", array( $this, 'filter_bulk_actions' ) );
		add_filter( "handle_bulk_actions-{$this->screen}", array( $this, 'handle_bulk_actions' ), 10, 3 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'templates' ) );
	}

	/**
	 * Filter the bulk actions for the list screen
	 *
	 * @see filter:bulk_actions-{screen_id}
	 * @since 1.0
	 */
	public function filter_bulk_actions( $actions ) {
		foreach ( $this->_bulk_actions as $bulk_action ) {
			$actions[ $bulk_action->get_action() ] = $bulk_action->get_label();
		}

		return $actions;
	}

	/**
	 * Handle the bulk action, calling the corresponding bulk action (if any) of this list screen method
	 *
	 * @see filter:handle_bulk_actions-{screen_id}
	 * @since 1.0
	 */
	public function handle_bulk_actions( $redirect_to, $action, $items ) {
		if ( isset( $this->_bulk_actions[ $action ] ) ) {
			$bulk_action = $this->_bulk_actions[ $action ];

			// Extract additional parameters specific to this bulk action from the request
			$additional_params = array();
			$prefix = $bulk_action->get_form_element_name_prefix();

			foreach ( $_REQUEST as $key => $value ) {
				if ( substr( $key, 0, strlen( $prefix ) ) == $prefix ) {
					$additional_params[ substr( $key, strlen( $prefix ) ) ] = $value;
				}
			}

			// Let the bulk action handle the execution
			$location_params = $bulk_action->handle( $items, $additional_params );

			// Redirect to the results page
			$query_args = array( $action => $location_params );
			$redirect_to = add_query_arg( $query_args, $redirect_to );
		}

		return $redirect_to;
	}

	/**
	 * Display admin notices for this list screen's custom bulk actions
	 *
	 * @since 1.0
	 */
	public function admin_notices() {
		if ( ! $this->is_current_screen() ) {
			return;
		}

		foreach ( $this->_bulk_actions as $bulk_action ) {
			$action = $bulk_action->get_action();
			$params = isset( $_GET[ $action ] ) ? $_GET[ $action ] : array();
			$bulk_action->notices( $params );
		}
	}

	/**
	 * Get the screen ID to which this list table is linked
	 *
	 * @since 1.0
	 *
	 * @return string Screen ID
	 */
	public function get_screen_id() {
		return $this->screen;
	}

	/**
	 * Whether the current list screen is this list screen
	 *
	 * @return bool True if we're on the list screen associated with this class, false otherwise
	 */
	public function is_current_screen() {
		return get_current_screen()->id == $this->screen;
	}

	/**
	 * Register a bulk action to this list screen
	 *
	 * @since 1.0
	 */
	public function add_bulkaction( $bulkaction ) {
		$this->_bulk_actions[ $bulkaction->get_action() ] = $bulkaction;
	}

	/**
	 * Register and enqueue admin scripts
	 *
	 * @since 1.0
	 */
	public function scripts() {
		if ( $this->is_current_screen() ) {
			wp_enqueue_script( 'wpba-admin', WPBA_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ) );
			wp_enqueue_style( 'wpba-admin', WPBA_PLUGIN_URL . 'assets/css/admin.css' );
		}

		// Localize script
		$defaults = array(
			'i18n' => array()
		);

		wp_localize_script( 'wpba-admin', 'WPBA', array_merge_recursive( $defaults, $this->get_javascript_parameters() ) );
	}

	/**
	 * Get parameters to be passed to the admin JavaScript through wp_localize_script
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_javascript_parameters() {
		return array();
	}

	/**
	 * Output bulk actions' HTML templates for use in JavaScript
	 *
	 * @since 1.0
	 */
	public function templates() {
		if ( $this->is_current_screen() ) {
			foreach ( $this->_bulk_actions as $bulkaction ) {
				$this->bulkaction_template( $bulkaction );
			}
		}
	}

	/**
	 * Output the template for a single bulk action
	 *
	 * @since 1.0
	 * 
	 * @param WPBA_BulkAction_Abstract $bulkaction Bulk action object
	 */
	public function bulkaction_template( $bulkaction ) {
		ob_start();
		$bulkaction->template();
		$output = ob_get_clean();

		if ( ! $output ) {
			return;
		}

		echo '<script type="text/html" class="wpba-template" id="wpba-template-' . esc_attr( $bulkaction->get_action() ) . '">';
		echo $output;
		echo '</script>';
	}

}
