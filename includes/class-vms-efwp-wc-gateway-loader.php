<?php
/**
 * Loads the WooCommerce payment gateway and checkout integrations (free core).
 *
 * Classic checkout, block checkout, and popup overlay checkout are included
 * in the free plugin. Pro adds catalog/subscription management tools.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_WC_Gateway_Loader.
 */
class VMS_EFWP_WC_Gateway_Loader {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register' ) );
		add_action( 'plugins_loaded', array( $this, 'include_gateway' ), 11 );
		add_action( 'before_woocommerce_init', array( $this, 'declare_compat' ) );

		require_once VMS_EFWP_PATH . 'includes/class-vms-efwp-checkout-overlay.php';
		VMS_EFWP_Checkout_Overlay::init();

		require_once VMS_EFWP_PATH . 'includes/class-vms-efwp-checkout-loader.php';
		new VMS_EFWP_Checkout_Loader();
	}

	/**
	 * Declare HPOS / Cart Block compatibility.
	 */
	public function declare_compat() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', VMS_EFWP_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', VMS_EFWP_FILE, true );
		}
	}

	/**
	 * Include gateway class once WC is loaded.
	 */
	public function include_gateway() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}
		require_once VMS_EFWP_PATH . 'includes/class-vms-efwp-wc-gateway.php';
	}

	/**
	 * Register the gateway with WooCommerce (classic checkout).
	 *
	 * @param array $methods Methods.
	 * @return array
	 */
	public function register( $methods ) {
		if ( class_exists( 'VMS_EFWP_WC_Gateway' ) ) {
			$methods[] = 'VMS_EFWP_WC_Gateway';
		}
		return $methods;
	}
}
