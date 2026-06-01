<?php
/**
 * FastSpring REST API client.
 *
 * Documentation: https://developer.fastspring.com/reference/getting-started-with-the-api
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_API.
 *
 * Authenticates against api.fastspring.com using HTTP Basic Auth with the
 * API username/password configured in the FastSpring dashboard.
 */
class WP_FastSpring_API {

	const BASE_URL = 'https://api.fastspring.com';

	/**
	 * Settings.
	 *
	 * @var WP_FastSpring_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param WP_FastSpring_Settings $settings Settings handler.
	 */
	public function __construct( WP_FastSpring_Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Build Authorization header.
	 *
	 * @return string|WP_Error
	 */
	private function auth_header() {
		$user = $this->settings->api_username();
		$pass = $this->settings->api_password();
		if ( '' === $user || '' === $pass ) {
			return new WP_Error( 'wp_fastspring_missing_credentials', __( 'FastSpring API credentials are missing for the active mode.', 'wp-fastspring' ) );
		}
		return 'Basic ' . base64_encode( $user . ':' . $pass );
	}

	/**
	 * Generic request wrapper.
	 *
	 * @param string $method  HTTP method.
	 * @param string $path    Endpoint path beginning with a slash.
	 * @param array  $args    Query args (for GET) or body args (for POST/PUT).
	 * @param array  $options Extra options (timeout, headers, raw_body).
	 * @return array|WP_Error Decoded response array on success.
	 */
	public function request( $method, $path, $args = array(), $options = array() ) {
		$method = strtoupper( $method );
		$auth   = $this->auth_header();
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		$url = self::BASE_URL . $path;
		$req = array(
			'method'  => $method,
			'timeout' => isset( $options['timeout'] ) ? (int) $options['timeout'] : 25,
			'headers' => array_merge(
				array(
					'Authorization' => $auth,
					'Accept'        => 'application/json',
					'Content-Type'  => 'application/json',
					'User-Agent'    => 'WP-FastSpring/' . WP_FASTSPRING_VERSION . '; ' . home_url(),
				),
				isset( $options['headers'] ) ? (array) $options['headers'] : array()
			),
		);

		if ( in_array( $method, array( 'POST', 'PUT', 'PATCH', 'DELETE' ), true ) && ( ! empty( $args ) || ! empty( $options['raw_body'] ) ) ) {
			$req['body'] = isset( $options['raw_body'] ) ? $options['raw_body'] : wp_json_encode( $args );
		} elseif ( 'GET' === $method && ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		$response = wp_remote_request( $url, $req );

		if ( is_wp_error( $response ) ) {
			WP_FastSpring_Logger::error( 'API transport error: ' . $response->get_error_message(), 'api', array( 'url' => $url ) );
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( $code >= 400 ) {
			$message = is_array( $data ) && isset( $data['error'] ) ? wp_json_encode( $data['error'] ) : $body;
			WP_FastSpring_Logger::error(
				sprintf( 'API %s %s -> %d: %s', $method, $path, $code, $message ),
				'api',
				array( 'method' => $method, 'path' => $path )
			);
			return new WP_Error( 'wp_fastspring_api_' . $code, $message, array( 'status' => $code, 'body' => $data ) );
		}

		return is_array( $data ) ? $data : array();
	}

	/**
	 * Test connection.
	 *
	 * @return true|WP_Error
	 */
	public function test_connection() {
		$result = $this->request( 'GET', '/accounts', array( 'limit' => 1 ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		return true;
	}

	/* -------------------------------------------------------------------- *
	 * Accounts
	 * -------------------------------------------------------------------- */

	/**
	 * List/search accounts.
	 *
	 * @param array $params Optional query parameters (email, page, limit).
	 * @return array|WP_Error
	 */
	public function get_accounts( $params = array() ) {
		return $this->request( 'GET', '/accounts', $params );
	}

	/**
	 * Get a single account.
	 *
	 * @param string $account_id Account id.
	 * @return array|WP_Error
	 */
	public function get_account( $account_id ) {
		return $this->request( 'GET', '/accounts/' . rawurlencode( $account_id ) );
	}

	/**
	 * Create an account.
	 *
	 * @param array $payload Account data.
	 * @return array|WP_Error
	 */
	public function create_account( $payload ) {
		return $this->request( 'POST', '/accounts', $payload );
	}

	/**
	 * Update an account.
	 *
	 * @param string $account_id Account id.
	 * @param array  $payload Updates.
	 * @return array|WP_Error
	 */
	public function update_account( $account_id, $payload ) {
		return $this->request( 'POST', '/accounts/' . rawurlencode( $account_id ), $payload );
	}

	/**
	 * Generate an authenticated account management URL.
	 *
	 * @param string $account_id Account id.
	 * @return array|WP_Error
	 */
	public function get_account_management_url( $account_id ) {
		return $this->request( 'GET', '/accounts/' . rawurlencode( $account_id ) . '/authenticate' );
	}

	/* -------------------------------------------------------------------- *
	 * Coupons
	 * -------------------------------------------------------------------- */

	/**
	 * List coupons.
	 *
	 * @param array $params Optional query.
	 * @return array|WP_Error
	 */
	public function get_coupons( $params = array() ) {
		return $this->request( 'GET', '/coupons', $params );
	}

	/**
	 * Get a single coupon.
	 *
	 * @param string $coupon_id Coupon id.
	 * @return array|WP_Error
	 */
	public function get_coupon( $coupon_id ) {
		return $this->request( 'GET', '/coupons/' . rawurlencode( $coupon_id ) );
	}

	/**
	 * Generate coupon codes for a coupon.
	 *
	 * @param string $coupon_id Coupon id.
	 * @param array  $payload   Generate args.
	 * @return array|WP_Error
	 */
	public function generate_coupon_codes( $coupon_id, $payload = array() ) {
		return $this->request( 'POST', '/coupons/' . rawurlencode( $coupon_id ) . '/codes', $payload );
	}

	/**
	 * Create a coupon.
	 *
	 * @param array $payload Coupon definition.
	 * @return array|WP_Error
	 */
	public function create_coupon( $payload ) {
		return $this->request( 'POST', '/coupons', $payload );
	}

	/**
	 * Update a coupon.
	 *
	 * @param string $coupon_id Coupon id.
	 * @param array  $payload Updates.
	 * @return array|WP_Error
	 */
	public function update_coupon( $coupon_id, $payload ) {
		return $this->request( 'POST', '/coupons/' . rawurlencode( $coupon_id ), $payload );
	}

	/**
	 * Delete a coupon.
	 *
	 * @param string $coupon_id Coupon id.
	 * @return array|WP_Error
	 */
	public function delete_coupon( $coupon_id ) {
		return $this->request( 'DELETE', '/coupons/' . rawurlencode( $coupon_id ) );
	}

	/* -------------------------------------------------------------------- *
	 * Products
	 * -------------------------------------------------------------------- */

	/**
	 * List all product paths.
	 *
	 * @return array|WP_Error
	 */
	public function list_products() {
		return $this->request( 'GET', '/products' );
	}

	/**
	 * Get full product details for given paths.
	 *
	 * @param string|array $paths Product paths.
	 * @return array|WP_Error
	 */
	public function get_products( $paths ) {
		$paths = (array) $paths;
		if ( empty( $paths ) ) {
			return array();
		}
		$ids = implode( ',', array_map( 'rawurlencode', $paths ) );
		return $this->request( 'GET', '/products/' . $ids );
	}

	/**
	 * Get a single product.
	 *
	 * @param string $product_path Product slug.
	 * @return array|WP_Error
	 */
	public function get_product( $product_path ) {
		return $this->request( 'GET', '/products/' . rawurlencode( $product_path ) );
	}

	/**
	 * Create or update one or many products.
	 *
	 * @param array $products Products array (each indexed by product path).
	 * @return array|WP_Error
	 */
	public function upsert_products( $products ) {
		return $this->request( 'POST', '/products', array( 'products' => array_values( $products ) ) );
	}

	/**
	 * Convenience: upsert a single product.
	 *
	 * @param array $product Product.
	 * @return array|WP_Error
	 */
	public function upsert_product( $product ) {
		return $this->upsert_products( array( $product ) );
	}

	/**
	 * Delete a product.
	 *
	 * @param string $product_path Product slug.
	 * @return array|WP_Error
	 */
	public function delete_product( $product_path ) {
		return $this->request( 'DELETE', '/products/' . rawurlencode( $product_path ) );
	}

	/**
	 * Get product price details for a country/currency.
	 *
	 * @param string $product_path Path.
	 * @param array  $params       Query (country, currency, ...).
	 * @return array|WP_Error
	 */
	public function get_product_price( $product_path, $params = array() ) {
		return $this->request( 'GET', '/products/price/' . rawurlencode( $product_path ), $params );
	}

	/* -------------------------------------------------------------------- *
	 * Orders
	 * -------------------------------------------------------------------- */

	/**
	 * Get an order by id or reference.
	 *
	 * @param string $order_id Order ID.
	 * @return array|WP_Error
	 */
	public function get_order( $order_id ) {
		return $this->request( 'GET', '/orders/' . rawurlencode( $order_id ) );
	}

	/**
	 * Search orders.
	 *
	 * @param array $params Query parameters.
	 * @return array|WP_Error
	 */
	public function search_orders( $params = array() ) {
		return $this->request( 'GET', '/orders/search', $params );
	}

	/**
	 * Update order tags or attributes.
	 *
	 * @param string $order_id Order id.
	 * @param array  $payload  Update.
	 * @return array|WP_Error
	 */
	public function update_order( $order_id, $payload ) {
		return $this->request( 'POST', '/orders/' . rawurlencode( $order_id ), $payload );
	}

	/* -------------------------------------------------------------------- *
	 * Subscriptions
	 * -------------------------------------------------------------------- */

	/**
	 * Get one or many subscriptions.
	 *
	 * @param string|array $ids ID or array of IDs.
	 * @return array|WP_Error
	 */
	public function get_subscription( $ids ) {
		$ids = (array) $ids;
		$enc = array_map( 'rawurlencode', $ids );
		return $this->request( 'GET', '/subscriptions/' . implode( ',', $enc ) );
	}

	/**
	 * Get subscription entries (charge history).
	 *
	 * @param string $subscription_id ID.
	 * @return array|WP_Error
	 */
	public function get_subscription_entries( $subscription_id ) {
		return $this->request( 'GET', '/subscriptions/' . rawurlencode( $subscription_id ) . '/entries' );
	}

	/**
	 * Update a subscription.
	 *
	 * @param array $subscriptions Subscriptions array.
	 * @return array|WP_Error
	 */
	public function update_subscriptions( $subscriptions ) {
		return $this->request( 'POST', '/subscriptions', array( 'subscriptions' => array_values( $subscriptions ) ) );
	}

	/**
	 * Cancel a subscription.
	 *
	 * @param string $subscription_id ID.
	 * @param bool   $immediate If true, cancel immediately instead of at period end.
	 * @return array|WP_Error
	 */
	public function cancel_subscription( $subscription_id, $immediate = false ) {
		$path = '/subscriptions/' . rawurlencode( $subscription_id );
		if ( $immediate ) {
			$path .= '?billingPeriod=0';
		}
		return $this->request( 'DELETE', $path );
	}

	/**
	 * Pause a subscription.
	 *
	 * @param string $subscription_id ID.
	 * @param array  $args  Args (e.g. periods).
	 * @return array|WP_Error
	 */
	public function pause_subscription( $subscription_id, $args = array() ) {
		return $this->request( 'POST', '/subscriptions/' . rawurlencode( $subscription_id ) . '/pause', $args );
	}

	/**
	 * Resume a subscription.
	 *
	 * @param string $subscription_id ID.
	 * @return array|WP_Error
	 */
	public function resume_subscription( $subscription_id ) {
		return $this->request( 'POST', '/subscriptions/' . rawurlencode( $subscription_id ) . '/resume' );
	}

	/**
	 * Uncancel a subscription previously marked for cancellation.
	 *
	 * @param string $subscription_id ID.
	 * @return array|WP_Error
	 */
	public function uncancel_subscription( $subscription_id ) {
		return $this->request( 'POST', '/subscriptions/' . rawurlencode( $subscription_id ) . '/uncancel' );
	}

	/**
	 * Convert a subscription to a different product.
	 *
	 * @param string $subscription_id ID.
	 * @param array  $payload         Conversion payload (product, prorate, etc).
	 * @return array|WP_Error
	 */
	public function convert_subscription( $subscription_id, $payload ) {
		return $this->request( 'POST', '/subscriptions/' . rawurlencode( $subscription_id ) . '/convert', $payload );
	}

	/**
	 * Estimate the cost of a subscription change.
	 *
	 * @param array $payload Estimate args.
	 * @return array|WP_Error
	 */
	public function estimate_subscription_change( $payload ) {
		return $this->request( 'POST', '/subscriptions/estimate', $payload );
	}

	/* -------------------------------------------------------------------- *
	 * Invoices
	 * -------------------------------------------------------------------- */

	/**
	 * Search invoices.
	 *
	 * @param array $params Query.
	 * @return array|WP_Error
	 */
	public function search_invoices( $params = array() ) {
		return $this->request( 'GET', '/invoices', $params );
	}

	/**
	 * Get a single invoice.
	 *
	 * @param string $invoice_id ID.
	 * @return array|WP_Error
	 */
	public function get_invoice( $invoice_id ) {
		return $this->request( 'GET', '/invoices/' . rawurlencode( $invoice_id ) );
	}

	/**
	 * Generate a proforma invoice.
	 *
	 * @param array $payload Invoice spec.
	 * @return array|WP_Error
	 */
	public function create_proforma_invoice( $payload ) {
		return $this->request( 'POST', '/invoices/proforma', $payload );
	}

	/* -------------------------------------------------------------------- *
	 * Quotes
	 * -------------------------------------------------------------------- */

	/**
	 * Search/list quotes.
	 *
	 * @param array $params Query parameters.
	 * @return array|WP_Error
	 */
	public function get_quotes( $params = array() ) {
		return $this->request( 'GET', '/quotes', $params );
	}

	/**
	 * Get a single quote.
	 *
	 * @param string $quote_id ID.
	 * @return array|WP_Error
	 */
	public function get_quote( $quote_id ) {
		return $this->request( 'GET', '/quotes/' . rawurlencode( $quote_id ) );
	}

	/**
	 * Create a quote.
	 *
	 * @param array $payload Quote args.
	 * @return array|WP_Error
	 */
	public function create_quote( $payload ) {
		return $this->request( 'POST', '/quotes', $payload );
	}

	/**
	 * Update a quote.
	 *
	 * @param string $quote_id ID.
	 * @param array  $payload  Updates.
	 * @return array|WP_Error
	 */
	public function update_quote( $quote_id, $payload ) {
		return $this->request( 'POST', '/quotes/' . rawurlencode( $quote_id ), $payload );
	}

	/* -------------------------------------------------------------------- *
	 * Returns / refunds
	 * -------------------------------------------------------------------- */

	/**
	 * Issue a return / refund.
	 *
	 * @param array $payload Args.
	 * @return array|WP_Error
	 */
	public function create_return( $payload ) {
		return $this->request( 'POST', '/returns', $payload );
	}

	/**
	 * Search returns.
	 *
	 * @param array $params Query.
	 * @return array|WP_Error
	 */
	public function search_returns( $params = array() ) {
		return $this->request( 'GET', '/returns', $params );
	}

	/**
	 * Get a single return.
	 *
	 * @param string $return_id ID.
	 * @return array|WP_Error
	 */
	public function get_return( $return_id ) {
		return $this->request( 'GET', '/returns/' . rawurlencode( $return_id ) );
	}

	/* -------------------------------------------------------------------- *
	 * Sessions (V1 - storefront sessions)
	 * -------------------------------------------------------------------- */

	/**
	 * Create a checkout session.
	 *
	 * @param array $payload Session payload.
	 * @return array|WP_Error
	 */
	public function create_session( $payload ) {
		return $this->request( 'POST', '/sessions', $payload );
	}

	/**
	 * Get a checkout session.
	 *
	 * @param string $session_id ID.
	 * @return array|WP_Error
	 */
	public function get_session( $session_id ) {
		return $this->request( 'GET', '/sessions/' . rawurlencode( $session_id ) );
	}

	/* -------------------------------------------------------------------- *
	 * Sessions V2
	 * -------------------------------------------------------------------- */

	/**
	 * Create a Sessions V2 checkout session.
	 *
	 * @param array $payload Payload.
	 * @return array|WP_Error
	 */
	public function create_session_v2( $payload ) {
		return $this->request( 'POST', '/sessions/v2', $payload );
	}

	/**
	 * Update a Sessions V2 session.
	 *
	 * @param string $session_id ID.
	 * @param array  $payload    Patch payload.
	 * @return array|WP_Error
	 */
	public function update_session_v2( $session_id, $payload ) {
		return $this->request( 'POST', '/sessions/v2/' . rawurlencode( $session_id ), $payload );
	}

	/**
	 * Get a Sessions V2 session.
	 *
	 * @param string $session_id ID.
	 * @return array|WP_Error
	 */
	public function get_session_v2( $session_id ) {
		return $this->request( 'GET', '/sessions/v2/' . rawurlencode( $session_id ) );
	}

	/* -------------------------------------------------------------------- *
	 * Events
	 * -------------------------------------------------------------------- */

	/**
	 * List events of a given type.
	 *
	 * @param string $type   Type: processed | unprocessed | all.
	 * @param array  $params Query params (begin, end, type, days, etc).
	 * @return array|WP_Error
	 */
	public function get_events( $type = 'processed', $params = array() ) {
		$type = in_array( $type, array( 'processed', 'unprocessed' ), true ) ? $type : 'processed';
		return $this->request( 'GET', '/events/' . $type, $params );
	}

	/**
	 * Mark an event as processed.
	 *
	 * @param string $event_id ID.
	 * @return array|WP_Error
	 */
	public function mark_event_processed( $event_id ) {
		return $this->request( 'POST', '/events/' . rawurlencode( $event_id ), array( 'processed' => true ) );
	}

	/* -------------------------------------------------------------------- *
	 * Data / Reports
	 * -------------------------------------------------------------------- */

	/**
	 * Trigger a Data report and get the request id.
	 *
	 * @param string $type   Report type, e.g. 'revenue', 'subscription', 'order'.
	 * @param array  $params Report parameters (begin, end, currency, etc).
	 * @return array|WP_Error
	 */
	public function create_report( $type, $params = array() ) {
		return $this->request( 'POST', '/data/v1/' . rawurlencode( $type ), $params );
	}

	/**
	 * Check the status of a previously requested report.
	 *
	 * @param string $request_id Request id.
	 * @return array|WP_Error
	 */
	public function get_report_status( $request_id ) {
		return $this->request( 'GET', '/data/v1/status/' . rawurlencode( $request_id ) );
	}

	/**
	 * Download a finished report payload.
	 *
	 * @param string $request_id Request id.
	 * @return array|WP_Error
	 */
	public function download_report( $request_id ) {
		return $this->request( 'GET', '/data/v1/download/' . rawurlencode( $request_id ) );
	}

	/* -------------------------------------------------------------------- *
	 * Webhooks (HMAC management)
	 * -------------------------------------------------------------------- */

	/**
	 * List configured webhooks.
	 *
	 * @return array|WP_Error
	 */
	public function get_webhooks() {
		return $this->request( 'GET', '/webhooks' );
	}

	/**
	 * Get the current HMAC secret for a webhook id.
	 *
	 * @param string $webhook_id Webhook id.
	 * @return array|WP_Error
	 */
	public function get_webhook_hmac( $webhook_id ) {
		return $this->request( 'GET', '/webhooks/' . rawurlencode( $webhook_id ) . '/hmac' );
	}

	/**
	 * Rotate (regenerate) the HMAC secret for a webhook.
	 *
	 * @param string $webhook_id Webhook id.
	 * @return array|WP_Error
	 */
	public function rotate_webhook_hmac( $webhook_id ) {
		return $this->request( 'POST', '/webhooks/' . rawurlencode( $webhook_id ) . '/hmac/rotate' );
	}
}
