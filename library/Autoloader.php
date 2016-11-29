<?php
/**
 * Singleton.
 *
 * @since 1.0
 */
class WPBA_Autoloader {

	/**
	 * Holds the only instance of this class
	 *
	 * @static
	 * @var WPBA
	 * @access private
	 * @since 1.0
	 */
	private static $_instance = NULL;

	/**
	 * Constructor. Register autoloader.
	 *
	 * @access private
	 * @since 1.0
	 */
	private function __construct() {
		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/**
	 * Get the instance of this class, insantiating it if it doesn't exist yet
	 *
	 * @since 1.0
	 *
	 * @return WPBA_Autoloader Class instance
	 */
	public static function get_instance() {
		if ( ! is_object( self::$_instance ) ) {
			self::$_instance = new WPBA_Autoloader();
			self::$_instance->__construct();
		}
		
		return self::$_instance;
	}

	/**
	 * @since 1.0
	 */
	public function autoload( $class_name ) {
		$prefix = 'WPBA_';

		if ( false !== strpos( $class_name, $prefix ) ) {
			$classes_dir = WPBA_PLUGIN_DIR . 'library' . DIRECTORY_SEPARATOR;
			$class_file = str_replace( '_', DIRECTORY_SEPARATOR, substr( $class_name, strlen( $prefix ) ) ) . '.php';
			$file_path = $classes_dir . $class_file;

			if ( is_readable( $file_path ) ) {
				require_once $file_path;
			}
		}
	}

}
