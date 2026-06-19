<?php
/**
 * Loads the WooCommerce payment gateway, only when WooCommerce is active.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_WC_Gateway_Loader.
 */
class VMS_EFWP_WC_Gateway_Loader {

	/**
	 * Store Builder Library script URL (popup overlay checkout).
	 *
	 * @see https://developer.fastspring.com/reference/store-builder-library-overview
	 */
	const SBL_SCRIPT_URL = 'https://sbl.onfastspring.com/sbl/1.0.7/fastspring-builder.min.js';

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

		// Store Builder overlay on checkout (Paddle-style popup on the same page).
		add_action( 'wp_enqueue_scripts', array( $this, 'register_checkout_overlay_assets' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_checkout_overlay_assets' ), 10 );
		add_filter( 'script_loader_tag', array( $this, 'add_sbl_script_attributes' ), 10, 3 );
		add_filter( 'woocommerce_payment_successful_result', array( $this, 'inject_overlay_payment_data' ), 10, 2 );
		add_filter( 'woocommerce_store_api_payment_result', array( $this, 'inject_store_api_payment_result' ), 10, 2 );
		add_action( 'woocommerce_rest_checkout_process_payment_with_context', array( $this, 'clear_store_api_redirect' ), 20, 2 );

		// Popup-checkout bridge page for direct payment links only.
		add_action( 'template_redirect', array( $this, 'maybe_render_checkout_bridge' ) );
	}

	/**
	 * Whether overlay assets should be registered (handles must exist for Blocks deps).
	 *
	 * @return bool
	 */
	private function should_register_overlay_assets() {
		return ! is_admin();
	}

	/**
	 * Whether overlay assets should be loaded on the current request.
	 *
	 * @return bool
	 */
	private function should_load_checkout_assets() {
		if ( ! function_exists( 'is_checkout' ) ) {
			return false;
		}

		return is_checkout() || ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-pay' ) );
	}

	/**
	 * Register Store Builder overlay assets (early so handles exist on cart/checkout Blocks).
	 */
	public function register_checkout_overlay_assets() {
		if ( ! $this->should_register_overlay_assets() ) {
			return;
		}

		wp_register_style(
			'vms-efwp-checkout-popup',
			VMS_EFWP_URL . 'assets/css/checkout-popup.css',
			array(),
			VMS_EFWP_VERSION
		);

		wp_register_script(
			'fastspring-builder',
			self::SBL_SCRIPT_URL,
			array(),
			'1.0.7',
			true
		);

		wp_register_script(
			'vms-efwp-checkout-popup',
			VMS_EFWP_URL . 'assets/js/checkout-popup.js',
			array( 'jquery', 'fastspring-builder', 'wp-api-fetch' ),
			VMS_EFWP_VERSION,
			true
		);
	}

	/**
	 * Load the Store Builder Library and overlay handler on checkout pages.
	 */
	public function enqueue_checkout_overlay_assets() {
		if ( ! $this->should_load_checkout_assets() ) {
			return;
		}
		if ( ! function_exists( 'vms_efwp' ) || ! vms_efwp()->settings ) {
			return;
		}

		$settings = vms_efwp()->settings;
		if ( ! $settings->has_popup_checkout() ) {
			return;
		}

		wp_enqueue_style( 'vms-efwp-checkout-popup' );
		wp_enqueue_script( 'fastspring-builder' );
		wp_enqueue_script( 'vms-efwp-checkout-popup' );

		wp_localize_script(
			'vms-efwp-checkout-popup',
			'vmsEfwpCheckout',
			array(
				'popupStorefront' => $settings->popup_storefront(),
				'hostedDomain'    => $settings->storefront(),
				'accessKey'       => $settings->access_key(),
				'checkoutUrl'     => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' ),
				'restUrl'         => rest_url( 'vms-efwp/v1/overlay/' ),
				'completeRestUrl' => rest_url( 'vms-efwp/v1/complete/' ),
				'restNonce'       => wp_create_nonce( 'wp_rest' ),
				'debug'           => defined( 'WP_DEBUG' ) && WP_DEBUG,
				'i18n'            => array(
					'error'           => __( 'FastSpring checkout could not open. Confirm your popup checkout path is configured and your site domain is whitelisted in FastSpring.', 'vms-elements-fastspring-woo-payment' ),
					'missingPopup'    => __( 'FastSpring popup checkout path is not configured in FastSpring → Settings.', 'vms-elements-fastspring-woo-payment' ),
					'missingAccessKey' => __( 'FastSpring Store Builder access key is required for custom WooCommerce pricing. Add it in FastSpring → Settings (Developer Tools → Store Builder Library in the FastSpring app).', 'vms-elements-fastspring-woo-payment' ),
					'loadFailed'      => __( 'FastSpring Store Builder did not load. Check your popup checkout path and whitelisted domains.', 'vms-elements-fastspring-woo-payment' ),
				),
			)
		);
	}

	/**
	 * Add Store Builder data attributes required for popup overlay checkout.
	 *
	 * @param string $tag    Script tag.
	 * @param string $handle Handle.
	 * @param string $src    Source URL.
	 * @return string
	 */
	public function add_sbl_script_attributes( $tag, $handle, $src ) {
		if ( 'fastspring-builder' !== $handle ) {
			return $tag;
		}
		if ( ! function_exists( 'vms_efwp' ) || ! vms_efwp()->settings ) {
			return $tag;
		}

		$popup_storefront = vms_efwp()->settings->popup_storefront();
		if ( '' === $popup_storefront ) {
			return $tag;
		}

		$access_key = vms_efwp()->settings->access_key();
		$access_attr = $access_key ? sprintf( ' data-access-key="%s"', esc_attr( $access_key ) ) : '';

		return sprintf(
			'<script id="fsc-api" src="%1$s" type="text/javascript" data-storefront="%2$s" data-popup-closed="vmsEfwpPopupClosed" data-error-callback="vmsEfwpErrorCallback" data-continuous="true"%3$s></script>' . "\n",
			esc_url( $src ),
			esc_attr( $popup_storefront ),
			$access_attr
		);
	}

	/**
	 * Build overlay payload for an order (shared by AJAX, REST, and bridge page).
	 *
	 * @param WC_Order $order Order.
	 * @return array
	 */
	private function build_overlay_for_order( WC_Order $order ) {
		$session_id   = (string) $order->get_meta( '_vms_efwp_session_id' );
		$push_payload = null;
		$use_secure   = false;

		if ( function_exists( 'WC' ) && WC()->payment_gateways() ) {
			$gateways = WC()->payment_gateways()->payment_gateways();
			if ( isset( $gateways['vms_efwp'] ) && $gateways['vms_efwp'] instanceof VMS_EFWP_WC_Gateway ) {
				/** @var VMS_EFWP_WC_Gateway $gateway */
				$gateway      = $gateways['vms_efwp'];
				$push_payload = $gateway->build_sbl_checkout_payload( $order );
				$use_secure   = $gateway->uses_sbl_secure();
			}
		}

		$overlay = array(
			'sessionId'   => $session_id,
			'successUrl'  => $order->get_checkout_order_received_url(),
			'cancelUrl'   => $order->get_checkout_payment_url(),
			'orderId'     => $order->get_id(),
			'orderKey'    => $order->get_order_key(),
			'pushPayload' => $push_payload,
			'useSecure'   => $use_secure,
			'tags'        => array( 'wc_order_id' => (string) $order->get_id() ),
		);

		if ( class_exists( 'VMS_EFWP_Checkout_Overlay' ) ) {
			$overlay = VMS_EFWP_Checkout_Overlay::stash( $order->get_id(), $overlay );
		}

		return $overlay;
	}

	/**
	 * Attach overlay payload to classic checkout AJAX responses.
	 *
	 * @param array    $result   Payment result.
	 * @param WC_Order $order_id Order ID (WC passes int; filter documents order object in some versions).
	 * @return array
	 */
	public function inject_overlay_payment_data( $result, $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || 'vms_efwp' !== $order->get_payment_method() ) {
			return $result;
		}

		if ( ! function_exists( 'vms_efwp' ) || ! vms_efwp()->settings ) {
			return $result;
		}

		$settings   = vms_efwp()->settings;
		$session_id = (string) $order->get_meta( '_vms_efwp_session_id' );

		if ( '' === $session_id || ! $settings->has_popup_checkout() ) {
			return $result;
		}

		$overlay = $this->build_overlay_for_order( $order );

		// Popup-only: stay on checkout — JS opens the FastSpring overlay (no order-received redirect).
		$result['redirect']           = false;
		$result['vms_efwp_overlay']   = $overlay;
		$result['payment_details']    = isset( $result['payment_details'] ) && is_array( $result['payment_details'] )
			? $result['payment_details']
			: array();
		$result['payment_details']['vms_efwp_overlay'] = $overlay;

		return $result;
	}

	/**
	 * Expose overlay payload to Gutenberg block checkout Store API responses.
	 *
	 * @param array    $payment_result Payment result.
	 * @param WC_Order $order          Order.
	 * @return array
	 */
	public function inject_store_api_payment_result( $payment_result, $order ) {
		if ( ! $order instanceof WC_Order || 'vms_efwp' !== $order->get_payment_method() ) {
			return $payment_result;
		}

		if ( ! function_exists( 'vms_efwp' ) || ! vms_efwp()->settings || ! vms_efwp()->settings->has_popup_checkout() ) {
			return $payment_result;
		}

		$session_id = (string) $order->get_meta( '_vms_efwp_session_id' );
		if ( '' === $session_id ) {
			return $payment_result;
		}

		$overlay = $this->build_overlay_for_order( $order );

		$payment_result['redirect_url'] = '';
		if ( ! isset( $payment_result['payment_details'] ) || ! is_array( $payment_result['payment_details'] ) ) {
			$payment_result['payment_details'] = array();
		}
		$payment_result['payment_details']['vms_efwp_overlay'] = $overlay;

		return $payment_result;
	}

	/**
	 * Blocks checkout sets redirectUrl to order-received even when the gateway
	 * returns an empty redirect. Clear it so the popup can open on the same page.
	 *
	 * @param mixed $context Payment context.
	 * @param mixed $result  Payment result object.
	 */
	public function clear_store_api_redirect( $context, $result ) {
		if ( ! is_object( $context ) || ! is_object( $result ) ) {
			return;
		}

		$method = isset( $context->payment_method ) ? $context->payment_method : '';
		if ( 'vms_efwp' !== $method ) {
			return;
		}

		if ( method_exists( $result, 'set_redirect_url' ) ) {
			$result->set_redirect_url( '' );
		}
	}

	/**
	 * Render the FastSpring popup bridge when the buyer is sent to
	 * /?vms_efwp_pay=<order_id>&key=<order_key> after placing an order.
	 */
	public function maybe_render_checkout_bridge() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Order key acts as the nonce.
		if ( empty( $_GET['vms_efwp_pay'] ) ) {
			return;
		}
		if ( ! function_exists( 'wc_get_order' ) || ! function_exists( 'vms_efwp' ) ) {
			return;
		}

		$order_id = absint( $_GET['vms_efwp_pay'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$key      = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order    = $order_id ? wc_get_order( $order_id ) : null;

		if ( ! $order || ! hash_equals( $order->get_order_key(), $key ) ) {
			wp_safe_redirect( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) );
			exit;
		}

		// If the order is already paid (e.g. webhook beat the redirect), go
		// straight to the thank-you page.
		if ( $order->is_paid() ) {
			wp_safe_redirect( $order->get_checkout_order_received_url() );
			exit;
		}

		$settings    = vms_efwp()->settings;
		$session_id  = (string) $order->get_meta( '_vms_efwp_session_id' );
		$popup_store = $settings ? $settings->popup_storefront() : '';
		$domain      = $settings ? $settings->storefront() : '';

		if ( '' === $session_id || '' === $domain ) {
			// Nothing we can open; send them back to pay again.
			wp_safe_redirect( $order->get_checkout_payment_url() );
			exit;
		}

		$success_url = $order->get_checkout_order_received_url();
		$cancel_url  = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' );
		$overlay     = $this->build_overlay_for_order( $order );

		$this->render_bridge_html(
			$popup_store ? $popup_store : $domain,
			$overlay,
			$success_url,
			$cancel_url
		);
		exit;
	}

	/**
	 * Output the minimal HTML page that loads the SBL and opens the popup.
	 *
	 * @param string $popup_storefront data-storefront value.
	 * @param array  $overlay          Overlay payload.
	 * @param string $success_url      Order-received URL.
	 * @param string $cancel_url       Checkout URL when buyer cancels.
	 */
	private function render_bridge_html( $popup_storefront, $overlay, $success_url, $cancel_url ) {
		nocache_headers();
		$settings    = function_exists( 'vms_efwp' ) ? vms_efwp()->settings : null;
		$access_key  = $settings ? $settings->access_key() : '';
		$loading     = esc_html__( 'Opening secure checkout…', 'vms-elements-fastspring-woo-payment' );
		$retry       = esc_html__( 'Return to checkout', 'vms-elements-fastspring-woo-payment' );
		$complete_url = rest_url( 'vms-efwp/v1/complete/' );
		$rest_nonce   = wp_create_nonce( 'wp_rest' );
		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="robots" content="noindex,nofollow" />
	<title><?php echo esc_html__( 'Redirecting to secure checkout', 'vms-elements-fastspring-woo-payment' ); ?></title>
	<script
		id="fsc-api"
		src="<?php echo esc_url( self::SBL_SCRIPT_URL ); ?>"
		type="text/javascript"
		data-storefront="<?php echo esc_attr( $popup_storefront ); ?>"
		data-popup-closed="vmsEfwpClosed"
		data-error-callback="vmsEfwpErrorCallback"
		data-continuous="true"
		<?php if ( $access_key ) : ?>
		data-access-key="<?php echo esc_attr( $access_key ); ?>"
		<?php endif; ?>
	></script>
	<style>
		body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:#f6f7f7;color:#1e1e1e;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0;text-align:center;padding:24px;}
		.vefwp-bridge{max-width:420px}
		.vefwp-spinner{width:38px;height:38px;border:4px solid #c3c4c7;border-top-color:#2271b1;border-radius:50%;animation:vefwp-spin 1s linear infinite;margin:0 auto 18px}
		@keyframes vefwp-spin{to{transform:rotate(360deg)}}
		a{color:#2271b1}
	</style>
</head>
<body>
	<div class="vefwp-bridge">
		<div class="vefwp-spinner" aria-hidden="true"></div>
		<p><?php echo $loading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></p>
		<p><a id="vefwp-fallback" href="<?php echo esc_url( $cancel_url ); ?>"><?php echo $retry; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></a></p>
	</div>
	<script>
	(function(){
		var OVERLAY = <?php echo wp_json_encode( $overlay ); ?>;
		var SUCCESS = <?php echo wp_json_encode( $success_url ); ?>;
		var CANCEL  = <?php echo wp_json_encode( $cancel_url ); ?>;
		var COMPLETE_URL = <?php echo wp_json_encode( $complete_url ); ?>;
		var REST_NONCE = <?php echo wp_json_encode( $rest_nonce ); ?>;
		var opened = false, done = false, tries = 0;

		function confirmPaymentAndRedirect( orderId, orderKey, fsOrderId ) {
			if ( ! orderId || ! fsOrderId || ! COMPLETE_URL ) {
				window.location.replace( SUCCESS );
				return;
			}

			var url = COMPLETE_URL + orderId;
			var headers = { 'Content-Type': 'application/json' };
			if ( REST_NONCE ) {
				headers['X-WP-Nonce'] = REST_NONCE;
			}

			var attempts = 0;
			function attemptComplete() {
				attempts += 1;
				return fetch( url, {
					method: 'POST',
					credentials: 'same-origin',
					headers: headers,
					body: JSON.stringify( { fs_order_id: fsOrderId, key: orderKey } )
				} )
					.then( function( response ) {
						return response.json().then( function( body ) {
							return { ok: response.ok, body: body };
						} );
					} )
					.then( function( result ) {
						var status = result.body && result.body.status ? result.body.status : '';
						if ( status === 'completed' || status === 'already_paid' || attempts >= 4 ) {
							window.location.replace( SUCCESS );
							return;
						}
						return new Promise( function( resolve ) {
							setTimeout( resolve, 1200 );
						} ).then( attemptComplete );
					} );
			}

			attemptComplete().catch( function() {
				window.location.replace( SUCCESS );
			} );
		}

		window.vmsEfwpErrorCallback = function( code, message ) {
			console.error( '[VMS FastSpring]', code, message );
		};

		window.vmsEfwpClosed = function( data ){
			if ( done ) { return; }
			done = true;
			if ( window.fastspring && window.fastspring.builder && typeof window.fastspring.builder.reset === 'function' ) {
				try { window.fastspring.builder.reset(); } catch ( e ) {}
			}
			if ( data && data.id ) {
				confirmPaymentAndRedirect(
					OVERLAY.orderId || 0,
					OVERLAY.orderKey || '',
					data.id
				);
			} else {
				window.location.replace( CANCEL );
			}
		};

		function ready(){
			return window.fastspring && window.fastspring.builder && typeof window.fastspring.builder.checkout === 'function';
		}

		function launch(){
			if ( opened || ! ready() || ! OVERLAY ) { return; }
			opened = true;
			try {
				window.fastspring.builder.reset();
				if ( OVERLAY.tags ) {
					window.fastspring.builder.tag( OVERLAY.tags );
				}
				if ( OVERLAY.pushPayload ) {
					if ( OVERLAY.useSecure ) {
						window.fastspring.builder.secure( OVERLAY.pushPayload, '' );
					} else {
						window.fastspring.builder.push( {
							reset: true,
							products: ( OVERLAY.pushPayload.items || [] ).map( function( item ) {
								return { path: item.product, quantity: item.quantity || 1 };
							} ),
							paymentContact: OVERLAY.pushPayload.contact || {},
							country: OVERLAY.pushPayload.country,
							language: ( OVERLAY.pushPayload.language || 'EN' ).toLowerCase()
						} );
					}
				}
				window.fastspring.builder.checkout();
			} catch ( e ) {
				window.location.replace( CANCEL );
			}
		}

		var timer = setInterval(function(){
			tries++;
			if ( ready() ) { clearInterval( timer ); launch(); }
			else if ( tries > 100 ) { clearInterval( timer ); window.location.replace( CANCEL ); }
		}, 100);
	})();
	</script>
</body>
</html>
		<?php
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
		require_once VMS_EFWP_PATH . 'includes/class-vms-efwp-wc-blocks.php';
		if ( ! class_exists( 'VMS_EFWP_WC_Blocks' ) ) {
			return;
		}
		$registry->register( new VMS_EFWP_WC_Blocks() );
	}
}
