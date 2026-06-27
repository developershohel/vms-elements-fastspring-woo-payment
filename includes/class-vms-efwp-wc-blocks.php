<?php
/**
 * WooCommerce Cart/Checkout Blocks integration for the FastSpring gateway.
 *
 * Without this, the FastSpring gateway will not appear on stores that use
 * the block-based Checkout (the default since WooCommerce 8.x, and required
 * to be visible on WC 10.x).
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

if ( ! class_exists( AbstractPaymentMethodType::class ) ) {
	return;
}

/**
 * Class VMS_EFWP_WC_Blocks.
 */
final class VMS_EFWP_WC_Blocks extends AbstractPaymentMethodType {

	/**
	 * Payment method name (must match gateway id).
	 *
	 * @var string
	 */
	protected $name = 'vms_efwp';

	/**
	 * Initialize: load the gateway-level WC settings.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_vms_efwp_settings', array() );
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
		$handle = 'vms-efwp-blocks';

		$asset_path = vms_efwp_asset_url( 'assets/js/blocks/checkout-block.js' );

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
				VMS_EFWP_VERSION,
				true
			);
		}

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( $handle, 'vms-elements-fastspring-woo-payment' );
		}

		return array( $handle );
	}

	/**
	 * Data passed to the block payment method (exposed as
	 * `wc.wcSettings.getSetting( 'vms_efwp_data' )` in JS).
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		$plugin_settings_raw = get_option( 'vms_efwp_settings', array() );
		if ( ! is_array( $plugin_settings_raw ) ) {
			$plugin_settings_raw = array();
		}

		$title = ! empty( $this->settings['title'] )
			? $this->settings['title']
			: ( ! empty( $plugin_settings_raw['gateway_title'] ) ? $plugin_settings_raw['gateway_title'] : __( 'Pay with FastSpring', 'vms-elements-fastspring-woo-payment' ) );

		$description = ! empty( $this->settings['description'] )
			? $this->settings['description']
			: ( ! empty( $plugin_settings_raw['gateway_description'] ) ? $plugin_settings_raw['gateway_description'] : __( 'Pay securely in a popup overlay powered by FastSpring.', 'vms-elements-fastspring-woo-payment' ) );

		return array(
			'title'       => $title,
			'description' => $description,
			'icons'       => array(),
			'supports'    => $this->get_supported_features(),
			'available'   => $this->is_checkout_ready(),
		);
	}

	/**
	 * Whether checkout prerequisites are satisfied for shoppers.
	 *
	 * @return bool
	 */
	private function is_checkout_ready() {
		if ( 'yes' !== ( isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'no' ) ) {
			return false;
		}

		if ( ! class_exists( 'VMS_EFWP_WC_Gateway' ) || ! function_exists( 'WC' ) ) {
			return false;
		}

		$gateways = WC()->payment_gateways()->payment_gateways();
		if ( empty( $gateways['vms_efwp'] ) || ! $gateways['vms_efwp'] instanceof VMS_EFWP_WC_Gateway ) {
			return false;
		}

		return empty( $gateways['vms_efwp']->get_availability_issues() );
	}

	/**
	 * Features supported by the gateway.
	 *
	 * @return array
	 */
	public function get_supported_features() {
		$features = array( 'products', 'refunds' );
		if ( class_exists( 'VMS_EFWP_WC_Gateway' ) ) {
			$gateways = WC()->payment_gateways()->payment_gateways();
			if ( isset( $gateways['vms_efwp'] ) && is_array( $gateways['vms_efwp']->supports ) ) {
				$features = $gateways['vms_efwp']->supports;
			}
		}
		return $features;
	}
}
