<?php
/**
 * @since 1.0
 */
class WPBA_ListScreen_Post extends WPBA_ListScreen_Abstract {

	/**
	 * Post type this list screen is attached to
	 *
	 * @var string
	 * @since 1.0
	 */
	protected $post_type;

	/**
	 * Constructor, initializing the list screen and setting the screen ID 
	 *
	 * @since 1.0
	 *
	 * @param string $post_type Post type name
	 */
	public function __construct( $post_type ) {
		$this->post_type = $post_type;

		// WordPress post edit screen IDs is constructed from the "edit-" prefix and the post type
		$this->screen = 'edit-' . $post_type;
	}

	/**
	 * Register BAs default bulk actions for this list screen
	 *
	 * @see WPBA_ListScreen_Abstract::register_default_bulk_actions()
	 * @since 1.0
	 */
	public function register_default_bulk_actions() {
		$this->add_bulkaction( new WPBA_BulkAction_ChangePostType() );
	}

}