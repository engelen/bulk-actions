<?php
/*
Plugin Name: Advanced Bulk Actions
Description: Supercharge the WordPress admin panel with additional bulk actions to manage your content. Advanced Bulk Actions supports pages, posts and custom post types. Update post status, visibility, featured image and post type for multiple posts at once.
Version: 1.1.2
Author: Jesper van Engelen
Author URI: http://jespervanengelen.com
Text Domain: wpba
License: GPLv2

Copyright 2016	Jesper van Engelen	contact@jepps.nl

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if access directly

define( 'WPBA_VERSION', '1.1.2' );
define( 'WPBA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPBA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class.
 * Singleton.
 *
 * @since 1.0
 */
class WPBA {

	/**
	 * Holds the only instance of this plugin
	 *
	 * @static
	 * @var WPBA
	 * @access private
	 * @since 1.0
	 */
	private static $_instance = NULL;
	
	/**
	 * Plugin version
	 *
	 * @var string
	 * @access protected
	 * @since 1.0
	 */
	protected $version = '1.1.2';

	/**
	 * List screen objects
	 *
	 * @var array[WPBA_ListScreen_Abstract]
	 * @access private
	 * @since 1.0
	 */
	private $_list_screens = array();
	
	/**
	 * Initialize
	 *
	 * @since 1.0
	 */
	private function __construct() {
		// Autoloader
		$this->autoloader();

		// Hooks
		add_action( 'plugins_loaded', array( $this, 'finish_setup' ) );
		add_action( 'init', array( $this, 'localize' ), 3 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Plugin upgrade
		add_action( 'plugins_loaded', array( $this, 'plugin_check_upgrade' ) );
	}

	/**
	 * Get the autoloader instance
	 *
	 * @return WPBA_Autoloader Autoloader class instance
	 */
	public function autoloader() {
		require_once WPBA_PLUGIN_DIR . 'library/Autoloader.php';
		return WPBA_Autoloader::get_instance();
	}

	public function admin_init() {
		// List screens
		if ( is_admin() ) {
			// Register and attach list screens
			$this->register_list_screens();
			$this->attach_list_screens();

			new WPBA_AdminFeedback();
		}
	}
	
	/**
	 * Register all list screens
	 *
	 * @since 1.0
	 */
	public function register_list_screens() {
		$post_types = get_post_types( array( '_builtin' => false, 'show_ui' => true ) );
		$post_types[] = 'post';
		$post_types[] = 'page';

		foreach ( $post_types as $post_type ) {
			$listscreen = new WPBA_ListScreen_Post( $post_type );
			$this->register_list_screen( $listscreen->get_screen_id(), $listscreen );
		}
	}

	/**
	 * Register a single list screen
	 *
	 * @since 1.0
	 *
	 * @param string $name List screen name. Used to find ListScreen class. Examples: "Post", "User"
	 * @param WPBA_ListScreen_Abstract List screen object
	 */
	public function register_list_screen( $name, $listscreen ) {
		$this->_list_screens[ $name ] = $listscreen;
	}

	/**
	 * Attach all list screens to the corresponding list screen pages
	 *
	 * @since 1.0
	 */
	public function attach_list_screens() {
		foreach ( $this->_list_screens as $list_screen ) {
			$list_screen->attach();
		}
	}

	/**
	 * Get the instance of this class, insantiating it if it doesn't exist yet
	 *
	 * @since 1.0
	 *
	 * @return WPBA Class instance
	 */
	public static function get_instance() {
		if ( ! is_object( self::$_instance ) ) {
			self::$_instance = new WPBA();
			self::$_instance->__construct();
		}
		
		return self::$_instance;
	}
	
	/**
	 * Handle localization, loading the plugin textdomain
	 *
	 * @since 1.0
	 */
	public function localize() {
		load_plugin_textdomain( 'wpba', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	
	/**
	 * Handle final aspects of plugin setup, such as adding action hooks
	 *
	 * @since 1.0
	 */
	public function finish_setup() {
		/**
		 * Fires after the plugin was fully set up.
		 *
		 * @since 1.0
		 *
		 * @param WPBA $plugin_instance Main plugin class instance
		 */
		do_action( 'wpba/after_setup', $this );
	}
	
	/**
	 * Handle inital installation and upgrading of the plugin
	 *
	 * @since 1.0
	 */
	public function plugin_check_upgrade() {
		$version = $this->get_version();
		$db_version = get_option( 'wpba_version' );
		
		// First install
		if ( ! $db_version ) {
			// First install

			// Save timestamp at which the plugin was installed (does nothing if it already exists)
			add_option( 'wpba_installed_timestamp', time() );

			// Save version
			add_option( 'wpba_version', $version );

			/**
			 * Fires after the plugin is first installed
			 *
			 * @since 1.1.1
			 */
			do_action( 'wpba/after_install' );
		}
		else {
			// Check whether the plugin has been upgraded
			$difference = version_compare( $db_version, $version );
			
			if ( $difference != 0 ) {
				// Upgrade plugin
				
				// Save new version
				update_option( 'wpba_version', $version );

				/**
				 * Fires after the plugin is upgraded to a newer version.
				 *
				 * @since 1.0
				 *
				 * @param string $old_version Plugin version before the upgrade
				 * @param string $new_version Plugin version after the upgrade
				 */
				do_action( 'wpba/after_upgrade', $db_version, $version );
			}
		}
	}
	
	/**
	 * Get the plugin version
	 *
	 * @since 1.0
	 *
	 * @return string Plugin version
	 */
	public function get_version() {
		return $this->version;
	}

}

WPBA::get_instance();
