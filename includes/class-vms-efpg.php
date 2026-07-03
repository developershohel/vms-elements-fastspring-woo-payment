<?php
/**
 * Main plugin loader.
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFPG.
 */
final class VMS_EFPG {

	/**
	 * Singleton instance.
	 *
	 * @var VMS_EFPG|null
	 */
	private static $instance = null;

	/**
	 * API client.
	 *
	 * @var VMS_EFPG_API|null
	 */
	public $api = null;

	/**
	 * Settings handler.
	 *
	 * @var VMS_EFPG_Settings|null
	 */
	public $settings = null;

	/**
	 * Returns the singleton.
	 *
	 * @return VMS_EFPG
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
		$base = VMS_EFPG_PATH . 'includes/';

		require_once $base . 'class-vms-efpg-install.php';
		require_once $base . 'class-vms-efpg-migrate.php';
		require_once $base . 'class-vms-efpg-settings.php';
		require_once $base . 'class-vms-efpg-api.php';
		require_once $base . 'class-vms-efpg-logger.php';
		require_once $base . 'class-vms-efpg-assets.php';
		require_once $base . 'class-vms-efpg-webhook-permissions.php';
		require_once $base . 'class-vms-efpg-webhook.php';
		require_once $base . 'class-vms-efpg-data-store.php';

		if ( ! class_exists( 'VMS_EFPG_Stats', false ) ) {
			require_once $base . 'class-vms-efpg-stats.php';
		}

		if ( is_admin() ) {
			require_once $base . 'admin/class-vms-efpg-admin-resource-base.php';
			require_once $base . 'admin/class-vms-efpg-admin.php';
			require_once $base . 'admin/class-vms-efpg-admin-dashboard.php';
			require_once $base . 'admin/class-vms-efpg-admin-orders.php';
			require_once $base . 'admin/class-vms-efpg-admin-invoice-actions.php';
			require_once $base . 'admin/class-vms-efpg-admin-settings.php';
		}
	}

	/**
	 * Register hooks.
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), 10 );
		add_action( 'init', array( $this, 'on_init' ), 5 );
		add_action( 'admin_notices', array( $this, 'maybe_render_woocommerce_notice' ) );
		add_filter( 'plugin_action_links_' . VMS_EFPG_BASENAME, array( $this, 'add_action_links' ) );
	}

	/**
	 * Plugins loaded.
	 */
	public function on_plugins_loaded() {
		VMS_EFPG_Migrate::maybe_run();

		$this->settings = new VMS_EFPG_Settings();
		$this->api      = new VMS_EFPG_API( $this->settings );

		VMS_EFPG_Install::maybe_upgrade();
		VMS_EFPG_Install::maybe_backfill_order_invoices();
		VMS_EFPG_Install::maybe_backfill_user_scope();

		new VMS_EFPG_Webhook( $this->api, $this->settings );

		if ( $this->is_woocommerce_active() ) {
			require_once VMS_EFPG_PATH . 'includes/class-vms-efpg-wc-gateway-loader.php';
			new VMS_EFPG_WC_Gateway_Loader();
		}

		if ( is_admin() ) {
			new VMS_EFPG_Admin();
		}
	}

	/**
	 * Init.
	 */
	public function on_init() {
		VMS_EFPG_Data_Store::register_post_types();
	}

	/**
	 * Activation hook.
	 */
	public static function activate() {
		require_once VMS_EFPG_PATH . 'includes/class-vms-efpg-install.php';
		VMS_EFPG_Install::install();
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
			esc_html__( 'VMS Elements Payment Gateway with FastSpring for WooCommerce works best with WooCommerce. The dashboard will still operate, but the WooCommerce payment gateway is disabled until WooCommerce is activated.', 'vms-elements-fastspring-payment-gateway' )
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
			'<a href="' . esc_url( admin_url( 'admin.php?page=vms-efpg' ) ) . '">' . esc_html__( 'Dashboard', 'vms-elements-fastspring-payment-gateway' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=vms-efpg-settings' ) ) . '">' . esc_html__( 'Settings', 'vms-elements-fastspring-payment-gateway' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}
}
