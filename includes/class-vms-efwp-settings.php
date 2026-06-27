<?php
/**
 * Settings store.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Settings.
 *
 * Lightweight wrapper around the vms_efwp_settings option that knows
 * how to expose the right credentials based on the active mode.
 */
class VMS_EFWP_Settings {

	const OPTION_KEY = 'vms_efwp_settings';

	/**
	 * Default product path used for the auto-provisioned catch-all
	 * "Custom Price" product when single_custom_price mode is active and the
	 * merchant has not configured their own path.
	 */
	const DEFAULT_CUSTOM_PRICE_PATH = 'wc-checkout';

	/**
	 * Cached settings array.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->refresh();
	}

	/**
	 * Reload from DB.
	 */
	public function refresh() {
		$data = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		$this->data = $data;
	}

	/**
	 * Default values.
	 *
	 * @return array
	 */
	public function defaults() {
		return array(
			'mode'                       => 'sandbox',
			'live_username'              => '',
			'live_password'              => '',
			'live_storefront'            => '',
			'sandbox_username'           => '',
			'sandbox_password'           => '',
			'sandbox_storefront'         => '',
			'webhook_secret_live'        => '',
			'webhook_secret_sandbox'     => '',
			'enable_webhook'             => 'yes',
			'webhook_enabled_events_live'        => array(),
			'webhook_enabled_events_sandbox'     => array(),
			'webhook_enabled_events_live_synced_at'    => 0,
			'webhook_enabled_events_sandbox_synced_at' => 0,
			'enable_logging'             => 'yes',
			'sync_products'              => 'no',
			'popup_path'                 => '',
			'sandbox_access_key'         => '',
			'live_access_key'            => '',
			'gateway_title'              => __( 'Pay with FastSpring', 'vms-elements-fastspring-woo-payment' ),
			'gateway_description'        => __( 'Pay securely in a popup overlay powered by FastSpring.', 'vms-elements-fastspring-woo-payment' ),
			'pricing_strategy'           => 'single_custom_price',
			'custom_price_product_path'  => '',
			'checkout_page_id'           => 0,
			'payment_success_page_id'    => 0,
			'payment_success_show_details' => 'yes',
		);
	}

	/**
	 * Returns the active pricing strategy.
	 *
	 * @return string One of: catalog | per_product_override | single_custom_price.
	 */
	public function pricing_strategy() {
		$value = $this->get( 'pricing_strategy', 'single_custom_price' );
		$value = in_array( $value, array( 'catalog', 'per_product_override', 'single_custom_price' ), true ) ? $value : 'single_custom_price';

		if ( ! vms_efwp_is_pro() && 'catalog' === $value ) {
			$value = 'single_custom_price';
		}
		if ( ! vms_efwp_is_pro() && 'per_product_override' === $value ) {
			$value = 'single_custom_price';
		}

		return $value;
	}

	/**
	 * The FastSpring product path used in single_custom_price mode.
	 *
	 * Falls back to the auto-provisioned default path so checkout can work the
	 * moment the strategy is selected, before the merchant configures anything.
	 *
	 * @return string
	 */
	public function custom_price_product_path() {
		$path = (string) $this->get( 'custom_price_product_path', '' );
		return '' !== $path ? $path : self::DEFAULT_CUSTOM_PRICE_PATH;
	}

	/**
	 * Get a single setting with optional default.
	 *
	 * @param string $key Key.
	 * @param mixed  $default_value Fallback value.
	 * @return mixed
	 */
	public function get( $key, $default_value = '' ) {
		if ( isset( $this->data[ $key ] ) && '' !== $this->data[ $key ] ) {
			return $this->data[ $key ];
		}
		$defaults = $this->defaults();
		if ( isset( $defaults[ $key ] ) && '' !== $defaults[ $key ] ) {
			return $defaults[ $key ];
		}
		return $default_value;
	}

	/**
	 * Update a single setting.
	 *
	 * @param string $key Key.
	 * @param mixed  $value Value.
	 */
	public function set( $key, $value ) {
		$this->data[ $key ] = $value;
		update_option( self::OPTION_KEY, $this->data );
	}

	/**
	 * Replace all settings.
	 *
	 * @param array $values Values.
	 */
	public function update_all( array $values ) {
		$this->data = array_merge( $this->defaults(), $values );
		update_option( self::OPTION_KEY, $this->data );
	}

	/**
	 * Returns the active mode (live or sandbox).
	 *
	 * @return string
	 */
	public function get_mode() {
		$mode = $this->get( 'mode', 'sandbox' );
		return in_array( $mode, array( 'live', 'sandbox' ), true ) ? $mode : 'sandbox';
	}

	/**
	 * Whether the plugin is in test/sandbox mode.
	 *
	 * @return bool
	 */
	public function is_sandbox() {
		return 'sandbox' === $this->get_mode();
	}

	/**
	 * Returns API username for the active mode.
	 *
	 * @return string
	 */
	public function api_username() {
		return $this->is_sandbox() ? $this->get( 'sandbox_username' ) : $this->get( 'live_username' );
	}

	/**
	 * Returns API password for the active mode.
	 *
	 * @return string
	 */
	public function api_password() {
		return $this->is_sandbox() ? $this->get( 'sandbox_password' ) : $this->get( 'live_password' );
	}

	/**
	 * Raw storefront value for the active mode (may include popup path).
	 *
	 * @return string
	 */
	private function raw_storefront_value() {
		return trim( (string) ( $this->is_sandbox() ? $this->get( 'sandbox_storefront' ) : $this->get( 'live_storefront' ) ) );
	}

	/**
	 * Normalize a storefront value to the hostname only.
	 *
	 * @param string $raw Raw value.
	 * @return string
	 */
	private function normalize_storefront_host( $raw ) {
		$raw = preg_replace( '#^https?://#', '', $raw );
		$raw = trim( $raw, '/' );
		if ( false !== strpos( $raw, '/' ) ) {
			return substr( $raw, 0, strpos( $raw, '/' ) );
		}
		return $raw;
	}

	/**
	 * Detect popup checkout path when pasted as part of a full data-storefront value.
	 *
	 * @param string $raw Raw storefront value.
	 * @return string
	 */
	private function detect_popup_path_from_raw( $raw ) {
		$raw = preg_replace( '#^https?://#', '', trim( $raw, '/' ) );
		if ( false === strpos( $raw, '/' ) ) {
			return '';
		}
		$parts = explode( '/', $raw, 2 );
		return isset( $parts[1] ) ? trim( $parts[1], '/' ) : '';
	}

	/**
	 * Returns storefront identifier for the active mode.
	 *
	 * Common values look like "yourcompany.test.onfastspring.com" or
	 * "yourcompany.onfastspring.com".
	 *
	 * @return string
	 */
	public function storefront() {
		return $this->normalize_storefront_host( $this->raw_storefront_value() );
	}

	/**
	 * The Store Builder Library `data-storefront` value used to open the popup
	 * checkout. Combines the active storefront domain with the popup checkout
	 * path (e.g. "popup-vmsuniverse2026").
	 *
	 * @return string
	 */
	public function popup_storefront() {
		$domain = $this->storefront();
		if ( '' === $domain ) {
			return '';
		}

		$path = trim( (string) $this->get( 'popup_path', '' ), '/' );
		if ( '' === $path ) {
			$path = $this->detect_popup_path_from_raw( $this->raw_storefront_value() );
		}

		return '' !== $path ? $domain . '/' . $path : $domain;
	}

	/**
	 * Whether a popup checkout path is configured for Store Builder overlay mode.
	 *
	 * @return bool
	 */
	public function has_popup_checkout() {
		$popup  = $this->popup_storefront();
		$domain = $this->storefront();
		return '' !== $popup && '' !== $domain && $popup !== $domain;
	}

	/**
	 * Default checkout path for the Sessions V2 API (`storeId/checkoutId`).
	 *
	 * Derived from the configured storefront host and popup checkout path.
	 *
	 * @return string
	 */
	public function checkout_path() {
		$domain = $this->storefront();
		if ( '' === $domain ) {
			return '';
		}

		$store_id = preg_replace( '/\.(test\.)?onfastspring\.com$/i', '', $domain );
		$checkout_id = trim( (string) $this->get( 'popup_path', '' ), '/' );
		if ( '' === $checkout_id ) {
			$checkout_id = $this->detect_popup_path_from_raw( $this->raw_storefront_value() );
		}

		if ( '' === $store_id || '' === $checkout_id ) {
			return '';
		}

		return $store_id . '/' . $checkout_id;
	}

	/**
	 * Store Builder Library access key for the active mode.
	 *
	 * Found in FastSpring App → Developer Tools → Store Builder Library.
	 *
	 * @return string
	 */
	public function access_key() {
		return $this->is_sandbox() ? (string) $this->get( 'sandbox_access_key' ) : (string) $this->get( 'live_access_key' );
	}

	/**
	 * Whether a Store Builder access key is configured.
	 *
	 * @return bool
	 */
	public function has_access_key() {
		return '' !== $this->access_key();
	}

	/**
	 * Returns webhook HMAC secret for the active mode.
	 *
	 * @return string
	 */
	public function webhook_secret() {
		return $this->is_sandbox() ? $this->get( 'webhook_secret_sandbox' ) : $this->get( 'webhook_secret_live' );
	}

	/**
	 * Whether credentials for the active mode are filled.
	 *
	 * @return bool
	 */
	public function has_credentials() {
		return '' !== $this->api_username() && '' !== $this->api_password();
	}

	/**
	 * URL where FastSpring should POST webhook events.
	 *
	 * @return string
	 */
	public function webhook_url() {
		return add_query_arg( array( 'vms-efwp-webhook' => 1 ), home_url( '/' ) );
	}
}
