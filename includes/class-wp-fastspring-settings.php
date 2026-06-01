<?php
/**
 * Settings store.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Settings.
 *
 * Lightweight wrapper around the wp_fastspring_settings option that knows
 * how to expose the right credentials based on the active mode.
 */
class WP_FastSpring_Settings {

	const OPTION_KEY = 'wp_fastspring_settings';

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
			'enable_logging'             => 'yes',
			'sync_products'              => 'no',
			'gateway_title'              => __( 'Pay with FastSpring', 'wp-fastspring' ),
			'gateway_description'        => __( 'Secure checkout powered by FastSpring.', 'wp-fastspring' ),
			'pricing_strategy'           => 'per_product_override',
			'custom_price_product_path'  => '',
		);
	}

	/**
	 * Returns the active pricing strategy.
	 *
	 * @return string One of: catalog | per_product_override | single_custom_price.
	 */
	public function pricing_strategy() {
		$value = $this->get( 'pricing_strategy', 'per_product_override' );
		return in_array( $value, array( 'catalog', 'per_product_override', 'single_custom_price' ), true ) ? $value : 'per_product_override';
	}

	/**
	 * The FastSpring product path used in single_custom_price mode.
	 *
	 * @return string
	 */
	public function custom_price_product_path() {
		return (string) $this->get( 'custom_price_product_path', '' );
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
	 * Returns storefront identifier for the active mode.
	 *
	 * Common values look like "yourcompany.test.onfastspring.com" or
	 * "yourcompany.onfastspring.com".
	 *
	 * @return string
	 */
	public function storefront() {
		return $this->is_sandbox() ? $this->get( 'sandbox_storefront' ) : $this->get( 'live_storefront' );
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
		return add_query_arg( array( 'wp-fastspring-webhook' => 1 ), home_url( '/' ) );
	}
}
