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
			$location_params = $this->_bulk_actions[ $action ]->handle( $items );

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
		if ( ! get_current_screen()->id == $this->screen ) {
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
	 * Register a bulk action to this list screen
	 *
	 * @since 1.0
	 */
	public function add_bulkaction( $bulkaction ) {
		$this->_bulk_actions[ $bulkaction->get_action() ] = $bulkaction;
	}

}
