<?php
/**
 * Main plugin loader.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP.
 */
final class VMS_EFWP {

	/**
	 * Singleton instance.
	 *
	 * @var VMS_EFWP|null
	 */
	private static $instance = null;

	/**
	 * API client.
	 *
	 * @var VMS_EFWP_API|null
	 */
	public $api = null;

	/**
	 * Settings handler.
	 *
	 * @var VMS_EFWP_Settings|null
	 */
	public $settings = null;

	/**
	 * Product sync handler.
	 *
	 * @var VMS_EFWP_Product_Sync|null
	 */
	public $product_sync = null;

	/**
	 * Returns the singleton.
	 *
	 * @return VMS_EFWP
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
		$base = VMS_EFWP_PATH . 'includes/';

		require_once $base . 'class-vms-efwp-install.php';
		require_once $base . 'class-vms-efwp-migrate.php';
		require_once $base . 'class-vms-efwp-settings.php';
		require_once $base . 'class-vms-efwp-api.php';
		require_once $base . 'class-vms-efwp-logger.php';
		require_once $base . 'class-vms-efwp-webhook-permissions.php';
		require_once $base . 'class-vms-efwp-webhook.php';
		require_once $base . 'class-vms-efwp-product-sync.php';
		require_once $base . 'class-vms-efwp-data-store.php';
		require_once $base . 'class-vms-efwp-stats.php';

		if ( is_admin() ) {
			require_once $base . 'admin/class-vms-efwp-admin-resource-base.php';
			require_once $base . 'admin/class-vms-efwp-admin.php';
			require_once $base . 'admin/class-vms-efwp-admin-dashboard.php';
			require_once $base . 'admin/class-vms-efwp-admin-orders.php';
			require_once $base . 'admin/class-vms-efwp-admin-subscriptions.php';
			require_once $base . 'admin/class-vms-efwp-admin-accounts.php';
			require_once $base . 'admin/class-vms-efwp-admin-products.php';
			require_once $base . 'admin/class-vms-efwp-admin-coupons.php';
			require_once $base . 'admin/class-vms-efwp-admin-invoices.php';
			require_once $base . 'admin/class-vms-efwp-admin-quotes.php';
			require_once $base . 'admin/class-vms-efwp-admin-returns.php';
			require_once $base . 'admin/class-vms-efwp-admin-sessions.php';
			require_once $base . 'admin/class-vms-efwp-admin-events.php';
			require_once $base . 'admin/class-vms-efwp-admin-reports.php';
			require_once $base . 'admin/class-vms-efwp-admin-webhooks.php';
			require_once $base . 'admin/class-vms-efwp-admin-settings.php';
			require_once $base . 'admin/class-vms-efwp-admin-tools.php';
		}
	}

	/**
	 * Register hooks.
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), 10 );
		add_action( 'init', array( $this, 'on_init' ), 5 );
		add_action( 'admin_notices', array( $this, 'maybe_render_woocommerce_notice' ) );
		add_filter( 'plugin_action_links_' . VMS_EFWP_BASENAME, array( $this, 'add_action_links' ) );
	}

	/**
	 * Plugins loaded.
	 */
	public function on_plugins_loaded() {
		VMS_EFWP_Migrate::maybe_run();

		$this->settings = new VMS_EFWP_Settings();
		$this->api      = new VMS_EFWP_API( $this->settings );

		new VMS_EFWP_Webhook( $this->api, $this->settings );
		$this->product_sync = new VMS_EFWP_Product_Sync( $this->api, $this->settings );

		if ( $this->is_woocommerce_active() ) {
			require_once VMS_EFWP_PATH . 'includes/class-vms-efwp-checkout-overlay.php';
			VMS_EFWP_Checkout_Overlay::init();
			require_once VMS_EFWP_PATH . 'includes/class-vms-efwp-wc-gateway-loader.php';
			new VMS_EFWP_WC_Gateway_Loader();
		}

		if ( is_admin() ) {
			new VMS_EFWP_Admin();
		}
	}

	/**
	 * Init.
	 */
	public function on_init() {
		VMS_EFWP_Data_Store::register_post_types();
	}

	/**
	 * Activation hook.
	 */
	public static function activate() {
		require_once VMS_EFWP_PATH . 'includes/class-vms-efwp-install.php';
		VMS_EFWP_Install::install();
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
			esc_html__( 'VMS Elements Fastspring Woo Payment works best with WooCommerce. The dashboard will still operate, but the WooCommerce payment gateway is disabled until WooCommerce is activated.', 'vms-elements-fastspring-woo-payment' )
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
			'<a href="' . esc_url( admin_url( 'admin.php?page=vms-efwp' ) ) . '">' . esc_html__( 'Dashboard', 'vms-elements-fastspring-woo-payment' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=vms-efwp-settings' ) ) . '">' . esc_html__( 'Settings', 'vms-elements-fastspring-woo-payment' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}
}
