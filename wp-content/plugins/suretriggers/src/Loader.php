<?php
/**
 * Loader.
 * php version 5.6
 *
 * @category Loader
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers;

use DirectoryIterator;
use SureTriggers\Controllers\AuthController;
use SureTriggers\Controllers\AutomationController;
use SureTriggers\Controllers\EventController;
use SureTriggers\Controllers\GlobalSearchController;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Controllers\OptionController;
use SureTriggers\Controllers\RestController;
use SureTriggers\Controllers\RoutesController;
use SureTriggers\Controllers\SettingsController;
use SureTriggers\Traits\SingletonLoader;
use function add_menu_page;
use function add_submenu_page;

/**
 * Loader
 *
 * @category Loader
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class Loader {



	use SingletonLoader;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		register_activation_hook( SURE_TRIGGERS_FILE, [ $this, 'st_activate' ] );

		$this->define_constants();
		add_action( 'plugins_loaded', [ $this, 'initialize_core' ] );
		// Admin Menu.
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_init', [ $this, 'reset_plugin' ] );

		add_filter( 'plugin_action_links_' . plugin_basename( SURE_TRIGGERS_FILE ), [ $this, 'add_settings_link' ] );
		add_action( 'admin_init', [ $this, 'redirect_after_activation' ] );

		add_action( 'admin_notices', [ $this, 'display_notice' ] );

		add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widgets' ] );
	}

	/**
	 * Adding dashboard widget.
	 *
	 * @return void
	 */
	public function add_dashboard_widgets() {
		if ( isset( OptionController::$options['secret_key'] ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'suretriggers_dashboard_widget',
			'Please Connect SureTriggers',
			[ $this, 'dashboard_widget_display' ],
			'',
			'',
			'side',
			'high'
		);
	}

	/**
	 * Dashboard widget callback.
	 *
	 * @return void
	 */
	public function dashboard_widget_display() {            ?>
		<div>
			<p> <?php esc_html_e( 'Please connect to or create your SureTriggers account.', 'suretriggers' ); ?></p>
			<p> <?php esc_html_e( 'This will enable you to connect your various plugins, and apps together and automate repetitive tasks.', 'suretriggers' ); ?> </p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=suretriggers' ) ); ?>" class="button button-primary"> <?php esc_html_e( 'Get Started', 'suretriggers' ); ?> </a>
		</div>
		<?php
	}

	/**
	 * Display notice.
	 *
	 * @return void
	 */
	public function display_notice() {
		if ( isset( OptionController::$options['secret_key'] ) ) {
			return;
		}
		global $pagenow;
		if ( 'index.php' != $pagenow ) {
			return;
		}
		?>
		<div class="notice notice-success" style="padding-bottom: 15px;">
			<p>
				<strong>
					<?php esc_html_e( 'Connect your plugins and apps together with SureTriggers', 'suretriggers' ); ?>
					<span style="transform: rotate(-90deg); font-size: 15px;" class="dashicons dashicons-admin-plugins"></span>
				</strong>
			</p>
			<p> <?php esc_html_e( 'Please connect to or create your SureTriggers account. This will enable you to connect your various plugins and apps together and automate repetitive tasks.', 'suretriggers' ); ?> </p>

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=suretriggers' ) ); ?>" class="button button-primary"> <?php esc_html_e( 'Get Started With SureTriggers', 'suretriggers' ); ?> </a>
			<a href="https://suretriggers.com/" class="button button-secondary"> <?php esc_html_e( 'Learn More', 'suretriggers' ); ?> </a>
		</div>
		<?php
	}

	/**
	 * Redirect user after plugin activation.
	 *
	 * @return void
	 */
	public function redirect_after_activation() {
		$is_redirect = get_transient( 'st-redirect-after-activation' );
		if ( $is_redirect ) {
			delete_transient( 'st-redirect-after-activation' );
			$url = get_admin_url() . 'admin.php?page=suretriggers';
			wp_safe_redirect( $url );
			die;
		}
	}

	/**
	 * Adding setting link.
	 *
	 * @param array $links links.
	 * @return array
	 */
	public function add_settings_link( array $links ) {
		$url            = get_admin_url() . 'admin.php?page=suretriggers';
		$setting_option = get_option( 'suretrigger_options' );
		if ( isset( $setting_option ) && ! empty( $setting_option ) ) {
			$settings_link = '<a href="' . $url . '">' . __( 'Dashboard', 'suretriggers' ) . '</a>';
		} else {
			$settings_link = '<a href="' . $url . '">' . __( 'Connect', 'suretriggers' ) . '</a>';
		}
		$links[] = $settings_link;
		return $links;
	}

	/**
	 * Define constants
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public function define_constants() {
		$sass_url    = 'https://app.suretriggers.com';
		$api_url     = 'https://api.suretriggers.com';
		$webhook_url = 'https://webhook.suretriggers.com';
		
		define( 'SURE_TRIGGERS_BASE', plugin_basename( SURE_TRIGGERS_FILE ) );
		define( 'SURE_TRIGGERS_DIR', plugin_dir_path( SURE_TRIGGERS_FILE ) );
		define( 'SURE_TRIGGERS_URL', plugins_url( '/', SURE_TRIGGERS_FILE ) );
		define( 'SURE_TRIGGERS_VER', '1.0.57' );
		define( 'SURE_TRIGGERS_DB_VER', '1.0.57' );
		define( 'SURE_TRIGGERS_REST_NAMESPACE', 'sure-triggers/v1' );
		define( 'SURE_TRIGGERS_SASS_URL', $sass_url . '/wp-json/wp-plugs/v1/' );
		define( 'SURE_TRIGGERS_SITE_URL', $sass_url );
		define( 'API_SERVER_URL', $api_url );
		define( 'WEBHOOK_SERVER_URL', $webhook_url );

		define( 'SURE_TRIGGERS_PAGE', 'SureTrigger' );
		define( 'SURE_TRIGGERS_AS_GROUP', 'SureTrigger' );

		define( 'SURE_TRIGGERS_ACTION_ERROR_MESSAGE', 'An unexpected error occurred. Something went wrong with the action.' );
	}

	/**
	 * Flush permalink rules while plugin activation.
	 *
	 * @return void
	 */
	public function st_activate() {
		flush_rewrite_rules(); //phpcs:ignore

		set_transient( 'st-redirect-after-activation', true, 120 );
	}

	/**
	 * Add main menu
	 *
	 * @since x.x.x
	 *
	 * @return void
	 */
	public function admin_menu() {
		$page_title = apply_filters( 'st_menu_page_title', esc_html__( 'SureTriggers', 'suretriggers' ) );
		$logo       = file_get_contents( plugin_dir_path( SURE_TRIGGERS_FILE ) . 'assets/images/STLogo.svg' );

		add_menu_page(
			$page_title,
			$page_title,
			'manage_options',
			'suretriggers',
			[ $this, 'menu_callback' ],
			'data:image/svg+xml;base64,' . base64_encode( $logo ),
			30.6002
		);
	}

	/**
	 * Enqueue the admin scripts
	 *
	 * @param string $hook hook.
	 * @since x.x.x
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook = '' ) {
		if ( ! in_array( $hook, [ 'toplevel_page_suretriggers' ], true ) ) {
			return;
		}

		remove_all_actions( 'admin_notices' );

		$file = SURE_TRIGGERS_DIR . 'app/build/main.asset.php';
		if ( ! file_exists( $file ) ) {
			return;
		}

		$asset = require_once $file;

		if ( ! isset( $asset ) ) {
			return;
		}

		wp_register_script(
			'sure-trigger-admin',
			SURE_TRIGGERS_URL . 'app/build/main.js',
			array_merge( $asset['dependencies'], [ 'regenerator-runtime' ] ),
			$asset['version'],
			true
		);

		wp_localize_script(
			'sure-trigger-admin',
			'sureTriggerData',
			$this->get_localized_array()
		);
		wp_enqueue_script( 'sure-trigger-admin' );
		wp_enqueue_style( 'sure-trigger-components', SURE_TRIGGERS_URL . 'app/build/style-main.css', [], SURE_TRIGGERS_VER );
		wp_enqueue_style( 'sure-trigger-css', SURE_TRIGGERS_URL . 'app/build/main.css', [], SURE_TRIGGERS_VER );
	}

	/**
	 * Get localized array for sure triggers.
	 *
	 * @return array
	 */
	private function get_localized_array() {
		$current_user = wp_get_current_user();

		$data = [
			'siteContent'         => [
				'siteUrl'      => str_replace( '/wp-json/', '', get_rest_url() ),
				'redirectUrl'  => get_site_url() . '/wp-admin/themes.php?page=suretriggers',
				'connectNonce' => wp_create_nonce( 'sure-trigger-connect' ),
				'connectUrl'   => SURE_TRIGGERS_SITE_URL . '/connect-st/connect',
				'siteTitle'    => get_bloginfo( 'name' ),
				'resetUrl'     => base64_encode( wp_nonce_url( admin_url( 'admin.php?st-reset=true' ), 'st-reset-action' ) ),
			],
			'user'                => [
				'name'  => $current_user->display_name,
				'email' => $current_user->user_email,
			],
			'stSaasURL'           => trailingslashit( SURE_TRIGGERS_SITE_URL ),
			'stPluginURL'         => plugin_dir_url( SURE_TRIGGERS_FILE ),
			'integrations'        => IntegrationsController::get_activated_integrations(),
			'enabledIntegrations' => OptionController::get_option( 'enabled_integrations' ),
			'settingsPageURL'     => admin_url( 'themes.php?page=suretriggers' ),
			'verification_status' => false,
			'projects'            => [],
			'apiSlug'             => SURE_TRIGGERS_REST_NAMESPACE,
			'isElementorEditor'   => ( did_action( 'elementorpro/loaded' ) ) ? Elementor\Plugin::instance()->editor->is_edit_mode() : false,
			'reConnectSorryMsg'   => (bool) OptionController::get_option( 'st_connect_notice_deprecated' ),
		];

		if ( current_user_can( 'manage_options' ) ) {
			$data['siteContent']['accessKey']       = OptionController::get_option( 'secret_key' );
			$data['siteContent']['connected_email'] = OptionController::get_option( 'connected_email_key' );
		}

		$settings = OptionController::get_option( 'st_settings' );
		if ( empty( $settings ) ) {
			$settings = (object) [];
		}

		$data['settingsForm'] = SettingsController::get_fields();
		$data['settings']     = wp_json_encode( $settings );
		$data['nonce']        = wp_create_nonce( 'st-nonce' );
		$data['ajaxurl']      = esc_url( admin_url( 'admin-ajax.php', 'relative' ) );

		return apply_filters( 'sure_trigger_control_localize_vars', $data );
	}

	/**
	 * Menu callback
	 *
	 * @since x.x.x
	 *
	 * @return void
	 */
	public function menu_callback() {       
		?>
		<div id="sure-triggger-entry" class="st-base"></div>
		<?php
	}

	/**
	 * Include all files from the folder.
	 *
	 * @param string $folder folder path.
	 * @return void
	 */
	public function include_all_files( $folder ) {
		$dir = new DirectoryIterator( $folder );
		foreach ( $dir as $file ) {
			if ( ! $file->isDot() ) {
				if ( $file->isDir() ) {
					$this->include_all_files( $file->getPathname() );
				} else {
					require_once $file->getPathname();
				}
			}
		}
	}

	/**
	 * Initialize core trigger and actions.
	 *
	 * @return void
	 */
	public function initialize_core() {
		/**
		 * Include only integrations root files
		 */

		$this->include_all_files( SURE_TRIGGERS_DIR . 'src/Integrations/' );

		IntegrationsController::load_event_files();

		EventController::get_instance();
		IntegrationsController::get_instance();
		GlobalSearchController::get_instance();
		RestController::get_instance();
		OptionController::get_instance();
		AutomationController::get_instance();
		AuthController::get_instance();
		RoutesController::get_instance();
		SettingsController::get_instance();
	}

	/**
	 * Added option to reset plugin in case of testing.
	 *
	 * @return void
	 */
	public function reset_plugin() {
		$is_reset = sanitize_text_field( wp_unslash( isset( $_GET['st-reset'] ) ? $_GET['st-reset'] : false ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nonce    = sanitize_text_field( wp_unslash( isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : false ) );

		if ( $nonce && $is_reset && current_user_can( 'manage_options' ) && wp_verify_nonce( $nonce, 'st-reset-action' ) ) {
			delete_option( 'suretrigger_options' );
			wp_safe_redirect( admin_url( 'admin.php?page=suretriggers' ) );
			exit();
		}
	}
}
