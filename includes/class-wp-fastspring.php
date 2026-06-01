<?php
/**
 * Main plugin loader.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring.
 */
final class WP_FastSpring {

	/**
	 * Singleton instance.
	 *
	 * @var WP_FastSpring|null
	 */
	private static $instance = null;

	/**
	 * API client.
	 *
	 * @var WP_FastSpring_API|null
	 */
	public $api = null;

	/**
	 * Settings handler.
	 *
	 * @var WP_FastSpring_Settings|null
	 */
	public $settings = null;

	/**
	 * Returns the singleton.
	 *
	 * @return WP_FastSpring
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Load required files.
	 */
	private function includes() {
		$base = WP_FASTSPRING_PATH . 'includes/';

		require_once $base . 'class-wp-fastspring-install.php';
		require_once $base . 'class-wp-fastspring-settings.php';
		require_once $base . 'class-wp-fastspring-api.php';
		require_once $base . 'class-wp-fastspring-logger.php';
		require_once $base . 'class-wp-fastspring-webhook.php';
		require_once $base . 'class-wp-fastspring-product-sync.php';
		require_once $base . 'class-wp-fastspring-data-store.php';
		require_once $base . 'class-wp-fastspring-stats.php';

		if ( is_admin() ) {
			require_once $base . 'admin/class-wp-fastspring-admin-resource-base.php';
			require_once $base . 'admin/class-wp-fastspring-admin.php';
			require_once $base . 'admin/class-wp-fastspring-admin-dashboard.php';
			require_once $base . 'admin/class-wp-fastspring-admin-orders.php';
			require_once $base . 'admin/class-wp-fastspring-admin-subscriptions.php';
			require_once $base . 'admin/class-wp-fastspring-admin-accounts.php';
			require_once $base . 'admin/class-wp-fastspring-admin-products.php';
			require_once $base . 'admin/class-wp-fastspring-admin-coupons.php';
			require_once $base . 'admin/class-wp-fastspring-admin-invoices.php';
			require_once $base . 'admin/class-wp-fastspring-admin-quotes.php';
			require_once $base . 'admin/class-wp-fastspring-admin-returns.php';
			require_once $base . 'admin/class-wp-fastspring-admin-sessions.php';
			require_once $base . 'admin/class-wp-fastspring-admin-events.php';
			require_once $base . 'admin/class-wp-fastspring-admin-reports.php';
			require_once $base . 'admin/class-wp-fastspring-admin-webhooks.php';
			require_once $base . 'admin/class-wp-fastspring-admin-settings.php';
			require_once $base . 'admin/class-wp-fastspring-admin-tools.php';
		}
	}

	/**
	 * Register hooks.
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), 10 );
		add_action( 'init', array( $this, 'on_init' ), 5 );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_notices', array( $this, 'maybe_render_woocommerce_notice' ) );
		add_filter( 'plugin_action_links_' . WP_FASTSPRING_BASENAME, array( $this, 'add_action_links' ) );
	}

	/**
	 * Plugins loaded.
	 */
	public function on_plugins_loaded() {
		$this->settings = new WP_FastSpring_Settings();
		$this->api      = new WP_FastSpring_API( $this->settings );

		new WP_FastSpring_Webhook( $this->api, $this->settings );
		new WP_FastSpring_Product_Sync( $this->api, $this->settings );

		if ( $this->is_woocommerce_active() ) {
			require_once WP_FASTSPRING_PATH . 'includes/class-wp-fastspring-wc-gateway-loader.php';
			new WP_FastSpring_WC_Gateway_Loader();
		}

		if ( is_admin() ) {
			new WP_FastSpring_Admin();
		}
	}

	/**
	 * Init.
	 */
	public function on_init() {
		WP_FastSpring_Data_Store::register_post_types();
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wp-fastspring', false, dirname( WP_FASTSPRING_BASENAME ) . '/languages' );
	}

	/**
	 * Activation hook.
	 */
	public static function activate() {
		require_once WP_FASTSPRING_PATH . 'includes/class-wp-fastspring-install.php';
		WP_FastSpring_Install::install();
		flush_rewrite_rules();
	}

	/**
	 * Deactivation hook.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Check if WooCommerce is active.
	 *
	 * @return bool
	 */
	public function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Notice when WooCommerce is missing.
	 */
	public function maybe_render_woocommerce_notice() {
		if ( $this->is_woocommerce_active() ) {
			return;
		}
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html__( 'WP FastSpring works best with WooCommerce. The dashboard will still operate, but the WooCommerce payment gateway is disabled until WooCommerce is activated.', 'wp-fastspring' )
		);
	}

	/**
	 * Add action links on the plugins page.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public function add_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=wp-fastspring' ) ) . '">' . esc_html__( 'Dashboard', 'wp-fastspring' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=wp-fastspring-settings' ) ) . '">' . esc_html__( 'Settings', 'wp-fastspring' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}
}
