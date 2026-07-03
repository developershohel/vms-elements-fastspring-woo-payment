<?php
/**
 * Shared script/style registration and standalone page asset output.
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFPG_Assets.
 */
class VMS_EFPG_Assets {

	const SBL_SCRIPT_URL = 'https://sbl.onfastspring.com/sbl/1.0.7/fastspring-builder.min.js';

	/**
	 * Register checkout-related assets (idempotent).
	 */
	public static function register_checkout_assets() {
		if ( wp_style_is( 'vms-efpg-checkout-popup', 'registered' ) ) {
			return;
		}

		wp_register_style(
			'vms-efpg-checkout-popup',
			vms_efpg_asset_url( 'assets/css/checkout-popup.css' ),
			array(),
			VMS_EFPG_VERSION
		);

		wp_register_script(
			'vms-efpg-overlay-shell',
			vms_efpg_asset_url( 'assets/js/overlay-shell.js' ),
			array(),
			VMS_EFPG_VERSION,
			true
		);

		wp_register_script(
			'vms-efpg-checkout-bridge',
			vms_efpg_asset_url( 'assets/js/checkout-bridge.js' ),
			array( 'vms-efpg-overlay-shell' ),
			VMS_EFPG_VERSION,
			true
		);

		wp_register_script(
			'vms-efpg-standalone-sbl',
			self::SBL_SCRIPT_URL,
			array(),
			'1.0.7',
			false
		);
	}

	/**
	 * Translatable strings shared by checkout JavaScript.
	 *
	 * @return array
	 */
	public static function checkout_js_i18n() {
		return array(
			'error'                    => __( 'FastSpring checkout could not open. Confirm your popup checkout path is configured and your site domain is whitelisted in FastSpring.', 'vms-elements-fastspring-payment-gateway' ),
			'missingPopup'             => __( 'FastSpring popup checkout path is not configured in FastSpring → Settings.', 'vms-elements-fastspring-payment-gateway' ),
			'missingAccessKey'         => __( 'FastSpring Store Builder access key is required for custom WooCommerce pricing. Add it in FastSpring → Settings (Developer Tools → Store Builder Library in the FastSpring app).', 'vms-elements-fastspring-payment-gateway' ),
			'loadFailed'               => __( 'FastSpring Store Builder did not load. Check your popup checkout path and whitelisted domains.', 'vms-elements-fastspring-payment-gateway' ),
			'openFailed'               => __( 'FastSpring checkout failed to open.', 'vms-elements-fastspring-payment-gateway' ),
			'checkoutClosed'           => __( 'Checkout closed without completing payment.', 'vms-elements-fastspring-payment-gateway' ),
			/* translators: 1: error code, 2: error message */
			'sblError'                 => __( 'SBL error: %1$s — %2$s', 'vms-elements-fastspring-payment-gateway' ),
			'confirmPendingRedirect'   => __( 'Payment confirm pending — redirecting to thank-you page to finish.', 'vms-elements-fastspring-payment-gateway' ),
			/* translators: %s: error message */
			'confirmFailed'            => __( 'Payment confirm failed: %s', 'vms-elements-fastspring-payment-gateway' ),
			'overlayFetchFailed'       => __( 'Overlay REST fetch failed.', 'vms-elements-fastspring-payment-gateway' ),
			'blockedOrderReceivedNav'  => __( 'Blocked navigation to order-received while popup is pending.', 'vms-elements-fastspring-payment-gateway' ),
			'blockedPushState'         => __( 'Blocked pushState to order-received while popup is pending.', 'vms-elements-fastspring-payment-gateway' ),
			/* translators: %s: navigation method name */
			'blockedNavMethod'         => __( 'Blocked %s to order-received while popup is pending.', 'vms-elements-fastspring-payment-gateway' ),
			/* translators: %s: order ID or "unknown" */
			'openingPopupOrder'        => __( 'Opening popup for order %s', 'vms-elements-fastspring-payment-gateway' ),
			'completingOrder'          => __( 'Finalizing your payment…', 'vms-elements-fastspring-payment-gateway' ),
			'unknownOrder'             => __( 'unknown', 'vms-elements-fastspring-payment-gateway' ),
			'checkoutAriaLabel'        => __( 'FastSpring checkout', 'vms-elements-fastspring-payment-gateway' ),
		);
	}

	/**
	 * Inject FastSpring Store Builder attributes into an enqueued script tag.
	 *
	 * @param string $tag              Original script tag from WordPress.
	 * @param string $handle           Current script handle.
	 * @param string $expected_handle  Handle to modify.
	 * @param string $storefront       Popup storefront value.
	 * @param array  $options          Optional popup_closed, error_callback, access_key.
	 * @return string
	 */
	public static function enhance_sbl_script_tag( $tag, $handle, $expected_handle, $storefront, $options = array() ) {
		if ( $expected_handle !== $handle || '' === $storefront || false === strpos( $tag, '<script' ) ) {
			return $tag;
		}

		$options = wp_parse_args(
			$options,
			array(
				'popup_closed'   => '',
				'error_callback' => '',
				'access_key'     => '',
			)
		);

		$inject  = ' id="fsc-api" type="text/javascript" data-storefront="' . esc_attr( $storefront ) . '" data-continuous="true"';
		if ( $options['popup_closed'] ) {
			$inject .= ' data-popup-closed="' . esc_attr( $options['popup_closed'] ) . '"';
		}
		if ( $options['error_callback'] ) {
			$inject .= ' data-error-callback="' . esc_attr( $options['error_callback'] ) . '"';
		}
		if ( $options['access_key'] ) {
			$inject .= ' data-access-key="' . esc_attr( $options['access_key'] ) . '"';
		}

		return preg_replace( '/<script(\s)/', '<script' . $inject . '$1', $tag, 1 );
	}

	/**
	 * Enqueue assets for a minimal standalone HTML page and print them in <head>.
	 *
	 * @param array $args {
	 *     @type string $popup_storefront data-storefront value.
	 *     @type string $access_key       Optional SBL access key.
	 *     @type string $popup_closed     Global callback name for popup closed.
	 *     @type string $error_callback   Global callback name for SBL errors.
	 *     @type bool   $include_sbl      Whether to print the Store Builder script.
	 *     @type bool   $include_shell    Whether to print overlay-shell.js.
	 *     @type string $style_handle     Style handle to print (default checkout popup).
	 * }
	 */
	public static function print_standalone_head_assets( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'popup_storefront' => '',
				'access_key'       => '',
				'popup_closed'     => '',
				'error_callback'   => '',
				'include_sbl'      => true,
				'include_shell'    => true,
				'style_handle'     => 'vms-efpg-checkout-popup',
			)
		);

		self::register_checkout_assets();

		wp_enqueue_style( $args['style_handle'] );
		wp_print_styles( array( $args['style_handle'] ) );

		if ( $args['include_sbl'] && $args['popup_storefront'] ) {
			$filter = static function ( $tag, $handle, $src ) use ( $args ) {
				unset( $src );
				return self::enhance_sbl_script_tag(
					$tag,
					$handle,
					'vms-efpg-standalone-sbl',
					$args['popup_storefront'],
					array(
						'popup_closed'   => $args['popup_closed'],
						'error_callback' => $args['error_callback'],
						'access_key'     => $args['access_key'],
					)
				);
			};

			add_filter( 'script_loader_tag', $filter, 10, 3 );
			wp_enqueue_script( 'vms-efpg-standalone-sbl' );
			wp_print_scripts( array( 'vms-efpg-standalone-sbl' ) );
			remove_filter( 'script_loader_tag', $filter, 10 );
		}

		if ( $args['include_shell'] ) {
			wp_localize_script(
				'vms-efpg-overlay-shell',
				'VMS_EFPG_OverlayShell',
				array(
					'i18n' => array(
						'checkoutAriaLabel' => __( 'FastSpring checkout', 'vms-elements-fastspring-payment-gateway' ),
					),
				)
			);
			wp_enqueue_script( 'vms-efpg-overlay-shell' );
			wp_print_scripts( array( 'vms-efpg-overlay-shell' ) );
		}
	}
}
