<?php
/**
 * WooCommerce payment gateway powered by FastSpring sessions.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_WC_Gateway.
 */
class VMS_EFWP_WC_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor.
	 *
	 * Carefully avoids depending on vms_efwp()->settings being initialised
	 * because some sites instantiate gateways extremely early (e.g. for cron
	 * or REST routes) before our plugin's `plugins_loaded` callback has run.
	 */
	public function __construct() {
		$this->id                 = 'vms_efwp';
		$this->method_title       = __( 'FastSpring', 'vms-elements-fastspring-woo-payment' );
		$this->method_description = __( 'Accept credit cards, PayPal, Apple Pay, Google Pay and more via FastSpring popup overlay checkout (Store Builder Library).', 'vms-elements-fastspring-woo-payment' );
		$this->has_fields         = false;
		$this->supports           = array( 'products', 'refunds' );

		$this->init_form_fields();
		$this->init_settings();

		$plugin_settings_raw = get_option( VMS_EFWP_Settings::OPTION_KEY, array() );
		if ( ! is_array( $plugin_settings_raw ) ) {
			$plugin_settings_raw = array();
		}
		$default_title = isset( $plugin_settings_raw['gateway_title'] ) && '' !== $plugin_settings_raw['gateway_title']
			? $plugin_settings_raw['gateway_title']
			: __( 'Pay with FastSpring', 'vms-elements-fastspring-woo-payment' );
		$default_desc  = isset( $plugin_settings_raw['gateway_description'] ) && '' !== $plugin_settings_raw['gateway_description']
			? $plugin_settings_raw['gateway_description']
			: __( 'Pay securely in a popup overlay powered by FastSpring.', 'vms-elements-fastspring-woo-payment' );

		$this->title       = $this->get_option( 'title', $default_title );
		$this->description = $this->get_option( 'description', $default_desc );
		$this->enabled     = $this->get_option( 'enabled', 'no' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_vms_efwp_return', array( $this, 'handle_return' ) );

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
		$settings = function_exists( 'vms_efwp' ) ? vms_efwp()->settings : null;
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
				'title'   => __( 'Enable/Disable', 'vms-elements-fastspring-woo-payment' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable FastSpring', 'vms-elements-fastspring-woo-payment' ),
				'default' => 'no',
			),
			'title'           => array(
				'title'       => __( 'Title', 'vms-elements-fastspring-woo-payment' ),
				'type'        => 'text',
				'description' => __( 'Title shown to customers at checkout.', 'vms-elements-fastspring-woo-payment' ),
				'default'     => __( 'Pay with FastSpring', 'vms-elements-fastspring-woo-payment' ),
				'desc_tip'    => true,
			),
			'description'     => array(
				'title'       => __( 'Description', 'vms-elements-fastspring-woo-payment' ),
				'type'        => 'textarea',
				'description' => __( 'Description shown to customers at checkout.', 'vms-elements-fastspring-woo-payment' ),
				'default'     => __( 'Pay securely in a popup overlay powered by FastSpring.', 'vms-elements-fastspring-woo-payment' ),
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

		$plugin_settings = function_exists( 'vms_efwp' ) ? vms_efwp()->settings : null;
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
		if ( ! $plugin_settings->has_popup_checkout() ) {
			$issues[] = 'missing_popup_path';
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

		$settings = vms_efwp()->settings;
		if ( ! $settings ) {
			return false;
		}

		$reason = '';
		if ( ! $settings->has_credentials() ) {
			$reason = 'missing_credentials';
		} elseif ( '' === $settings->storefront() ) {
			$reason = 'missing_storefront';
		} elseif ( ! $settings->has_popup_checkout() ) {
			$reason = 'missing_popup_path';
		}

		if ( $reason ) {
			if ( class_exists( 'VMS_EFWP_Logger' ) ) {
				VMS_EFWP_Logger::warning(
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
		$settings = vms_efwp()->settings;
		if ( ! $settings ) {
			return $desc;
		}
		$messages = array();
		if ( ! $settings->has_credentials() ) {
			$messages[] = __( 'Admin-only notice: FastSpring API credentials are missing for the active mode.', 'vms-elements-fastspring-woo-payment' );
		}
		if ( '' === $settings->storefront() ) {
			$messages[] = __( 'Admin-only notice: FastSpring storefront ID is empty. Customers will not see this gateway until you set it.', 'vms-elements-fastspring-woo-payment' );
		}
		if ( ! $settings->has_popup_checkout() ) {
			$messages[] = __( 'Admin-only notice: FastSpring popup checkout path is missing. Set it in FastSpring → Settings (e.g. popup-vmsuniverse2026).', 'vms-elements-fastspring-woo-payment' );
		}
		if ( $messages ) {
			$desc .= '<br /><em style="color:#b91c1c;">' . esc_html( implode( ' ', $messages ) ) . '</em>';
		}
		return $desc;
	}

	/**
	 * Whether Store Builder should use secure() for price overrides.
	 *
	 * @return bool
	 */
	public function uses_sbl_secure() {
		$settings = vms_efwp()->settings;
		if ( ! $settings || ! $settings->has_access_key() ) {
			return false;
		}
		return 'catalog' !== $settings->pricing_strategy();
	}

	/**
	 * Build the Store Builder Library payload (push/secure) for popup checkout.
	 *
	 * Matches FastSpring's official demo: reset → secure/push → checkout().
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return array
	 */
	public function build_sbl_checkout_payload( WC_Order $order ) {
		$settings = vms_efwp()->settings;
		$strategy = $settings->pricing_strategy();
		$currency = strtoupper( $order->get_currency() );
		$items    = $this->build_session_items( $order, $strategy, $currency );
		$contact  = $this->build_contact( $order );

		$sbl_items = array();
		foreach ( $items as $item ) {
			$row = array(
				'product'  => $item['product'],
				'quantity' => isset( $item['quantity'] ) ? (int) $item['quantity'] : 1,
			);
			if ( isset( $item['pricing']['price'] ) && is_array( $item['pricing']['price'] ) ) {
				$row['pricing'] = array(
					'price' => $item['pricing']['price'],
				);
			}
			$sbl_items[] = $row;
		}

		$country = $order->get_billing_country();
		if ( ! $country ) {
			$country = 'US';
		}

		$language = strtoupper( substr( get_locale(), 0, 2 ) );
		if ( ! $language ) {
			$language = 'EN';
		}

		$sbl_contact = array(
			'email'     => isset( $contact['email'] ) ? $contact['email'] : $order->get_billing_email(),
			'firstName' => isset( $contact['firstName'] ) ? $contact['firstName'] : $order->get_billing_first_name(),
			'lastName'  => isset( $contact['lastName'] ) ? $contact['lastName'] : $order->get_billing_last_name(),
		);

		if ( ! empty( $contact['phoneNumber'] ) ) {
			$sbl_contact['phoneNumber'] = $contact['phoneNumber'];
		}

		return array(
			'country'  => $country,
			'language' => $language,
			'items'    => $sbl_items,
			'contact'  => $sbl_contact,
			'tags'     => VMS_EFWP_Data_Store::build_session_tags( $order ),
		);
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
		$settings = vms_efwp()->settings;
		$items    = array();

		if ( 'single_custom_price' === $strategy ) {
			$path = $settings->custom_price_product_path();
			if ( '' === $path ) {
				return array();
			}
			$total   = max( 0.01, round( (float) $order->get_total(), 2 ) );
			$items[] = array(
				'product'  => $path,
				'quantity' => 1,
				'pricing'  => array(
					'quantityBehavior' => 'lock',
					'quantityDefault'  => 1,
					'price'            => array( $currency => $total ),
				),
			);
			return $items;
		}

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( ! $product ) {
				continue;
			}
			$slug = get_post_meta( $product->get_id(), '_vms_efwp_product_path', true );
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
				$unit_price = $qty > 0 ? round( $line_total / $qty, 2 ) : $line_total;
				$unit_price = max( 0, $unit_price );
				$row['pricing'] = array(
					'quantityBehavior' => 'lock',
					'quantityDefault'  => $qty,
					'price'            => array( $currency => $unit_price ),
				);
			}

			$items[] = $row;
		}

		return $items;
	}

	/**
	 * Build the FastSpring contact object from a WooCommerce order.
	 *
	 * The Sessions v1 API expects a top-level `contact` object (not nested under
	 * `account`, and not using `first`/`last` keys). `account` is reserved for an
	 * existing FastSpring account *id* string. Passing an object there causes the
	 * API to reject the request (HTTP 409).
	 *
	 * @param WC_Order $order WooCommerce order.
	 * @return array
	 */
	private function build_contact( $order ) {
		$country = $order->get_billing_country();

		$contact = array(
			'firstName'   => $order->get_billing_first_name(),
			'lastName'    => $order->get_billing_last_name(),
			'email'       => $order->get_billing_email(),
			'company'     => $order->get_billing_company(),
			'phoneNumber' => $order->get_billing_phone(),
			'country'     => $country,
		);

		// FastSpring expects 2-letter region codes for US/CA and full names
		// elsewhere; WooCommerce only reliably gives codes, so only send the
		// region where the format is guaranteed to be accepted.
		if ( in_array( $country, array( 'US', 'CA' ), true ) ) {
			$contact['region'] = $order->get_billing_state();
		}

		// Drop empty values so we never send blank strings FastSpring may reject.
		return array_filter(
			$contact,
			static function ( $value ) {
				return is_string( $value ) && '' !== $value;
			}
		);
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
			$this->fail_payment( __( 'Order could not be loaded.', 'vms-elements-fastspring-woo-payment' ) );
		}

		$settings = vms_efwp()->settings;
		$api      = vms_efwp()->api;

		// 1) Sanity check: credentials + storefront.
		if ( ! $settings->has_credentials() ) {
			$this->fail_payment( __( 'FastSpring API credentials are not configured for the active mode.', 'vms-elements-fastspring-woo-payment' ) );
		}
		if ( '' === $settings->storefront() ) {
			$this->fail_payment( __( 'FastSpring storefront ID is empty. Please configure it before accepting payments.', 'vms-elements-fastspring-woo-payment' ) );
		}
		if ( ! $settings->has_popup_checkout() ) {
			$this->fail_payment( __( 'FastSpring popup checkout path is not configured. In FastSpring → Settings, set the Popup checkout path (e.g. popup-vmsuniverse2026) from Checkouts → Popup Checkouts → Place on your website.', 'vms-elements-fastspring-woo-payment' ) );
		}

		$strategy = $settings->pricing_strategy();
		if ( 'catalog' !== $strategy && ! $settings->has_access_key() ) {
			$this->fail_payment( __( 'FastSpring Store Builder access key is required for custom WooCommerce pricing. Add it in FastSpring → Settings (copy from FastSpring App → Developer Tools → Store Builder Library).', 'vms-elements-fastspring-woo-payment' ) );
		}

		$currency = strtoupper( $order->get_currency() );

		// 2) For the single custom-price strategy, make sure the catch-all
		// product exists in FastSpring. This lets ANY WooCommerce product be
		// purchased without mirroring the whole catalog into FastSpring.
		if ( 'single_custom_price' === $strategy ) {
			$path        = $settings->custom_price_product_path();
			$ensure_path = $api->ensure_catch_all_product( $path, get_bloginfo( 'name' ) . ' ' . __( 'Order', 'vms-elements-fastspring-woo-payment' ) );
			if ( is_wp_error( $ensure_path ) ) {
				VMS_EFWP_Logger::error(
					'Catch-all product provisioning failed: ' . $ensure_path->get_error_message(),
					'gateway',
					array( 'path' => $path )
				);
				$this->fail_payment(
					sprintf(
						/* translators: 1: product path, 2: API error */
						__( 'Could not prepare the FastSpring checkout product "%1$s" automatically. Create a one-time product with exactly this path in FastSpring App → Products (any price — it is overridden per order), then try again. Details: %2$s', 'vms-elements-fastspring-woo-payment' ),
						$path,
						$ensure_path->get_error_message()
					)
				);
			}
			// Persist the resolved path so we skip provisioning next time.
			if ( $ensure_path !== $settings->get( 'custom_price_product_path', '' ) ) {
				$settings->set( 'custom_price_product_path', $ensure_path );
			}
		}

		if ( 'per_product_override' === $strategy ) {
			$product_sync = vms_efwp()->product_sync;
			if ( $product_sync ) {
				$ensured = $product_sync->ensure_order_products_in_fastspring( $order );
				if ( is_wp_error( $ensured ) ) {
					VMS_EFWP_Logger::error(
						'Checkout product auto-provision failed: ' . $ensured->get_error_message(),
						'gateway',
						array( 'order_id' => $order_id )
					);
					$this->fail_payment( $ensured->get_error_message() );
				}
			}
		}

		$items = $this->build_session_items( $order, $strategy, $currency );
		if ( empty( $items ) ) {
			$this->fail_payment( __( 'No cart items could be matched to FastSpring products.', 'vms-elements-fastspring-woo-payment' ) );
		}

		// 3) Build payload (Sessions v1 schema).
		$payload = array(
			'tags'    => VMS_EFWP_Data_Store::build_session_tags( $order_id ),
			'items'   => $items,
			'contact' => $this->build_contact( $order ),
		);

		$language = substr( get_locale(), 0, 2 );
		if ( $language ) {
			$payload['language'] = $language;
		}

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
		$payload = apply_filters( 'vms_efwp_session_payload', $payload, $order, $this, $strategy );

		// 4) Talk to FastSpring.
		$session = $api->create_session( $payload );

		if ( is_wp_error( $session ) ) {
			$err       = $session->get_error_message();
			$err_data  = $session->get_error_data();
			$status    = is_array( $err_data ) && isset( $err_data['status'] ) ? (int) $err_data['status'] : 0;
			$readable  = $this->humanize_fastspring_error( $err, $strategy, $currency, $items, $status );
			VMS_EFWP_Logger::error(
				'Session creation failed: ' . $err,
				'gateway',
				array(
					'order_id' => $order_id,
					'strategy' => $strategy,
					'currency' => $currency,
					'status'   => $status,
					'items'    => $items,
					'payload'  => $payload,
				)
			);
			$this->fail_payment( $readable );
		}

		$session_id = isset( $session['id'] ) ? $session['id'] : '';
		$storefront = $settings->storefront();
		if ( ! $session_id ) {
			$this->fail_payment( __( 'FastSpring returned an empty session id. Please try again.', 'vms-elements-fastspring-woo-payment' ) );
		}

		// 5) Persist + redirect.
		$order->update_status( 'pending', __( 'Awaiting FastSpring payment.', 'vms-elements-fastspring-woo-payment' ) );
		$order->update_meta_data( '_vms_efwp_session_id', $session_id );
		$order->save();

		// Popup-only: JS opens FastSpring overlay on the same page (no redirect).
		return array(
			'result'   => 'success',
			'redirect' => false,
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
		// Block checkout surfaces exception messages to the shopper.
		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Plain-text customer message; tags stripped above.
		throw new Exception( wp_strip_all_tags( $message ) );
	}

	/**
	 * Turn raw FastSpring API errors into something the merchant/customer can
	 * actually act on.
	 *
	 * @param string $raw      Raw error message returned by the API.
	 * @param string $strategy Pricing strategy.
	 * @param string $currency Order currency.
	 * @param array  $items    Items sent.
	 * @param int    $status   HTTP status code returned by FastSpring.
	 * @return string
	 */
	private function humanize_fastspring_error( $raw, $strategy, $currency, $items, $status = 0 ) {
		$lower = strtolower( (string) $raw );
		$paths = array_filter(
			array_map(
				static function ( $i ) {
					return isset( $i['product'] ) ? $i['product'] : '';
				},
				$items
			)
		);

		if ( false !== strpos( $lower, 'currency' ) ) {
			return sprintf(
				/* translators: %s currency code */
				__( 'FastSpring rejected the currency "%s". Add this currency to your storefront (FastSpring App → Storefronts → Currencies) or switch WooCommerce to a supported one (USD/EUR/GBP are always supported).', 'vms-elements-fastspring-woo-payment' ),
				$currency
			);
		}
		if ( false !== strpos( $lower, 'product' ) && ( false !== strpos( $lower, 'not found' ) || false !== strpos( $lower, 'invalid' ) ) ) {
			return sprintf(
				/* translators: %s product paths */
				__( 'FastSpring could not find the product(s): %s. Create them in FastSpring App → Products with the matching path, or enable product sync in FastSpring → Settings.', 'vms-elements-fastspring-woo-payment' ),
				implode( ', ', $paths )
			);
		}
		if ( false !== strpos( $lower, 'price' ) && false !== strpos( $lower, 'override' ) ) {
			return __( 'FastSpring rejected the price override. Open the affected product in FastSpring App → Products and enable "Allow Price Override" (or change the pricing strategy in FastSpring → Settings to "FastSpring catalog price").', 'vms-elements-fastspring-woo-payment' );
		}
		if ( 'single_custom_price' === $strategy && false !== strpos( $lower, 'price' ) ) {
			return __( 'FastSpring rejected the price on your Custom Price product. Confirm the product is configured as Type = Custom Price and has the order\'s currency enabled.', 'vms-elements-fastspring-woo-payment' );
		}
		if ( false !== strpos( $lower, 'unauthorized' ) || 401 === $status || false !== strpos( $lower, '401' ) ) {
			return __( 'FastSpring API authentication failed. Double-check the API username/password for the active mode in FastSpring → Settings.', 'vms-elements-fastspring-woo-payment' );
		}

		if ( 409 === $status ) {
			if ( 'single_custom_price' === $strategy ) {
				return sprintf(
					/* translators: %s product path */
					__( 'FastSpring rejected the checkout session (409 conflict). Confirm your Custom Price product "%s" exists, is set to Type = Custom Price, has "Allow Price Override" enabled, and supports the order currency.', 'vms-elements-fastspring-woo-payment' ),
					implode( ', ', $paths )
				);
			}
			if ( 'per_product_override' === $strategy ) {
				return sprintf(
					/* translators: %s product paths */
					__( 'FastSpring rejected the checkout session (409 conflict). Each product (%s) must allow price overrides in FastSpring. The plugin auto-creates missing products at checkout — open the product in FastSpring App → Products and enable "Allow Price Override" if this error persists.', 'vms-elements-fastspring-woo-payment' ),
					implode( ', ', $paths )
				);
			}
			return sprintf(
				/* translators: %s product paths */
				__( 'FastSpring rejected the checkout session (409 conflict). Make sure the product(s) %s exist in your FastSpring catalog and the order currency is enabled for your storefront.', 'vms-elements-fastspring-woo-payment' ),
				implode( ', ', $paths )
			);
		}

		return sprintf(
			/* translators: %s raw error message */
			__( 'FastSpring rejected the checkout session: %s', 'vms-elements-fastspring-woo-payment' ),
			$raw
		);
	}

	/**
	 * Handle the post-purchase return from FastSpring (the webhook is the source of truth).
	 *
	 * URL: /?wc-api=vms_efwp_return&order=123
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
		if ( ! vms_efwp_is_pro() ) {
			return new WP_Error(
				'pro_required',
				__( 'WooCommerce refunds via FastSpring require the Pro add-on.', 'vms-elements-fastspring-woo-payment' )
			);
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new WP_Error( 'invalid_order', __( 'Invalid order.', 'vms-elements-fastspring-woo-payment' ) );
		}

		$fs_order_id = $order->get_transaction_id();
		if ( ! $fs_order_id ) {
			return new WP_Error( 'no_fastspring_order', __( 'No FastSpring order linked to this WooCommerce order.', 'vms-elements-fastspring-woo-payment' ) );
		}

		$api    = vms_efwp()->api;
		$return = array(
			'order'  => $fs_order_id,
			'reason' => $reason ? $reason : 'requested_by_customer',
		);
		if ( null !== $amount && $amount > 0 ) {
			$order_total = (float) $order->get_total();
			if ( $amount < $order_total ) {
				$return['amount'] = (float) $amount;
			}
		}
		$result = $api->create_return( $return );

		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$order->add_order_note(
			sprintf(
				/* translators: %s: refund reason */
				__( 'Refund requested via FastSpring. Reason: %s', 'vms-elements-fastspring-woo-payment' ),
				$reason
			)
		);
		return true;
	}
}
