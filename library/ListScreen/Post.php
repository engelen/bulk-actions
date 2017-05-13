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
	 * @see WPBA_ListScreen_Abstract::register_default_bulk_actions()
	 * @since 1.0
	 */
	public function register_default_bulk_actions() {
		$this->add_bulkaction( new WPBA_BulkAction_Post_ChangePostType() );
		$this->add_bulkaction( new WPBA_BulkAction_Post_ChangePostStatus() );
		$this->add_bulkaction( new WPBA_BulkAction_Post_ChangePostVisibility() );

		if ( post_type_supports( $this->post_type, 'thumbnail' ) ) {
			$this->add_bulkaction( new WPBA_BulkAction_Post_ChangeFeaturedImage() );
		}
	}

	/**
	 * @see WPBA_ListScreen_Abstract::scripts()
	 * @since 1.0
	 */
	public function scripts() {
		if ( $this->is_current_screen() ) {
			wp_enqueue_media();
		}

		parent::scripts();
	}

	/**
	 * @see WPBA_ListScreen_Abstract::get_javascript_parameters()
	 * @since 1.0
	 */
	public function get_javascript_parameters() {
		return array(
			'i18n' => array(
				'change_featured_image_title' => __( 'Bulk Action: Featured Image for Selected Posts', 'wpba' )
			)
		);
	}

}