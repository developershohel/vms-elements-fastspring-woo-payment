<?php
/**
 * Loads the WooCommerce payment gateway, only when WooCommerce is active.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_WC_Gateway_Loader.
 */
class WP_FastSpring_WC_Gateway_Loader {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register' ) );
		add_action( 'plugins_loaded', array( $this, 'include_gateway' ), 11 );
		add_action( 'before_woocommerce_init', array( $this, 'declare_compat' ) );

		// Register the Blocks payment method type as early and as broadly as
		// possible. We hook the canonical `woocommerce_blocks_payment_method_type_registration`
		// directly and lazy-load the class inside the callback so the order of
		// loading between WC core and our plugin can't break the integration.
		add_action( 'woocommerce_blocks_payment_method_type_registration', array( $this, 'register_payment_method_type' ) );
	}

	/**
	 * Declare HPOS / Cart Block compatibility.
	 */
	public function declare_compat() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WP_FASTSPRING_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', WP_FASTSPRING_FILE, true );
		}
	}

	/**
	 * Include gateway class once WC is loaded.
	 */
	public function include_gateway() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}
		require_once WP_FASTSPRING_PATH . 'includes/class-wp-fastspring-wc-gateway.php';
	}

	/**
	 * Register the gateway with WooCommerce (classic checkout).
	 *
	 * @param array $methods Methods.
	 * @return array
	 */
	public function register( $methods ) {
		if ( class_exists( 'WP_FastSpring_WC_Gateway' ) ) {
			$methods[] = 'WP_FastSpring_WC_Gateway';
		}
		return $methods;
	}

	/**
	 * Register the FastSpring payment method type with the WooCommerce Blocks
	 * Cart/Checkout Payment Method Registry. This is what makes the gateway
	 * show up on the block-based checkout (the default in modern WC).
	 *
	 * @param mixed $registry Payment Method Registry.
	 */
	public function register_payment_method_type( $registry ) {
		if ( ! $registry || ! is_object( $registry ) || ! method_exists( $registry, 'register' ) ) {
			return;
		}
		if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			return;
		}
		require_once WP_FASTSPRING_PATH . 'includes/class-wp-fastspring-wc-blocks.php';
		if ( ! class_exists( 'WP_FastSpring_WC_Blocks' ) ) {
			return;
		}
		$registry->register( new WP_FastSpring_WC_Blocks() );
	}
}
