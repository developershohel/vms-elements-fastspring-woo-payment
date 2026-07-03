<?php
/**
 * Loads the WooCommerce payment gateway and checkout integrations.
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFPG_WC_Gateway_Loader.
 */
class VMS_EFPG_WC_Gateway_Loader {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register' ) );
		add_action( 'plugins_loaded', array( $this, 'include_gateway' ), 11 );
		add_action( 'before_woocommerce_init', array( $this, 'declare_compat' ) );

		require_once VMS_EFPG_PATH . 'includes/class-vms-efpg-checkout-overlay.php';
		VMS_EFPG_Checkout_Overlay::init();

		require_once VMS_EFPG_PATH . 'includes/class-vms-efpg-checkout-loader.php';
		new VMS_EFPG_Checkout_Loader();
	}

	/**
	 * Declare HPOS / Cart Block compatibility.
	 */
	public function declare_compat() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', VMS_EFPG_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', VMS_EFPG_FILE, true );
		}
	}

	/**
	 * Include gateway class once WC is loaded.
	 */
	public function include_gateway() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}
		require_once VMS_EFPG_PATH . 'includes/class-vms-efpg-wc-gateway.php';
	}

	/**
	 * Register the gateway with WooCommerce (classic checkout).
	 *
	 * @param array $methods Methods.
	 * @return array
	 */
	public function register( $methods ) {
		if ( class_exists( 'VMS_EFPG_WC_Gateway' ) ) {
			$methods[] = 'VMS_EFPG_WC_Gateway';
		}
		return $methods;
	}
}
