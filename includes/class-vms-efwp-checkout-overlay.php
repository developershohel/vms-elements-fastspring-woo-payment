<?php
/**
 * REST + transient storage for FastSpring popup overlay payloads.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Checkout_Overlay.
 */
class VMS_EFWP_Checkout_Overlay {

	/**
	 * Transient prefix.
	 */
	const TRANSIENT_PREFIX = 'vms_efwp_overlay_';

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 */
	public static function register_routes() {
		register_rest_route(
			'vms-efwp/v1',
			'/overlay/(?P<order_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_overlay' ),
				'permission_callback' => array( __CLASS__, 'can_read_overlay' ),
				'args'                => array(
					'order_id' => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'key'      => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'token'    => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			'vms-efwp/v1',
			'/complete/(?P<order_id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'complete_payment' ),
				'permission_callback' => array( __CLASS__, 'can_read_overlay' ),
				'args'                => array(
					'order_id'    => array(
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'fs_order_id' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'key'         => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'token'       => array(
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Save overlay payload for later retrieval (Blocks checkout fallback).
	 *
	 * @param int   $order_id Order ID.
	 * @param array $overlay  Overlay payload.
	 * @return array Overlay payload including fetch token.
	 */
	public static function stash( $order_id, array $overlay ) {
		$order_id = absint( $order_id );
		if ( ! $order_id ) {
			return $overlay;
		}

		$token = wp_generate_password( 32, false, false );
		$overlay['fetchToken'] = $token;

		set_transient( self::TRANSIENT_PREFIX . $order_id, $overlay, 15 * MINUTE_IN_SECONDS );
		set_transient( self::TRANSIENT_PREFIX . 'token_' . $token, $order_id, 15 * MINUTE_IN_SECONDS );

		return $overlay;
	}

	/**
	 * Permission check for overlay REST endpoint.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public static function can_read_overlay( $request ) {
		$order_id = absint( $request->get_param( 'order_id' ) );
		$order    = wc_get_order( $order_id );

		if ( ! $order || 'vms_efwp' !== $order->get_payment_method() ) {
			return false;
		}

		$key = (string) $request->get_param( 'key' );
		if ( $key && hash_equals( $order->get_order_key(), $key ) ) {
			return true;
		}

		$token = (string) $request->get_param( 'token' );
		if ( $token ) {
			$token_order = get_transient( self::TRANSIENT_PREFIX . 'token_' . $token );
			return (int) $token_order === $order_id;
		}

		if ( $order->needs_payment() && get_transient( self::TRANSIENT_PREFIX . $order_id ) ) {
			return true;
		}

		return current_user_can( 'pay_for_order', $order_id );
	}

	/**
	 * Return overlay payload for an order.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_overlay( $request ) {
		$order_id = absint( $request->get_param( 'order_id' ) );
		$overlay  = get_transient( self::TRANSIENT_PREFIX . $order_id );

		if ( ! is_array( $overlay ) || empty( $overlay['sessionId'] ) ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return new WP_Error( 'invalid_order', __( 'Order not found.', 'vms-elements-fastspring-woo-payment' ), array( 'status' => 404 ) );
			}

			$session_id = (string) $order->get_meta( '_vms_efwp_session_id' );
			if ( '' === $session_id ) {
				return new WP_Error( 'missing_session', __( 'FastSpring session not found for this order.', 'vms-elements-fastspring-woo-payment' ), array( 'status' => 404 ) );
			}

			$overlay = array(
				'sessionId'  => $session_id,
				'successUrl' => $order->get_checkout_order_received_url(),
				'cancelUrl'  => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' ),
				'orderId'    => $order_id,
				'orderKey'   => $order->get_order_key(),
			);
		}

		if ( empty( $overlay['pushPayload'] ) && function_exists( 'WC' ) && WC()->payment_gateways() ) {
			$order = isset( $order ) ? $order : wc_get_order( $order_id );
			if ( $order ) {
				$gateways = WC()->payment_gateways()->payment_gateways();
				if ( isset( $gateways['vms_efwp'] ) && $gateways['vms_efwp'] instanceof VMS_EFWP_WC_Gateway ) {
					$overlay['pushPayload'] = $gateways['vms_efwp']->build_sbl_checkout_payload( $order );
					$overlay['useSecure']   = $gateways['vms_efwp']->uses_sbl_secure();
					$overlay['tags']        = array( 'wc_order_id' => (string) $order_id );
				}
			}
		}

		return rest_ensure_response( $overlay );
	}

	/**
	 * Confirm a FastSpring payment and mark the WooCommerce order paid.
	 *
	 * Used when the popup closes successfully. Webhooks remain the source of
	 * truth in production, but localhost and slow webhook delivery need this fallback.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function complete_payment( $request ) {
		$order_id    = absint( $request->get_param( 'order_id' ) );
		$fs_order_id = sanitize_text_field( (string) $request->get_param( 'fs_order_id' ) );

		if ( '' === $fs_order_id ) {
			return new WP_Error(
				'missing_fs_order',
				__( 'FastSpring order id is required.', 'vms-elements-fastspring-woo-payment' ),
				array( 'status' => 400 )
			);
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new WP_Error(
				'invalid_order',
				__( 'Order not found.', 'vms-elements-fastspring-woo-payment' ),
				array( 'status' => 404 )
			);
		}

		if ( $order->is_paid() ) {
			return rest_ensure_response(
				array(
					'status'      => 'already_paid',
					'order_id'    => $order_id,
					'fs_order_id' => $fs_order_id,
				)
			);
		}

		if ( ! function_exists( 'vms_efwp' ) || ! vms_efwp()->api ) {
			return new WP_Error(
				'plugin_unavailable',
				__( 'FastSpring API client is not available.', 'vms-elements-fastspring-woo-payment' ),
				array( 'status' => 500 )
			);
		}

		$fs_order = vms_efwp()->api->get_order( $fs_order_id );
		if ( is_wp_error( $fs_order ) ) {
			return $fs_order;
		}

		$tag_wc_id = isset( $fs_order['tags']['wc_order_id'] ) ? (int) $fs_order['tags']['wc_order_id'] : 0;
		if ( $tag_wc_id && $tag_wc_id !== $order_id ) {
			return new WP_Error(
				'order_mismatch',
				__( 'This FastSpring order is linked to a different WooCommerce order.', 'vms-elements-fastspring-woo-payment' ),
				array( 'status' => 403 )
			);
		}

		if ( empty( $fs_order['completed'] ) ) {
			return new WP_Error(
				'not_completed',
				__( 'FastSpring has not marked this order as completed yet. Please wait a moment and refresh.', 'vms-elements-fastspring-woo-payment' ),
				array( 'status' => 409 )
			);
		}

		self::apply_fastspring_payment( $order, $fs_order );

		return rest_ensure_response(
			array(
				'status'      => 'completed',
				'order_id'    => $order_id,
				'fs_order_id' => $fs_order_id,
			)
		);
	}

	/**
	 * Mark a WooCommerce order paid from a FastSpring order payload.
	 *
	 * @param WC_Order $order    WooCommerce order.
	 * @param array    $fs_order FastSpring order payload.
	 * @return bool
	 */
	public static function apply_fastspring_payment( WC_Order $order, array $fs_order ) {
		if ( $order->is_paid() ) {
			return true;
		}

		if ( empty( $fs_order['completed'] ) ) {
			return false;
		}

		$is_live = isset( $fs_order['live'] ) ? (bool) $fs_order['live'] : (
			function_exists( 'vms_efwp' ) && vms_efwp()->settings && ! vms_efwp()->settings->is_sandbox()
		);

		VMS_EFWP_Data_Store::upsert_order( $fs_order, ! $is_live );

		$fs_id = isset( $fs_order['id'] ) ? (string) $fs_order['id'] : '';
		$order->payment_complete( $fs_id );
		$order->add_order_note( __( 'Payment captured by FastSpring.', 'vms-elements-fastspring-woo-payment' ) );

		return true;
	}
}
