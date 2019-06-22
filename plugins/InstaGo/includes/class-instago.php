<?php
/**
 * InstaGo Settings
 *
 * @since 1.0.0
 * @package InstaGo
 */

require_once dirname( __FILE__ ) . '/../vendor/cmb2/init.php';
require_once dirname( __FILE__ ) . '/../vendor/cmb2-select2-posts/cmb2_select2_posts.php';

/**
 * InstaGo Metaboxes class.
 *
 * @since 1.0.0
 */
class IG_Instago {
	/**
	 * Parent plugin class.
	 *
	 * @var InstaGo
	 * @since 1.0.0
	 */
	protected $plugin = null;

	/**
	 * Option key, and option page slug.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $key = 'instago_settings';

	/**
	 * Options page metabox id.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $metabox_id = 'instago_metabox';

	/**
	 * Options Page title.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $title = '';

	/**
	 * Options Page hook.
	 *
	 * @var string
	 */
	protected $options_page = '';

	public $metabox_editor_id = 'instago_editor_metabox';

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

		$this->title = esc_html__( 'InstaGo Settings', 'instago' );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
		add_action( 'cmb2_admin_init', array( $this, 'add_editor_screen_metabox' ) );
		add_filter( 'cmb2_override_redirect_location_meta_save', array( $this, 'editor_screen_override_save' ), 10, 4 );
		add_filter( 'cmb2_override_redirect_location_meta_remove', array( $this, 'editor_screen_override_remove' ), 10, 4 );
		add_filter( 'cmb2_override_redirect_location_meta_value', array( $this, 'editor_screen_override_value' ), 10, 4 );

		add_action( 'admin_init', array( $this, 'process_license' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		add_action( 'instago_after_settings', array( $this, 'after_settings' ) );
	}

	/**
	 * Register our setting to WP.
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page.
	 *
	 * @since 1.0.0
	 */
	public function add_options_page() {
		$this->options_page = add_options_page(
			$this->title,
			$this->title,
			'manage_options',
			$this->key,
			array( $this, 'admin_page_display' )
		);

		// Include CMB CSS in the head to avoid FOUC.
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2.
	 *
	 * @since 1.0.0
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb2-options-page <?php echo esc_attr( $this->key ); ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

			<?php do_action( 'instago_before_settings' ); ?>

			<?php cmb2_metabox_form( $this->metabox_id, $this->key, array( 'save_button' => esc_attr__( 'Save settings', 'instago' ) ) ); ?>

			<?php do_action( 'instago_after_settings' ); ?>
		</div>
		<?php
	}

	/**
	 * Sets our saved status, as appropriate.
	 *
	 * @since 1.0.0
	 *
	 * @param string $object_id
	 * @param $updated
	 */
	public function settings_notices( $object_id = '', $updated ) {
		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}
		add_settings_error(
			$this->key . '-notices',
			'',
			esc_html__( 'Settings updated.', 'instago' ),
			'updated'
		);
		settings_errors( $this->key . '-notices' );
	}

	/**
	 * Create our options page.
	 *
	 * @since 1.0.0
	 */
	public function add_options_page_metabox() {

		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", array( $this, 'settings_notices' ), 10, 2 );

		$post_types = instago()->post_types;

		$cmb = new_cmb2_box( array(
			'id'         => $this->metabox_id,
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove.
				'key'   => 'options-page',
				'value' => array( $this->key ),
			),
		) );

		$cmb->add_field( array(
			'name'    => esc_html__( 'Dynamic URL slug', 'instago' ),
			'desc'    => esc_html__( 'Customize the slug for your InstaGo URL', 'instago' ),
			'id'      => 'dynamic_slug',
			'type'    => 'text',
			'default' => esc_attr__( 'go', 'instago' ),
		) );

		$cmb->add_field( array(
			'name'        => esc_html__( 'Internal Redirect', 'instago' ),
			'desc'        => esc_html__( 'Select a page or post', 'instago' ),
			'placeholder' => esc_html__( 'Set your value', 'instago' ),
			'id'          => 'redirect_location',
			'type'        => 'own_select2_posts',
			'post_type'   => $post_types,
			'width'       => '300px',
		) );

		$cmb->add_field( array(
			'name'      => esc_html__( 'External Redirect', 'instago' ),
			'desc'      => esc_html__( 'Full URL, including http://', 'instago' ),
			'id'        => 'redirect_location_offsite',
			'type'      => 'text',
			'escape_cb' => array( $this, 'full_url' ),
		) );

		$before_license = sprintf(
			'<h2>%s</h2>',
			esc_html__( 'License Activation', 'instago' )
		);

		$cmb->add_field( array(
			'name'       => esc_html__( 'License key', 'instago' ),
			'desc'       => esc_html__( 'License key provided by Pluginize', 'instago' ),
			'id'         => 'license_key',
			'type'       => 'text',
			'before_row' => $before_license,
			'after_field' => array( $this, 'get_activate_deactivate_links' ),
		) );
	}

	public function get_activate_deactivate_links() {
		$options = get_option( 'instago_settings' );

		if ( empty( $options ) || ! isset( $options['license_key'] ) ) {
			return;
		}

		$active_status = get_option( 'instago_license_status' );

		if ( 'valid' === $active_status ) {
			$value = 'instago_deactivate';
			$submit_text = esc_attr__( 'Deactivate license', 'instago' );
			$status = esc_html__( 'Status: Active', 'instago' );
		} else {
			$value = 'instago_activate';
			$submit_text = esc_attr__( 'Activate license', 'instago' );
			$status = esc_html__( 'Status: Inactive', 'instago' );
		}
		return sprintf(
			'<p>%s<br/><button class="button button-secondary" name="instago_activate_deactivate" id="instago_activate_deactivate" value="%s">%s</p>',
			$status,
			$value,
			$submit_text
		);
	}

	/**
	 * Escape our urls for full schema.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value URL provided by user.
	 * @return string
	 */
	function full_url( $value = '' ) {
		return esc_url( $value );
	}

	function after_settings() {
			$options = get_option( 'instago_settings' );
			?>
			<h2><?php esc_html_e( 'How It Works', 'instago' ); ?></h2>
			<p><?php esc_html_e( 'On your social media profiles, add your InstaGo dynamic URL to your bio.', 'instago' ); ?><br />
				<?php esc_html_e( 'Your dynamic URL is', 'instago' ); ?>: <?php if ( ! empty( $options['dynamic_slug'] ) ) { ?><a href="<?php echo trailingslashit( home_url( '/' ) . $options['dynamic_slug'] ); ?>"><?php echo trailingslashit( home_url( '/' ) . $options['dynamic_slug'] ); ?></a><?php } ?>
			</p>

			<p><?php esc_html_e( 'InstaGo uses the following logic to determine where to redirect visitors to your InstaGo URL:', 'instago' ); ?>

			<ol>
				<li><?php esc_html_e( 'Internal Link only, InstaGo will redirect visitors to the Internal Link', 'instago' ); ?></li>
				<li><?php esc_html_e( 'External Link only, InstaGo will redirect visitors to the External Link', 'instago' ); ?></li>
				<li><?php esc_html_e( 'Internal Link AND External Link, InstaGo will redirect visitors to the Internal Link', 'instago' ); ?></li>
			</ol>

			<p><strong><?php esc_html_e( 'Changing the Redirect URL', 'instago' ); ?></strong></p>

			<p><?php esc_html_e( 'You can update the InstaGo Redirect URL from the edit screen for a page/post by clicking the checkbox inside the "InstaGo Redirect" metabox and saving the page/post.', 'instago' ); ?></p>

			<p style="margin-top: 50px;">
				<?php esc_html_e( 'Powered by', 'instago' ); ?><br />
				<a href="https://pluginize.com/">
					<img src="<?php echo esc_url( instago()->url . '/assets/pluginize-logo.png' ); ?>" alt="<?php echo esc_attr( sprintf( __( '%s logo', 'instago' ), 'Pluginize' ) ); ?>">
				</a>
			</p>
		<?php
	}

	/**
	 * Add our metabox to appropriate post types.
	 *
	 * @since 1.0.0
	 */
	public function add_editor_screen_metabox() {

		$post_types = instago()->post_types;
		$cmb = new_cmb2_box( array(
			'id'           => $this->metabox_editor_id,
			'title'        => esc_html__( 'InstaGo Redirect', 'instago' ),
			'object_types' => $post_types,
			'context'      => 'side',
			'priority'     => 'low',
		) );

		$is_draft   = $this->is_draft();
		$attributes = ( $is_draft ) ? array( 'disabled' => 'disabled' ) : '';
		$classes    = ( $is_draft ) ? 'disabled' : 'enabled';

		$cmb->add_field( array(
			'desc'       => esc_html__( 'Redirect InstaGo URL to this page', 'instago' ),
			'id'         => 'redirect_location',
			'type'       => 'checkbox',
			'attributes' => $attributes,
			'classes'    => $classes,
		) );
	}

	/**
	 * Determine whether or not the current post editor post is a draft or published.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_draft() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return false;
		}

		$is_draft = false;

		if ( isset( $_SERVER['DOCUMENT_URI'] ) && '/wp-admin/post-new.php' === $_SERVER['DOCUMENT_URI'] ) {
			$is_draft = true;
		}

		if ( ! empty( $_GET ) && isset( $_GET['post'] ) ) {
			$post_id = absint( $_GET['post'] );

			if ( 'draft' === get_post_status( $post_id ) ) {
				$is_draft = true;
			}
		}

		return $is_draft;
	}

	/**
	 * Return our full settings page URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_options_page() {
		return admin_url( 'options-general.php?page=' . $this->key );
	}

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
		$response = $this->do_activate_deactivate( $activate_deactivate );
		$success = 'false';

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
							$message = sprintf( esc_html__( 'This appears to be an invalid license key for %s.', 'instago' ), instago()->plugin_name );
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
			), $this->get_options_page() );

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
			if ( isset( $_GET['page'] ) && $this->key === $_GET['page'] ) {
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

	/**
	 * Override our save value so we save to our plugin option instead of metadata.
	 *
	 * @since 1.0.0
	 *
	 * @param $value
	 * @param $object_type
	 * @param $args
	 * @param $field
	 * @return bool
	 */
	public function editor_screen_override_save( $value, $object_type, $args, $field ) {
		if ( ! isset( $object_type['type'] ) || 'options-page' === $object_type['type'] ) {
			return $value;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $value;
		}

		if ( 'draft' === get_post_status( $object_type['id'] ) ) {
			return 'meta_save_prevention';
		}

		$options                      = get_option( 'instago_settings', array() );
		$options['redirect_location'] = $object_type['id'];

		if ( empty( $options['dynamic_slug'] ) ) {
			$options['dynamic_slug'] = instago()->default_dynamic_slug;
		}
		return update_option( 'instago_settings', $options );
	}

	/**
	 * Override our removal of metadata and remove our option instead, when appropriate.
	 *
	 * @since 1.0.0
	 *
	 * @param $value
	 * @param $object_type
	 * @param $args
	 * @param $field
	 * @return bool
	 */
	public function editor_screen_override_remove( $value, $object_type, $args, $field ) {
		if ( ! isset( $object_type['type'] ) || 'options-page' === $object_type['type'] ) {
			return $value;
		}

		$options = get_option( 'instago_settings', array() );

		if ( empty( $object_type['old'] ) && $object_type['id'] === $options['redirect_location'] ) {
			$options['redirect_location'] = 0;
		}

		return update_option( 'instago_settings', $options );
	}

	/**
	 * Override the value to display in the editor screen metabox.
	 *
	 * @since 1.0.0
	 *
	 *
	 * @param mixed      $value     Original value.
	 * @param int        $object_id Object ID.
	 * @param array      $args      Args for the CMB2 field.
	 * @param CMB2_Field $field     CMB2_Field object.
	 * @return string
	 */
	public function editor_screen_override_value( $value, $object_id, $args, $field ) {
		if ( ! isset( $args['type'] ) || 'options-page' === $args['type'] ) {
			return $value;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $value;
		}

		if ( 'draft' === get_post_status( $object_id ) ) {
			return $value;
		}

		$options = get_option( 'instago_settings', array() );
		$id      = isset( $options['redirect_location'] ) ? (int) $options['redirect_location'] : 0;

		$value = ( $object_id === $id ) ? 'on' : $value;
		return $value;
	}
}
