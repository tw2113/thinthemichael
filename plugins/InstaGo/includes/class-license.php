<?php
/**
 * InstaGo License.
 *
 * @since 1.0.0
 * @package InstaGo
 */

/**
 * InstaGo License.
 *
 * @since 1.0.0
 */
class IG_License {

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
		add_action( 'admin_init', array( $this, 'process_license' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Process license handling.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function process_license() {

		if ( empty( $_POST ) || empty( $_POST['instago_activate_deactivate'] ) ) {
			return false;
		}

		// Run a quick security check.
		// Attempt hardcoding this.
		if ( ! check_admin_referer( 'nonce_CMB2phpinstago_metabox', 'nonce_CMB2phpinstago_metabox' ) ) {
			return false;
		}

		$activate_deactivate = ( 'instago_activate' === sanitize_text_field( $_POST['instago_activate_deactivate'] ) ) ? 'activate_license' : 'deactivate_license';
		$response            = $this->do_activate_deactivate( $activate_deactivate );
		$success             = 'false';

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$success = 'false';
			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = esc_html__( 'An error occurred, please try again.', 'instago' );
			}
		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( 'activate_license' === $activate_deactivate ) {
				$message = esc_html__( 'License successfully activated', 'instago' );
				if ( false === $license_data->success ) {
					$success = 'false';
					switch ( $license_data->error ) {

						case 'expired' :
							$message = sprintf(
								esc_html__( 'Your license key expired on %s.', 'instago' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;

						case 'revoked' :
							$message = esc_html__( 'Your license key has been disabled.', 'instago' );
							break;

						case 'missing' :
							$message = esc_html__( 'Invalid license.', 'instago' );
							break;

						case 'invalid' :
						case 'site_inactive' :
							$message = esc_html__( 'Your license is not active for this URL.', 'instago' );
							break;

						case 'item_name_mismatch' :
							$message = sprintf( esc_html__( 'This appears to be an invalid license key for %s.', 'instago' ), $this->plugin->plugin_name );
							break;

						case 'no_activations_left':
							$message = esc_html__( 'Your license key has reached its activation limit.', 'instago' );
							break;

						default :
							$message = esc_html__( 'An error occurred, please try again.', 'instago' );
							break;
					}
				} else {
					$success = 'true';
				}
			}

			if ( 'deactivate_license' === $activate_deactivate ) {
				if ( 'deactivated' === $license_data->license ) {
					delete_option( 'instago_license_status' );
					$message = esc_html__( 'License successfully deactivated', 'instago' );
					$success = 'true';
				}
			}

			if ( 'activate_license' === $activate_deactivate ) {
				update_option( 'instago_license_status', $license_data->license );
			}
		}

		if ( ! empty( $message ) ) {
			$redirect = add_query_arg( array(
				'sl_activation' => 'false',
				'message'       => urlencode( $message ),
				'success'       => $success,
			), $this->plugin->instago->get_options_page() );

			wp_redirect( $redirect );
			exit();
		}

		return true;
	}

	/**
	 * Process a license request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action Action being performed. Either deactivate or activate. Default activate.
	 * @return mixed
	 */
	public function do_activate_deactivate( $action = 'activate_license' ) {

		$options = get_option( 'instago_settings' );
		$license = ( ! empty( $options['license_key'] ) ) ? $options['license_key'] : '';

		$api_params = array(
			'edd_action' => $action,
			'license'    => $license,
			'item_name'  => urlencode( instago()->plugin_name ),
			'url'        => home_url(),
		);
		$store_url  = instago()->store_url;

		return wp_remote_post( $store_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
	}

	/**
	 * This is a means of catching errors from the activation method above and displaying it to the customer.
	 *
	 * @since 1.0.0
	 */
	public function admin_notices() {
		if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {
			if ( isset( $_GET['page'] ) && $this->plugin->instago->key === $_GET['page'] ) {
				switch ( $_GET['sl_activation'] ) {
					case 'false':
						$message = urldecode( $_GET['message'] );
						$success = ( 'true' === sanitize_text_field( $_GET['success'] ) ) ? 'updated' : 'error';
						?>
						<div class="<?php echo esc_attr( $success ); ?>">
							<p><?php echo esc_html( $message ); ?></p>
						</div>
						<?php
						break;

					case 'true':
					default:
						break;
				}
			}
		}
	}
}
