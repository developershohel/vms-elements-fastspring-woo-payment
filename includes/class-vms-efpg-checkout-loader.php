<?php
/**
 * Loads the WooCommerce payment gateway, only when WooCommerce is active.
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loads FastSpring popup overlay checkout + WooCommerce Blocks integration.
 */
class VMS_EFPG_Checkout_Loader {

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
		add_action( 'woocommerce_blocks_payment_method_type_registration', array( $this, 'register_payment_method_type' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_checkout_overlay_assets' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_checkout_overlay_assets' ), 10 );
		add_filter( 'script_loader_tag', array( $this, 'add_sbl_script_attributes' ), 10, 3 );
		add_filter( 'woocommerce_payment_successful_result', array( $this, 'inject_overlay_payment_data' ), 10, 2 );
		add_filter( 'woocommerce_store_api_payment_result', array( $this, 'inject_store_api_payment_result' ), 10, 2 );
		add_action( 'woocommerce_rest_checkout_process_payment_with_context', array( $this, 'clear_store_api_redirect' ), 20, 2 );
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

		if ( is_checkout() || ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-pay' ) ) ) {
			return true;
		}

		return $this->should_load_thankyou_confirm_assets();
	}

	/**
	 * Load payment-complete JS on order-received when confirmation may still be pending.
	 *
	 * @return bool
	 */
	private function should_load_thankyou_confirm_assets() {
		if ( ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'order-received' ) ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only flag for asset loading.
		if ( ! empty( $_GET['vms_efpg_fs_order'] ) ) {
			return true;
		}

		global $wp;
		$order_id = isset( $wp->query_vars['order-received'] ) ? absint( $wp->query_vars['order-received'] ) : 0;
		if ( ! $order_id || ! function_exists( 'wc_get_order' ) ) {
			return false;
		}

		$order = wc_get_order( $order_id );
		return $order instanceof WC_Order
			&& 'vms_efpg' === $order->get_payment_method()
			&& $order->needs_payment();
	}

	/**
	 * Register Store Builder overlay assets (early so handles exist on cart/checkout Blocks).
	 */
	public function register_checkout_overlay_assets() {
		if ( ! $this->should_register_overlay_assets() ) {
			return;
		}

		wp_register_style(
			'vms-efpg-checkout-popup',
			vms_efpg_asset_url( 'assets/css/checkout-popup.css' ),
			array(),
			VMS_EFPG_VERSION
		);

		wp_register_script(
			'fastspring-builder',
			self::SBL_SCRIPT_URL,
			array(),
			'1.0.7',
			true
		);

		wp_register_script(
			'vms-efpg-overlay-shell',
			vms_efpg_asset_url( 'assets/js/overlay-shell.js' ),
			array(),
			VMS_EFPG_VERSION,
			true
		);

		wp_register_script(
			'vms-efpg-checkout-popup',
			vms_efpg_asset_url( 'assets/js/checkout-popup.js' ),
			array( 'jquery', 'fastspring-builder', 'wp-api-fetch', 'vms-efpg-overlay-shell' ),
			VMS_EFPG_VERSION,
			true
		);

		wp_register_script(
			'vms-efpg-payment-complete',
			vms_efpg_asset_url( 'assets/js/checkout-popup.js' ),
			array(),
			VMS_EFPG_VERSION,
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
		if ( ! function_exists( 'vms_efpg' ) || ! vms_efpg()->settings ) {
			return;
		}

		$settings = vms_efpg()->settings;
		if ( ! $settings->has_popup_checkout() ) {
			return;
		}

		$localize = array(
			'popupStorefront' => $settings->popup_storefront(),
			'hostedDomain'    => $settings->storefront(),
			'accessKey'       => $settings->access_key(),
			'checkoutUrl'     => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' ),
			'restUrl'         => rest_url( 'vms-efpg/v1/overlay/' ),
			'completeRestUrl' => rest_url( 'vms-efpg/v1/complete/' ),
			'restNonce'       => wp_create_nonce( 'wp_rest' ),
			'debug'           => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'i18n'            => VMS_EFPG_Assets::checkout_js_i18n(),
		);

		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			wp_enqueue_style( 'vms-efpg-checkout-popup' );
			wp_enqueue_script( 'fastspring-builder' );
			wp_enqueue_script( 'vms-efpg-overlay-shell' );
			wp_localize_script(
				'vms-efpg-overlay-shell',
				'VMS_EFPG_OverlayShell',
				array(
					'i18n' => array(
						'checkoutAriaLabel' => __( 'FastSpring checkout', 'vms-elements-fastspring-payment-gateway' ),
					),
				)
			);
			wp_enqueue_script( 'vms-efpg-checkout-popup' );
			wp_localize_script( 'vms-efpg-checkout-popup', 'VMS_EFPG_Checkout', $localize );
			if ( function_exists( 'wp_set_script_translations' ) ) {
				wp_set_script_translations( 'vms-efpg-checkout-popup', 'vms-elements-fastspring-payment-gateway' );
				wp_set_script_translations( 'vms-efpg-overlay-shell', 'vms-elements-fastspring-payment-gateway' );
			}
			return;
		}

		if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-pay' ) ) {
			wp_enqueue_style( 'vms-efpg-checkout-popup' );
			wp_enqueue_script( 'fastspring-builder' );
			wp_enqueue_script( 'vms-efpg-overlay-shell' );
			wp_localize_script(
				'vms-efpg-overlay-shell',
				'VMS_EFPG_OverlayShell',
				array(
					'i18n' => array(
						'checkoutAriaLabel' => __( 'FastSpring checkout', 'vms-elements-fastspring-payment-gateway' ),
					),
				)
			);
			wp_enqueue_script( 'vms-efpg-checkout-popup' );
			wp_localize_script( 'vms-efpg-checkout-popup', 'VMS_EFPG_Checkout', $localize );
			if ( function_exists( 'wp_set_script_translations' ) ) {
				wp_set_script_translations( 'vms-efpg-checkout-popup', 'vms-elements-fastspring-payment-gateway' );
				wp_set_script_translations( 'vms-efpg-overlay-shell', 'vms-elements-fastspring-payment-gateway' );
			}
			return;
		}

		wp_enqueue_style( 'vms-efpg-checkout-popup' );
		wp_enqueue_script( 'vms-efpg-payment-complete' );
		wp_localize_script( 'vms-efpg-payment-complete', 'VMS_EFPG_Checkout', $localize );
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
		if ( ! function_exists( 'vms_efpg' ) || ! vms_efpg()->settings ) {
			return $tag;
		}

		$popup_storefront = vms_efpg()->settings->popup_storefront();
		if ( '' === $popup_storefront ) {
			return $tag;
		}

		$access_key = vms_efpg()->settings->access_key();

		return VMS_EFPG_Assets::enhance_sbl_script_tag(
			$tag,
			$handle,
			'fastspring-builder',
			$popup_storefront,
			array(
				'popup_closed'   => 'VMS_EFPG_PopupClosed',
				'error_callback' => 'VMS_EFPG_ErrorCallback',
				'access_key'     => $access_key,
			)
		);
	}

	/**
	 * Build overlay payload for an order (shared by AJAX, REST, and bridge page).
	 *
	 * @param WC_Order $order Order.
	 * @return array
	 */
	private function build_overlay_for_order( WC_Order $order ) {
		$session_id   = (string) $order->get_meta( '_vms_efpg_session_id' );
		$push_payload = null;
		$use_secure   = false;

		if ( function_exists( 'WC' ) && WC()->payment_gateways() ) {
			$gateways = WC()->payment_gateways()->payment_gateways();
			if ( isset( $gateways['vms_efpg'] ) && $gateways['vms_efpg'] instanceof VMS_EFPG_WC_Gateway ) {
				/** @var VMS_EFPG_WC_Gateway $gateway */
				$gateway      = $gateways['vms_efpg'];
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
			'tags'        => VMS_EFPG_Data_Store::build_session_tags( $order ),
		);

		if ( class_exists( 'VMS_EFPG_Checkout_Overlay' ) ) {
			$overlay = VMS_EFPG_Checkout_Overlay::stash( $order->get_id(), $overlay );
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
		if ( ! $order || 'vms_efpg' !== $order->get_payment_method() ) {
			return $result;
		}

		if ( ! function_exists( 'vms_efpg' ) || ! vms_efpg()->settings ) {
			return $result;
		}

		$settings   = vms_efpg()->settings;
		$session_id = (string) $order->get_meta( '_vms_efpg_session_id' );

		if ( '' === $session_id || ! $settings->has_popup_checkout() ) {
			return $result;
		}

		$overlay = $this->build_overlay_for_order( $order );

		// Popup-only: stay on checkout — JS opens the FastSpring overlay (no order-received redirect).
		$result['redirect']           = false;
		$result['vms_efpg_overlay']   = $overlay;
		$result['payment_details']    = isset( $result['payment_details'] ) && is_array( $result['payment_details'] )
			? $result['payment_details']
			: array();
		$result['payment_details']['vms_efpg_overlay'] = $overlay;

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
		if ( ! $order instanceof WC_Order || 'vms_efpg' !== $order->get_payment_method() ) {
			return $payment_result;
		}

		if ( ! function_exists( 'vms_efpg' ) || ! vms_efpg()->settings || ! vms_efpg()->settings->has_popup_checkout() ) {
			return $payment_result;
		}

		$session_id = (string) $order->get_meta( '_vms_efpg_session_id' );
		if ( '' === $session_id ) {
			return $payment_result;
		}

		$overlay = $this->build_overlay_for_order( $order );

		$payment_result['redirect_url'] = '';
		if ( ! isset( $payment_result['payment_details'] ) || ! is_array( $payment_result['payment_details'] ) ) {
			$payment_result['payment_details'] = array();
		}
		$payment_result['payment_details']['vms_efpg_overlay'] = $overlay;

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
		if ( 'vms_efpg' !== $method ) {
			return;
		}

		if ( method_exists( $result, 'set_redirect_url' ) ) {
			$result->set_redirect_url( '' );
		}
	}

	/**
	 * Render the FastSpring popup bridge when the buyer is sent to
	 * /?vms_efpg_pay=<order_id>&key=<order_key> after placing an order.
	 */
	public function maybe_render_checkout_bridge() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Order key acts as the nonce.
		if ( empty( $_GET['vms_efpg_pay'] ) ) {
			return;
		}
		if ( ! function_exists( 'wc_get_order' ) || ! function_exists( 'vms_efpg' ) ) {
			return;
		}

		$order_id = absint( $_GET['vms_efpg_pay'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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

		$settings    = vms_efpg()->settings;
		$session_id  = (string) $order->get_meta( '_vms_efpg_session_id' );
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
		$settings    = function_exists( 'vms_efpg' ) ? vms_efpg()->settings : null;
		$access_key  = $settings ? $settings->access_key() : '';
		$loading     = esc_html__( 'Opening secure checkout…', 'vms-elements-fastspring-payment-gateway' );
		$retry       = esc_html__( 'Return to checkout', 'vms-elements-fastspring-payment-gateway' );
		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="vms-efpg-fastspring-checkout">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="robots" content="noindex,nofollow" />
	<title><?php echo esc_html__( 'Redirecting to secure checkout', 'vms-elements-fastspring-payment-gateway' ); ?></title>
	<?php
		VMS_EFPG_Assets::print_standalone_head_assets(
			array(
				'popup_storefront' => $popup_storefront,
				'access_key'       => $access_key,
				'popup_closed'     => 'VMS_EFPG_Closed',
				'error_callback'   => 'VMS_EFPG_ErrorCallback',
			)
		);
		?>
</head>
<body class="vms-efpg-fastspring-checkout">
	<main id="vms-efpg-fastspring-overlay-root" class="vms-efpg-fastspring-overlay-root" aria-live="polite">
		<div class="vms-efpg-checkout-shell vms-efpg-checkout-shell--loading">
			<div class="vms-efpg-spinner" aria-hidden="true"></div>
			<p><?php echo $loading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></p>
			<p><a id="vms-efpg-fallback" href="<?php echo esc_url( $cancel_url ); ?>"><?php echo $retry; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></a></p>
		</div>
	</main>
	<?php
		VMS_EFPG_Assets::register_checkout_assets();
		wp_localize_script(
			'vms-efpg-checkout-bridge',
			'VMS_EFPG_CheckoutBridge',
			array(
				'overlay'    => $overlay,
				'successUrl' => $success_url,
				'cancelUrl'  => $cancel_url,
			)
		);
		wp_enqueue_script( 'vms-efpg-checkout-bridge' );
		wp_print_scripts( array( 'vms-efpg-checkout-bridge' ) );
		?>
</body>
</html>
		<?php
	}

	/**
	 * Register the FastSpring payment method type with WooCommerce Blocks.
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
		if ( ! class_exists( 'VMS_EFPG_WC_Blocks', false ) ) {
			require_once VMS_EFPG_PATH . 'includes/class-vms-efpg-wc-blocks.php';
		}
		if ( ! class_exists( 'VMS_EFPG_WC_Blocks', false ) ) {
			return;
		}
		if ( method_exists( $registry, 'get_all_registered' ) ) {
			$registered = $registry->get_all_registered();
			if ( is_array( $registered ) && isset( $registered['vms_efpg'] ) ) {
				return;
			}
		}
		$registry->register( new VMS_EFPG_WC_Blocks() );
	}
}

