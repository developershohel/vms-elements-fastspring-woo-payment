<?php
/**
 * WooCommerce Cart/Checkout Blocks integration for the FastSpring gateway.
 *
 * Without this, the FastSpring gateway will not appear on stores that use
 * the block-based Checkout (the default since WooCommerce 8.x, and required
 * to be visible on WC 10.x).
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

if ( ! class_exists( AbstractPaymentMethodType::class ) ) {
	return;
}

/**
 * Class WP_FastSpring_WC_Blocks.
 */
final class WP_FastSpring_WC_Blocks extends AbstractPaymentMethodType {

	/**
	 * Payment method name (must match gateway id).
	 *
	 * @var string
	 */
	protected $name = 'wp_fastspring';

	/**
	 * Initialize: load the gateway-level WC settings.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_wp_fastspring_settings', array() );
		if ( ! is_array( $this->settings ) ) {
			$this->settings = array();
		}
	}

	/**
	 * Should this payment method be available?
	 *
	 * Intentionally lenient: returns true as long as the WC-level enabled
	 * toggle is on. We let the gateway's own `is_available()` (called during
	 * the actual checkout request) do the per-cart filtering. This matches
	 * how WC core gateways work and avoids the gateway being silently absent
	 * from the block checkout because of an unrelated condition.
	 *
	 * @return bool
	 */
	public function is_active() {
		$enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'no';
		return 'yes' === $enabled;
	}

	/**
	 * Register the script that the block checkout loads to render the gateway.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$handle = 'wp-fastspring-blocks';

		$asset_path = WP_FASTSPRING_URL . 'assets/js/blocks/checkout-block.js';

		if ( ! wp_script_is( $handle, 'registered' ) ) {
			wp_register_script(
				$handle,
				$asset_path,
				array(
					'wc-blocks-registry',
					'wc-settings',
					'wp-element',
					'wp-html-entities',
					'wp-i18n',
				),
				WP_FASTSPRING_VERSION,
				true
			);
		}

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( $handle, 'wp-fastspring' );
		}

		return array( $handle );
	}

	/**
	 * Data passed to the block payment method (exposed as
	 * `wc.wcSettings.getSetting( 'wp_fastspring_data' )` in JS).
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		$plugin_settings_raw = get_option( 'wp_fastspring_settings', array() );
		if ( ! is_array( $plugin_settings_raw ) ) {
			$plugin_settings_raw = array();
		}

		$title = ! empty( $this->settings['title'] )
			? $this->settings['title']
			: ( ! empty( $plugin_settings_raw['gateway_title'] ) ? $plugin_settings_raw['gateway_title'] : __( 'Pay with FastSpring', 'wp-fastspring' ) );

		$description = ! empty( $this->settings['description'] )
			? $this->settings['description']
			: ( ! empty( $plugin_settings_raw['gateway_description'] ) ? $plugin_settings_raw['gateway_description'] : __( 'Secure checkout powered by FastSpring.', 'wp-fastspring' ) );

		return array(
			'title'       => $title,
			'description' => $description,
			'icons'       => array(),
			'supports'    => $this->get_supported_features(),
		);
	}

	/**
	 * Features supported by the gateway.
	 *
	 * @return array
	 */
	public function get_supported_features() {
		$features = array( 'products', 'refunds' );
		if ( class_exists( 'WP_FastSpring_WC_Gateway' ) ) {
			$gateways = WC()->payment_gateways()->payment_gateways();
			if ( isset( $gateways['wp_fastspring'] ) && is_array( $gateways['wp_fastspring']->supports ) ) {
				$features = $gateways['wp_fastspring']->supports;
			}
		}
		return $features;
	}
}
