<?php
/**
 * Plugin Name: InstaGo
 * Plugin URI:  https://pluginize.com
 * Description: Easily send your social media traffic to your freshest content
 * Version:     1.0.1
 * Author:      Pluginize
 * Author URI:  https://pluginize.com
 * License:     GPLv2
 * Text Domain: instago
 * Domain Path: /languages
 *
 * @link https://pluginize.com
 *
 * @package InstaGo
 * @version 1.0.0
 */

/**
 * Copyright (c) 2017 Pluginize (email : support@pluginize.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Autoloads files with classes when needed.
 *
 * @since 1.0.0
 * @param string $class_name Name of the class being requested.
 */
function instago_autoload_classes( $class_name ) {
	if ( 0 !== strpos( $class_name, 'IG_' ) ) {
		return;
	}

	$filename = strtolower( str_replace(
		'_', '-',
		substr( $class_name, strlen( 'IG_' ) )
	) );

	InstaGo::include_file( 'includes/class-' . $filename );
}
spl_autoload_register( 'instago_autoload_classes' );

/**
 * Main initiation class.
 *
 * @since 1.0.0
 */
final class InstaGo {

	/**
	 * Current version.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	const VERSION = '1.0.1';

	/**
	 * URL of plugin directory.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $basename = '';

	/**
	 * Detailed activation error messages.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $activation_errors = array();

	/**
	 * Singleton instance of plugin.
	 *
	 * @var InstaGo
	 * @since 1.0.0
	 */
	protected static $single_instance = null;

	/**
	 * Instance of IG_Instago.
	 *
	 * @var IG_Instago
	 * @since 1.0.0
	 */
	protected $instago;

	/**
	 * Intended post types.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $post_types = '';

	/**
	 * Premium store URL.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $store_url = '';

	/**
	 * Premium product name.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $plugin_name = '';

	/**
	 * Plugin Authorship.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $plugin_author = '';

	/**
	 * Default slug to use for redirection.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $default_dynamic_slug = '';

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return InstaGo A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		$this->basename      = plugin_basename( __FILE__ );
		$this->url           = plugin_dir_url( __FILE__ );
		$this->path          = plugin_dir_path( __FILE__ );
		$this->store_url     = 'https://pluginize.com';
		$this->plugin_name   = 'InstaGo';
		$this->plugin_author = 'Pluginize';
		$this->default_dynamic_slug = 'go';
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since 1.0.0
	 */
	public function plugin_classes() {
		// Attach other plugin classes to the base plugin class.
		$this->instago = new IG_Instago( $this );
	}

	/**
	 * Add hooks and filters.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'template_redirect', array( $this, 'do_redirect' ), 9 );
		add_action( 'admin_head', array( $this, 'inline_styles' ) );

		$this->updater();
	}

	public function inline_styles() {
		if ( $this->is_instago() ) {
		?>
			<style>.enabled span.cmb2-metabox-description{color:rgb(0,0,0);}</style>
		<?php
		}
	}

	/**
	 * Activate the plugin.
	 *
	 * @since 1.0.0
	 */
	public function _activate() {
		// In case people deactivate for some reason, we should respect existing settings.
		$existing = get_option( 'instago_settings' );

		if ( empty( $existing ) ) {
			$options                      = array();
			$options['dynamic_slug']      = $this->default_dynamic_slug;
			$options['redirect_location'] = '';

			update_option( 'instago_settings', $options );
		}
	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 *
	 * @since 1.0.0
	 */
	public function _deactivate() {}

	/**
	 * Init hooks.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		/**
		 * Filters the post types to check against for our redirect location.
		 *
		 * @since 1.0.0
		 *
		 * @param array $value Array of post types. Default 'post' and 'page'.
		 */
		$this->post_types = apply_filters( 'instago_post_types', array( 'post', 'page' ) );

		if ( ! $this->check_requirements() ) {
			return;
		}

		load_plugin_textdomain( 'instago', false, dirname( $this->basename ) . '/languages/' );

		$this->plugin_classes();
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since 1.0.0
	 * @return boolean Result of meets_requirements.
	 */
	public function check_requirements() {

		if ( $this->meets_requirements() ) {
			return true;
		}

		if ( ! $this->meets_php_requirements() ) {
			add_action( 'admin_notices', array( $this, 'minimum_version' ) );
			return false;
		}

		add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

		add_action( 'admin_init', array( $this, 'deactivate_me' ) );

		return false;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since 1.0.0
	 */
	public function deactivate_me() {
		// We do a check for deactivate_plugins before calling it, to protect
		// any developers from accidentally calling it too early and breaking things.
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->basename );
		}
	}

	public function meets_php_requirements() {
		return ( version_compare( PHP_VERSION, '5.3.0', '>=' ) );
	}

	public function minimum_version() {
		echo '<div id="message" class="notice is-dismissible error"><p>' . esc_html__( 'InstaGo requires PHP 5.3 or higher. Your hosting provider or website administrator should be able to assist in updating your PHP version.', 'instago' ) . '</p></div>';
	}

	/**
	 * Check that all plugin requirements are met.
	 *
	 * @since 1.0.0
	 *
	 * @return boolean True if requirements are met.
	 */
	public function meets_requirements() {
		return $this->meets_php_requirements();
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met.
	 *
	 * @since 1.0.0
	 */
	public function requirements_not_met_notice() {
		$default_message = sprintf(
			__( 'InstaGo is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'instago' ),
			admin_url( 'plugins.php' )
		);

		$details = null;

		// Add details if any exist.
		if ( ! empty( $this->activation_errors ) && is_array( $this->activation_errors ) ) {
			$details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
		}

		// Output errors.
		?>
		<div id="message" class="error">
			<p><?php echo $default_message; //WPCS: XSS ok, sanitization ok.?></p>
			<?php echo $details;//WPCS: XSS ok, sanitization ok. ?>
		</div>
		<?php
	}

	public function is_instago() {
		$post_id = ( ! empty( $_GET ) && isset( $_GET['post'] ) ) ? absint( $_GET['post'] ) : 0;
		$post_type = ( ! empty( $_GET ) && isset( $_GET['post_type'] ) ) ? sanitize_text_field( $_GET['post_type'] ) : '';

		if ( in_array( $post_type, $this->post_types, true ) ) {
			return true;
		}

		if ( in_array( get_post_type( $post_id ), $this->post_types, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field Field to get.
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'instago':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filename Name of the file to be included.
	 * @return bool Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path (optional) appended path.
	 * @return string Directory and path.
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path (optional) appended path.
	 * @return string URL and path.
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}

	/**
	 * Potentially perform our redirect.
	 *
	 * @since 1.0.0
	 */
	public function do_redirect() {
		$options                   = get_option( 'instago_settings', array() );
		$dynamic_slug              = ( ! empty( $options['dynamic_slug'] ) ) ? $options['dynamic_slug'] : '';
		$redirect_location         = ( ! empty( $options['redirect_location'] ) ) ? $options['redirect_location'] : 0;
		$redirect_location_offsite = ( ! empty( $options['redirect_location_offsite'] ) ) ? $options['redirect_location_offsite'] : '';

		$maybe_subdirectory = get_option( 'siteurl' );

		$subdir_pieces = explode( '/', $maybe_subdirectory );

		$subdir_piece = end( $subdir_pieces );

		$request = str_replace( $subdir_piece, '', $_SERVER['REQUEST_URI'] );

		$request = str_replace( '/', '', $request );

		// Default to our on-site location.
		$final_location = get_permalink( absint( $redirect_location ) );

		if ( empty( $_SERVER['REQUEST_URI'] ) || $request !== $dynamic_slug ) {
			return;
		}

		// Will only enter and potentially use offsite URL if no on-site available.
		if ( empty( $redirect_location ) ) {
			if ( ! empty( $redirect_location_offsite ) ) {
				$final_location = esc_url( $redirect_location_offsite );
			} else {
				return;
			}
		}

		wp_redirect( $final_location );
		exit();
	}

	/**
	 * Run our updater routine.
	 *
	 * @since 1.4.0
	 */
	public function updater() {
		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			require_once $this->path . 'vendor/edd-updater/EDD_SL_Plugin_Updater.php';
		}
		$options = get_option( 'instago_settings' );
		$license_key = ( ! empty( $options['license_key'] ) ) ? trim( $options['license_key'] ) : '';
		$edd_updater = new EDD_SL_Plugin_Updater( $this->store_url, __FILE__, array(
				'version'   => InstaGo::VERSION,
				'license'   => $license_key,
				'item_name' => $this->plugin_name,
				'author'    => $this->plugin_author,
			)
		);
	}
}

/**
 * Grab the InstaGo object and return it.
 * Wrapper for InstaGo::get_instance().
 *
 * @since 1.0.0
 *
 * @return InstaGo Singleton instance of plugin class.
 */
function instago() {
	return InstaGo::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( instago(), 'hooks' ) );

register_activation_hook( __FILE__, array( instago(), '_activate' ) );
register_deactivation_hook( __FILE__, array( instago(), '_deactivate' ) );
