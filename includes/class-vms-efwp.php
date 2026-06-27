<?php
/**
 * Main plugin loader (free core only — Pro code ships in the separate Pro add-on).
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
	 * Load required files (free core only).
	 */
	private function includes() {
		$base = VMS_EFWP_PATH . 'includes/';

		require_once $base . 'class-vms-efwp-install.php';
		require_once $base . 'class-vms-efwp-migrate.php';
		require_once $base . 'class-vms-efwp-settings.php';
		require_once $base . 'class-vms-efwp-api.php';
		require_once $base . 'class-vms-efwp-logger.php';
		require_once $base . 'class-vms-efwp-assets.php';
		require_once $base . 'class-vms-efwp-webhook-permissions.php';
		require_once $base . 'class-vms-efwp-webhook.php';
		require_once $base . 'class-vms-efwp-data-store.php';

		if ( ! class_exists( 'VMS_EFWP_Stats', false ) ) {
			require_once $base . 'class-vms-efwp-stats.php';
		}

		if ( is_admin() ) {
			require_once $base . 'admin/class-vms-efwp-admin-resource-base.php';
			require_once $base . 'admin/class-vms-efwp-admin.php';
			require_once $base . 'admin/class-vms-efwp-admin-dashboard.php';
			require_once $base . 'admin/class-vms-efwp-admin-orders.php';
			require_once $base . 'admin/class-vms-efwp-admin-settings.php';
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

		VMS_EFWP_Install::maybe_upgrade();
		VMS_EFWP_Install::maybe_backfill_order_invoices();
		VMS_EFWP_Install::maybe_backfill_user_scope();

		new VMS_EFWP_Webhook( $this->api, $this->settings );

		if ( $this->is_woocommerce_active() ) {
			require_once VMS_EFWP_PATH . 'includes/class-vms-efwp-wc-gateway-loader.php';
			new VMS_EFWP_WC_Gateway_Loader();
		}

		if ( is_admin() ) {
			new VMS_EFWP_Admin();
		}

		/**
		 * Fires after the free plugin core has loaded. The Pro add-on hooks here.
		 */
		do_action( 'vms_efwp_loaded' );
	}

	/**
	 * Init.
	 */
	public function on_init() {
		VMS_EFWP_Data_Store::register_post_types();

		/**
		 * Fires on init after free core registration. Pro modules hook here.
		 */
		do_action( 'vms_efwp_init' );
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
		if ( ! vms_efwp_is_pro() ) {
			$plugin_links[] = '<a href="' . esc_url( VMS_EFWP_Features::pro_url() ) . '" target="_blank" rel="noopener noreferrer" style="color:#2271b1;font-weight:600;">' . esc_html__( 'Get Pro', 'vms-elements-fastspring-woo-payment' ) . '</a>';
		}
		return array_merge( $plugin_links, $links );
	}
}
