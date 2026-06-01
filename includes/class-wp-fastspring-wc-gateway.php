<?php
/**
 * WooCommerce payment gateway powered by FastSpring sessions.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_WC_Gateway.
 */
class WP_FastSpring_WC_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor.
	 *
	 * Carefully avoids depending on wp_fastspring()->settings being initialised
	 * because some sites instantiate gateways extremely early (e.g. for cron
	 * or REST routes) before our plugin's `plugins_loaded` callback has run.
	 */
	public function __construct() {
		$this->id                 = 'wp_fastspring';
		$this->method_title       = __( 'FastSpring', 'wp-fastspring' );
		$this->method_description = __( 'Accept credit cards, PayPal, Apple Pay, Google Pay and more via FastSpring hosted checkout.', 'wp-fastspring' );
		$this->has_fields         = false;
		$this->supports           = array( 'products', 'refunds' );

		$this->init_form_fields();
		$this->init_settings();

		$plugin_settings_raw = get_option( WP_FastSpring_Settings::OPTION_KEY, array() );
		if ( ! is_array( $plugin_settings_raw ) ) {
			$plugin_settings_raw = array();
		}
		$default_title = isset( $plugin_settings_raw['gateway_title'] ) && '' !== $plugin_settings_raw['gateway_title']
			? $plugin_settings_raw['gateway_title']
			: __( 'Pay with FastSpring', 'wp-fastspring' );
		$default_desc  = isset( $plugin_settings_raw['gateway_description'] ) && '' !== $plugin_settings_raw['gateway_description']
			? $plugin_settings_raw['gateway_description']
			: __( 'Secure checkout powered by FastSpring.', 'wp-fastspring' );

		$this->title       = $this->get_option( 'title', $default_title );
		$this->description = $this->get_option( 'description', $default_desc );
		$this->enabled     = $this->get_option( 'enabled', 'no' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_wp_fastspring_return', array( $this, 'handle_return' ) );

		// Ensure the FastSpring storefront URL survives the WooCommerce Store API
		// safe-redirect filter (which by default only allows the site host).
		add_filter( 'woocommerce_store_api_disable_nonce_check', '__return_false' );
		add_filter( 'allowed_redirect_hosts', array( $this, 'allow_fastspring_redirect_host' ), 10, 1 );
	}

	/**
	 * Whitelist the FastSpring storefront host for wp_safe_redirect-style
	 * checks used by WC's Store API checkout endpoint.
	 *
	 * @param array $hosts Hosts.
	 * @return array
	 */
	public function allow_fastspring_redirect_host( $hosts ) {
		$settings = function_exists( 'wp_fastspring' ) ? wp_fastspring()->settings : null;
		if ( ! $settings ) {
			return $hosts;
		}
		$storefront = $settings->storefront();
		if ( $storefront ) {
			$hosts[] = $storefront;
		}
		// FastSpring may also redirect through its main domain.
		$hosts[] = 'fastspring.com';
		$hosts[] = 'onfastspring.com';
		return array_values( array_unique( array_filter( $hosts ) ) );
	}

	/**
	 * Configure the per-gateway form fields (kept minimal; main settings live in our plugin page).
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'         => array(
				'title'   => __( 'Enable/Disable', 'wp-fastspring' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable FastSpring', 'wp-fastspring' ),
				'default' => 'no',
			),
			'title'           => array(
				'title'       => __( 'Title', 'wp-fastspring' ),
				'type'        => 'text',
				'description' => __( 'Title shown to customers at checkout.', 'wp-fastspring' ),
				'default'     => __( 'Pay with FastSpring', 'wp-fastspring' ),
				'desc_tip'    => true,
			),
			'description'     => array(
				'title'       => __( 'Description', 'wp-fastspring' ),
				'type'        => 'textarea',
				'description' => __( 'Description shown to customers at checkout.', 'wp-fastspring' ),
				'default'     => __( 'Secure checkout powered by FastSpring.', 'wp-fastspring' ),
			),
		);
	}

	/**
	 * Diagnostic helper: returns an array of reason codes preventing the
	 * gateway from showing. Empty array = good to go.
	 *
	 * @return array
	 */
	public function get_availability_issues() {
		$issues = array();

		if ( 'yes' !== $this->enabled ) {
			$issues[] = 'gateway_disabled';
		}

		$plugin_settings = function_exists( 'wp_fastspring' ) ? wp_fastspring()->settings : null;
		if ( ! $plugin_settings ) {
			$issues[] = 'plugin_not_loaded';
			return $issues;
		}
		if ( ! $plugin_settings->has_credentials() ) {
			$issues[] = 'missing_credentials';
		}
		if ( '' === $plugin_settings->storefront() ) {
			$issues[] = 'missing_storefront';
		}

		return $issues;
	}

	/**
	 * Whether the gateway is available for the current cart.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( ! parent::is_available() ) {
			return false;
		}

		$settings = wp_fastspring()->settings;
		if ( ! $settings ) {
			return false;
		}

		$reason = '';
		if ( ! $settings->has_credentials() ) {
			$reason = 'missing_credentials';
		} elseif ( '' === $settings->storefront() ) {
			$reason = 'missing_storefront';
		}

		if ( $reason ) {
			if ( class_exists( 'WP_FastSpring_Logger' ) ) {
				WP_FastSpring_Logger::warning(
					'FastSpring gateway hidden at checkout: ' . $reason,
					'gateway',
					array( 'mode' => $settings->get_mode() )
				);
			}
			// Show to admins so they can diagnose; hide for shoppers.
			return current_user_can( 'manage_woocommerce' );
		}

		return true;
	}

	/**
	 * Append admin-only availability hints to the description.
	 *
	 * @return string
	 */
	public function get_description() {
		$desc = parent::get_description();
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return $desc;
		}
		$settings = wp_fastspring()->settings;
		if ( ! $settings ) {
			return $desc;
		}
		$messages = array();
		if ( ! $settings->has_credentials() ) {
			$messages[] = __( 'Admin-only notice: FastSpring API credentials are missing for the active mode.', 'wp-fastspring' );
		}
		if ( '' === $settings->storefront() ) {
			$messages[] = __( 'Admin-only notice: FastSpring storefront ID is empty. Customers will not see this gateway until you set it.', 'wp-fastspring' );
		}
		if ( $messages ) {
			$desc .= '<br /><em style="color:#b91c1c;">' . esc_html( implode( ' ', $messages ) ) . '</em>';
		}
		return $desc;
	}

	/**
	 * Build the FastSpring session line items based on the active pricing strategy.
	 *
	 * @param WC_Order $order    WooCommerce order.
	 * @param string   $strategy Pricing strategy.
	 * @param string   $currency Order currency.
	 * @return array
	 */
	private function build_session_items( $order, $strategy, $currency ) {
		$settings = wp_fastspring()->settings;
		$items    = array();

		if ( 'single_custom_price' === $strategy ) {
			$path = $settings->custom_price_product_path();
			if ( '' === $path ) {
				return array();
			}
			$total = (float) $order->get_subtotal();
			$total -= (float) $order->get_total_discount();
			$total  = max( 0.01, round( $total, 4 ) );
			$items[] = array(
				'product'  => $path,
				'quantity' => 1,
				'pricing'  => array(
					'price' => array( $currency => $total ),
				),
			);
			return $items;
		}

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( ! $product ) {
				continue;
			}
			$slug = get_post_meta( $product->get_id(), '_fastspring_product_path', true );
			if ( ! $slug ) {
				$slug = sanitize_title( $product->get_slug() );
			}
			$qty = max( 1, (int) $item->get_quantity() );

			$row = array(
				'product'  => $slug,
				'quantity' => $qty,
			);

			if ( 'per_product_override' === $strategy ) {
				$line_total = (float) $item->get_total();
				$unit_price = $qty > 0 ? round( $line_total / $qty, 4 ) : $line_total;
				$row['pricing'] = array(
					'price' => array( $currency => $unit_price ),
				);
			}

			$items[] = $row;
		}

		return $items;
	}

	/**
	 * Process payment by creating a FastSpring session and redirecting to its storefront URL.
	 *
	 * Errors are thrown as exceptions so the WooCommerce Store API (Block-based
	 * Checkout) surfaces the real message to the customer instead of a generic
	 * 400. Classic checkout will still receive them via wc_add_notice via the
	 * exception handler in WC core.
	 *
	 * @param int $order_id Order id.
	 * @return array
	 * @throws Exception When the session cannot be created.
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			$this->fail_payment( __( 'Order could not be loaded.', 'wp-fastspring' ) );
		}

		$settings = wp_fastspring()->settings;
		$api      = wp_fastspring()->api;

		// 1) Sanity check: credentials + storefront.
		if ( ! $settings->has_credentials() ) {
			$this->fail_payment( __( 'FastSpring API credentials are not configured for the active mode.', 'wp-fastspring' ) );
		}
		if ( '' === $settings->storefront() ) {
			$this->fail_payment( __( 'FastSpring storefront ID is empty. Please configure it before accepting payments.', 'wp-fastspring' ) );
		}

		$strategy = $settings->pricing_strategy();
		$currency = strtoupper( $order->get_currency() );

		// 2) Strategy-specific guards.
		if ( 'single_custom_price' === $strategy && '' === $settings->custom_price_product_path() ) {
			$this->fail_payment( __( 'Single Custom Price strategy is selected, but no Custom Price product path is configured in FastSpring settings.', 'wp-fastspring' ) );
		}

		$items = $this->build_session_items( $order, $strategy, $currency );
		if ( empty( $items ) ) {
			$this->fail_payment( __( 'No cart items could be matched to FastSpring products. Make sure every product in the cart has a FastSpring path or enable product sync.', 'wp-fastspring' ) );
		}

		// 3) Build payload.
		$payload = array(
			'tags'     => array( 'wc_order_id' => (string) $order_id ),
			'items'    => $items,
			'currency' => $currency,
			'account'  => array(
				'contact' => array(
					'first' => $order->get_billing_first_name(),
					'last'  => $order->get_billing_last_name(),
					'email' => $order->get_billing_email(),
				),
			),
			'country'  => $order->get_billing_country() ? $order->get_billing_country() : null,
			'language' => substr( get_locale(), 0, 2 ) ? substr( get_locale(), 0, 2 ) : 'en',
		);
		// Remove null entries.
		$payload = array_filter( $payload, static function ( $v ) { return null !== $v; } );

		$cart_discount = (float) $order->get_total_discount();
		if ( 'catalog' !== $strategy && $cart_discount > 0 ) {
			$payload['tags']['wc_discount'] = (string) $cart_discount;
		}

		/**
		 * Filter the FastSpring session payload before submission.
		 *
		 * @param array     $payload  Payload sent to /sessions.
		 * @param WC_Order  $order    WooCommerce order.
		 * @param self      $gateway  Gateway instance.
		 * @param string    $strategy Active pricing strategy.
		 */
		$payload = apply_filters( 'wp_fastspring_session_payload', $payload, $order, $this, $strategy );

		// 4) Talk to FastSpring.
		$session = $api->create_session( $payload );

		if ( is_wp_error( $session ) ) {
			$err = $session->get_error_message();
			$readable = $this->humanize_fastspring_error( $err, $strategy, $currency, $items );
			WP_FastSpring_Logger::error(
				'Session creation failed: ' . $err,
				'gateway',
				array(
					'order_id' => $order_id,
					'strategy' => $strategy,
					'currency' => $currency,
					'items'    => $items,
				)
			);
			$this->fail_payment( $readable );
		}

		$session_id = isset( $session['id'] ) ? $session['id'] : '';
		$storefront = $settings->storefront();
		if ( ! $session_id ) {
			$this->fail_payment( __( 'FastSpring returned an empty session id. Please try again.', 'wp-fastspring' ) );
		}

		// 5) Persist + redirect.
		$order->update_status( 'pending', __( 'Awaiting FastSpring payment.', 'wp-fastspring' ) );
		$order->update_meta_data( '_fastspring_session_id', $session_id );
		$order->save();

		$redirect = sprintf( 'https://%s/session/%s', $storefront, rawurlencode( $session_id ) );

		return array(
			'result'   => 'success',
			'redirect' => $redirect,
		);
	}

	/**
	 * Throw a payment failure that propagates to both classic and Block checkouts.
	 *
	 * @param string $message Customer-facing message.
	 * @throws Exception Always.
	 * @return void
	 */
	private function fail_payment( $message ) {
		// Classic checkout reads notices.
		if ( function_exists( 'wc_add_notice' ) ) {
			wc_add_notice( $message, 'error' );
		}
		// Block/Store API surfaces exception messages to the shopper.
		throw new Exception( esc_html( $message ) );
	}

	/**
	 * Turn raw FastSpring API errors into something the merchant/customer can
	 * actually act on.
	 *
	 * @param string $raw      Raw error message returned by the API.
	 * @param string $strategy Pricing strategy.
	 * @param string $currency Order currency.
	 * @param array  $items    Items sent.
	 * @return string
	 */
	private function humanize_fastspring_error( $raw, $strategy, $currency, $items ) {
		$lower = strtolower( (string) $raw );

		if ( false !== strpos( $lower, 'currency' ) ) {
			return sprintf(
				/* translators: %s currency code */
				__( 'FastSpring rejected the currency "%s". Add this currency to your storefront (FastSpring App → Storefronts → Currencies) or switch WooCommerce to a supported one (USD/EUR/GBP are always supported).', 'wp-fastspring' ),
				$currency
			);
		}
		if ( false !== strpos( $lower, 'product' ) && ( false !== strpos( $lower, 'not found' ) || false !== strpos( $lower, 'invalid' ) ) ) {
			$paths = array_map(
				static function ( $i ) {
					return isset( $i['product'] ) ? $i['product'] : '';
				},
				$items
			);
			return sprintf(
				/* translators: %s product paths */
				__( 'FastSpring could not find the product(s): %s. Create them in FastSpring App → Products with the matching path, or enable product sync in FastSpring → Settings.', 'wp-fastspring' ),
				implode( ', ', array_filter( $paths ) )
			);
		}
		if ( false !== strpos( $lower, 'price' ) && false !== strpos( $lower, 'override' ) ) {
			return __( 'FastSpring rejected the price override. Open the affected product in FastSpring App → Products and enable "Allow Price Override" (or change the pricing strategy in FastSpring → Settings to "FastSpring catalog price").', 'wp-fastspring' );
		}
		if ( 'single_custom_price' === $strategy && false !== strpos( $lower, 'price' ) ) {
			return __( 'FastSpring rejected the price on your Custom Price product. Confirm the product is configured as Type = Custom Price and has the order\'s currency enabled.', 'wp-fastspring' );
		}
		if ( false !== strpos( $lower, 'unauthorized' ) || false !== strpos( $lower, '401' ) ) {
			return __( 'FastSpring API authentication failed. Double-check the API username/password for the active mode in FastSpring → Settings.', 'wp-fastspring' );
		}

		return sprintf(
			/* translators: %s raw error message */
			__( 'FastSpring rejected the checkout session: %s', 'wp-fastspring' ),
			$raw
		);
	}

	/**
	 * Handle the post-purchase return from FastSpring (the webhook is the source of truth).
	 *
	 * URL: /?wc-api=wp_fastspring_return&order=123
	 */
	public function handle_return() {
		$order_id = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		$order    = $order_id ? wc_get_order( $order_id ) : null;

		if ( ! $order ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
		wp_safe_redirect( $order->get_checkout_order_received_url() );
		exit;
	}

	/**
	 * Process refund via FastSpring API.
	 *
	 * @param int    $order_id Order id.
	 * @param float  $amount   Amount.
	 * @param string $reason   Reason.
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new WP_Error( 'invalid_order', __( 'Invalid order.', 'wp-fastspring' ) );
		}

		$fs_order_id = $order->get_transaction_id();
		if ( ! $fs_order_id ) {
			return new WP_Error( 'no_fastspring_order', __( 'No FastSpring order linked to this WooCommerce order.', 'wp-fastspring' ) );
		}

		$api    = wp_fastspring()->api;
		$result = $api->create_return(
			array(
				'order'  => $fs_order_id,
				'reason' => $reason ? $reason : 'requested_by_customer',
			)
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$order->add_order_note( sprintf( __( 'Refund requested via FastSpring. Reason: %s', 'wp-fastspring' ), $reason ) );
		return true;
	}
}
