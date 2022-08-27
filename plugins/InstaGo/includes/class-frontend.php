<?php
/**
 * InstaGo Frontend.
 *
 * @since   1.0.0
 * @package InstaGo
 */

/**
 * InstaGo Frontend.
 *
 * @since 1.0.0
 */
class IG_Frontend {

	/**
	 * Parent plugin class.
	 *
	 * @var InstaGo
	 * @since 1.0.0
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param InstaGo $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		add_action( 'template_redirect', array( $this, 'do_redirect' ), 9 );
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

		// Assume we may just need to redirect home.
		$final_location = get_bloginfo( 'url' );

		// Get on-site location.
		$onsite_location = false;
		if ( ! empty( absint( $redirect_location ) ) ) {
			$onsite_location = get_permalink( absint( $redirect_location ) );
		}

		if ( false !== $onsite_location ) {
			$final_location = $onsite_location;
		}

		if ( empty( $_SERVER['REQUEST_URI'] ) || $request !== $dynamic_slug ) {
			return;
		}

		// Use offsite URL if no on-site available.
		if ( empty( $redirect_location ) ) {
			if ( ! empty( $redirect_location_offsite ) ) {
				$final_location = esc_url( $redirect_location_offsite );
			}
		}

		wp_redirect( $final_location );
		exit();
	}
}
