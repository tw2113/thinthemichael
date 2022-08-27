<?php
/**
 * InstaGo Settings
 *
 * @since 1.0.0
 * @package InstaGo
 */

require_once dirname( __FILE__ ) . '/../vendor/cmb2/init.php';
require_once dirname( __FILE__ ) . '/../components/cmb2-select2-posts/cmb2_select2_posts.php';

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
		add_action( 'admin_head', array( $this, 'inline_styles' ) );

		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );
		add_action( 'cmb2_admin_init', array( $this, 'add_editor_screen_metabox' ) );
		add_filter( 'cmb2_override_redirect_location_meta_save', array( $this, 'editor_screen_override_save' ), 10, 4 );
		add_filter( 'cmb2_override_redirect_location_meta_remove', array( $this, 'editor_screen_override_remove' ), 10, 4 );
		add_filter( 'cmb2_override_redirect_location_meta_value', array( $this, 'editor_screen_override_value' ), 10, 4 );

		add_action( 'instago_before_settings', array( $this, 'before_settings' ) );
		add_action( 'instago_after_settings', array( $this, 'after_settings' ) );
	}

	/**
	 * Check to see if we are on a page that we should be firing ourselves on.
	 *
	 * @author  Brad Parbs
	 * @since   1.1.0
	 *
	 * @return  boolean  Whether or not we're on an instago admin page.
	 * And to check to see if it's an accepted InstaGo page or post for redirection.
	 */
	public function is_instago() {

		// Set our post ID and post type vars.
		$post_id   = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : false;
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : false;
		$settings  = ( isset( $_GET['page'] ) && $_GET['page'] === 'instago_settings' ) ? true : false;

		// If we have a post type, and its in our array, we're good to go.
		if ( $post_type && in_array( $post_type, $this->plugin->post_types, true ) ) {
			return true;
		}

		// If we have a post ID, and it has a post type, and thats in our array of valid post types, then we're good.
		if ( $post_id && get_post_type( $post_id ) && in_array( get_post_type( $post_id ), $this->plugin->post_types, true ) ) {
			return true;
		}

		if ( $settings ) {
			return true;
		}

		// Fallback to false.
		return false;
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

	public function inline_styles() {
		if ( $this->is_instago() ) {
			?>
			<style>.enabled span.cmb2-metabox-description {
					color: rgb(0, 0, 0);
				}</style>
			<?php
		}
	}

	/**
	 * Create our options page.
	 *
	 * @since 1.0.0
	 */
	public function add_options_page_metabox() {

		$has_changed = ( str_contains( $_SERVER['REQUEST_URI'], '&i=' ) ) ? explode( '&i=', $_SERVER['REQUEST_URI'] )[1] : '';

		if ( ! empty( $has_changed ) ) {
			add_action( 'admin_notices', 'updated_redirection__success' );
		}

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
			'post_type'   => $this->plugin->post_types,
			'width'       => '300px',
		) );

		$cmb->add_field( array(
			'name'      => esc_html__( 'External Redirect', 'instago' ),
			'desc'      => esc_html__( 'Full URL, including http://', 'instago' ),
			'id'        => 'redirect_location_offsite',
			'type'      => 'text',
			'escape_cb' => array( $this, 'full_url' ),
		) );

		$cmb->add_field( array(
			'name'                     => esc_html__( 'Minimum User Level', 'instago' ),
			'desc'                     => esc_html__( 'Select Role', 'instago' ),
			'id'                       => 'role_capability',
			'type'                     => 'select',
			'show_option_none'         => true,
			'default'                  => 'manage_options',
			'options'                  => array(
				'manage_options'       => __( 'Administrator', 'manage_options' ),
				'delete_others_pages'  => __( 'Editor', 'delete_others_pages' ),
				'publish_posts'        => __( 'Author', 'publish_posts' ),
				'delete_posts'         => __( 'Contributor', 'delete_posts' ),
			),
		) );

		$cmb->add_field( array(
			'name'       => esc_html__( 'License key', 'instago' ),
			'desc'       => esc_html__( 'License key provided by Pluginize', 'instago' ),
			'id'         => 'license_key',
			'type'       => 'text',
			'before_row' => sprintf( '<h2>%s</h2>', esc_html__( 'License Activation', 'instago' ) ),
			'after_field' => array( $this, 'get_activate_deactivate_links' ),
		) );
	}

	/**
	 * Helper to get activate and deactivate links.
	 *
	 * @author Brad Parbs
	 * @since 1.0.0
	 *
	 * @return string Markup for links.
	 */
	public function get_activate_deactivate_links() {

		// Grab our options.
		$options = get_option( 'instago_settings' );

		// Sanity check our options.
		if ( empty( $options ) || ! isset( $options['license_key'] ) ) {
			return '';
		}

		// Grab our lisc option.
		$is_valid = ( 'valid' === get_option( 'instago_license_status' ) );

		return sprintf(
			'<p>%s<br/><button class="button button-secondary" name="instago_activate_deactivate" id="instago_activate_deactivate" value="%s">%s</p>',
			$is_valid ? esc_html__( 'Status: Active', 'instago' ) : esc_html__( 'Status: Inactive', 'instago' ),
			$is_valid ? 'instago_deactivate' : 'instago_activate',
			$is_valid ? esc_html__( 'Deactivate license', 'instago' ) : esc_html__( 'Activate license', 'instago' )
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

	function before_settings() {
		if ( empty( $_GET['add'] ) ) {
			return;
		}
		$new_redirect_id = sanitize_text_field( $_GET['add'] );
		$options         = get_option( 'instago_settings' );
		$options['redirect_location'] = $new_redirect_id;

		return update_option( 'instago_settings', $options );
	}

	function after_settings() {
		$options = get_option( 'instago_settings' );
		?>
		<h2><?php esc_html_e( 'How It Works', 'instago' ); ?></h2>
		<p><?php esc_html_e( 'On your social media profiles, add your InstaGo dynamic URL to your bio.', 'instago' ); ?><br />

			<?php if ( isset( $options['dynamic_slug'] ) ) : ?>

				<?php esc_html_e( 'Click to copy your dynamic URL', 'instago' ); ?>:

				<span id="instago_dynamic_url" class="clipboard" onclick="copy_url_to_clipboard()">
					<?php echo esc_url( trailingslashit( home_url( '/' ) . $options['dynamic_slug'] ) ); ?>
				</span> <span id="copy_result">Copied ! </span>
			<?php endif ?>
		</p>

		<p><?php esc_html_e( 'InstaGo uses the following logic to determine where to redirect visitors to your InstaGo URL:', 'instago' ); ?>

		<ol>
			<li>
				<?php esc_html_e( 'Internal Link only, InstaGo will redirect visitors to the Internal Link', 'instago' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'External Link only, InstaGo will redirect visitors to the External Link', 'instago' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Internal Link AND External Link, InstaGo will redirect visitors to the Internal Link', 'instago' ); ?>
			</li>
		</ol>
		</p>

		<p>
			<strong><?php esc_html_e( 'Changing the Redirect URL', 'instago' ); ?></strong>
		</p>

		<p>
			<?php esc_html_e( 'You can update the InstaGo Redirect URL from the edit screen for a page/post by clicking the checkbox inside the "InstaGo Redirect" metabox and saving the page/post.', 'instago' ); ?>
		</p>

		<p style="margin-top: 50px;">

			<?php esc_html_e( 'Powered by', 'instago' ); ?>
			<br />
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

		$options = get_option( 'instago_settings' );

		if ( ! empty( $options['role_capability'] ) && current_user_can( $options['role_capability'] ) ) {

			$cmb = new_cmb2_box( array(
				'id'           => $this->metabox_editor_id,
				'title'        => esc_html__( 'InstaGo Redirect', 'instago' ),
				'object_types' => $this->plugin->post_types,
				'context'      => 'side',
				'priority'     => 'low',
			) );

			$cmb->add_field( array(
				'desc'       => esc_html__( 'Redirect InstaGo URL to this page', 'instago' ),
				'id'         => 'redirect_location',
				'type'       => 'checkbox',
				'attributes' => ( $this->is_draft() ) ? array( 'disabled' => 'disabled' ) : '',
				'classes'    => ( $this->is_draft() ) ? 'disabled' : 'enabled',
			) );

		}
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
			if ( 'draft' === get_post_status( absint( $_GET['post'] ) ) ) {
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

		return ( $object_id === $id ) ? 'on' : $value;
	}
}

/**
 * Notice for updated redirection
 *
 * @return void
 */
function updated_redirection__success() {
	$class = 'notice notice-success';
	$message = esc_html__( 'Redirection has been successfully updated', 'instago' );
	echo sprintf(
		'<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message )
	);
}
