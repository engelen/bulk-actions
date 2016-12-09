<?php
/**
 * @since 1.0
 */
abstract class WPBA_BulkAction_Abstract {

	/**
	 * Unique bulk action name to which this bulk action is linked
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $action;

	/**
	 * Label to display in the bulk actions dropdown
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $label;

	/**
	 * Bulk action callback for this specific callback
	 *
	 * @since 1.0
	 *
	 * @param array $items Items to perform the bulk action on
	 * @param array $additional_params Optional. Additional parameters passed in the request. This should be populated with all request variables that have the bulk action's form prefix
	 * @return array Parameters to pass in the redirect as a subarray of the $action GET-parameter
	 */
	abstract public function handle( $items, $additional_params = array() );

	/**
	 * Get the unique bulk action name to which this list table is linked
	 *
	 * @return string Bulk action name
	 */
	public function get_action() {
		return $this->action;
	}

	/**
	 * Get the bulk action label to be displayed in the bulk actions dropdown
	 *
	 * @return string Bulk action label
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Display admin notices related to this bulk action
	 *
	 * @since 1.0
	 *
	 * @param array $params Parameters related to this bulk action. Same as array returned from WPBA_BulkAction_Abstract::handle()
	 */
	public function notices( $params ) {}

	/**
	 * Output the HTML template for the additional settings for this bulk action
	 *
	 * @since 1.0
	 */
	public function template() {}

	/**
	 * Get the form element name with a prefix specific to this column
	 *
	 * @since 1.0
	 *
	 * @param string $name Part of the name to use after the prefix
	 */
	public function get_form_element_name( $name ) {
		return $this->get_form_element_name_prefix() . $name;
	}

	/**
	 * Get the prefix for the name attribute for this bulk action's form elements
	 *
	 * @since 1.0
	 *
	 * @return string Form element name attribute prefix
	 */
	public function get_form_element_name_prefix() {
		return 'wpba-' . $this->action . '-';
	}

}
