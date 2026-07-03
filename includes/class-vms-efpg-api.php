<?php
/**
 * FastSpring REST API client.
 *
 * Documentation: https://developer.fastspring.com/reference/getting-started-with-the-api
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFPG_API.
 *
 * Authenticates against api.fastspring.com using HTTP Basic Auth with the
 * API username/password configured in the FastSpring dashboard.
 */
class VMS_EFPG_API {

	const BASE_URL = 'https://api.fastspring.com';

	/**
	 * Settings.
	 *
	 * @var VMS_EFPG_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param VMS_EFPG_Settings $settings Settings handler.
	 */
	public function __construct( VMS_EFPG_Settings $settings ) {
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
			return new WP_Error( 'vms_efpg_missing_credentials', __( 'FastSpring API credentials are missing for the active mode.', 'vms-elements-fastspring-payment-gateway' ) );
		}
		return 'Basic ' . base64_encode( $user . ':' . $pass );
	}

	/**
	 * Keep only documented query parameters for an endpoint.
	 *
	 * @param array $params  Request params.
	 * @param array $allowed Allowed query keys.
	 * @return array
	 */
	private function filter_query_params( $params, $allowed ) {
		if ( ! is_array( $params ) || empty( $allowed ) ) {
			return array();
		}

		$filtered = array();
		foreach ( $allowed as $key ) {
			if ( ! array_key_exists( $key, $params ) ) {
				continue;
			}

			$value = $params[ $key ];
			if ( null === $value || '' === $value ) {
				continue;
			}

			$filtered[ $key ] = $value;
		}

		return $filtered;
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
					'User-Agent'    => 'vms-elements-fastspring-payment-gateway/' . VMS_EFPG_VERSION . '; ' . home_url(),
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
			VMS_EFPG_Logger::error( 'API transport error: ' . $response->get_error_message(), 'api', array( 'url' => $url ) );
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( $code >= 400 ) {
			$data    = json_decode( $body, true );
			$message = $this->extract_error_message( $data, $body, $code );
			VMS_EFPG_Logger::error(
				sprintf( 'API %s %s -> %d: %s', $method, $path, $code, $message ),
				'api',
				array( 'method' => $method, 'path' => $path, 'body' => $body )
			);
			return new WP_Error( 'vms_efpg_api_' . $code, $message, array( 'status' => $code, 'body' => $data ) );
		}

		if ( ! empty( $options['raw_response'] ) ) {
			return $body;
		}

		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			return array();
		}

		$result_error = $this->detect_result_error( $data );
		if ( is_wp_error( $result_error ) ) {
			VMS_EFPG_Logger::error(
				sprintf( 'API %s %s -> logical error: %s', $method, $path, $result_error->get_error_message() ),
				'api',
				array( 'method' => $method, 'path' => $path, 'body' => $body )
			);
			return $result_error;
		}

		return $data;
	}

	/**
	 * Detect FastSpring logical errors returned with HTTP 200.
	 *
	 * @param array $data Decoded JSON body.
	 * @return true|WP_Error
	 */
	private function detect_result_error( $data ) {
		if ( isset( $data['result'] ) && 'error' === $data['result'] ) {
			return new WP_Error(
				'vms_efpg_api_error',
				$this->extract_error_message( $data, '', 200 ),
				array( 'status' => 200, 'body' => $data )
			);
		}

		foreach ( array( 'products', 'orders' ) as $bucket ) {
			if ( empty( $data[ $bucket ] ) || ! is_array( $data[ $bucket ] ) ) {
				continue;
			}
			foreach ( $data[ $bucket ] as $item ) {
				if ( is_array( $item ) && isset( $item['result'] ) && 'error' === $item['result'] ) {
					return new WP_Error(
						'vms_efpg_api_error',
						$this->extract_error_message( $item, '', 200 ),
						array( 'status' => 200, 'body' => $data )
					);
				}
			}
		}

		return true;
	}

	/**
	 * Build a human-meaningful error string from a FastSpring error response.
	 *
	 * FastSpring is inconsistent: some errors arrive as { "error": {...} },
	 * some as a flat map of field => message, some (notably 409s) with an empty
	 * body. This guarantees we never surface a blank message to the merchant.
	 *
	 * @param mixed  $data Decoded JSON body (array) or null.
	 * @param string $body Raw response body.
	 * @param int    $code HTTP status code.
	 * @return string
	 */
	private function extract_error_message( $data, $body, $code ) {
		$parts = array();

		if ( is_array( $data ) ) {
			$prefix = '';
			if ( ! empty( $data['product'] ) && is_scalar( $data['product'] ) ) {
				$prefix = (string) $data['product'] . ': ';
			}

			if ( isset( $data['error'] ) ) {
				$parts[] = $prefix . ( is_scalar( $data['error'] ) ? (string) $data['error'] : wp_json_encode( $data['error'] ) );
			} elseif ( isset( $data['message'] ) && is_scalar( $data['message'] ) ) {
				$parts[] = (string) $data['message'];
			} else {
				foreach ( $data as $field => $value ) {
					$value   = is_scalar( $value ) ? (string) $value : wp_json_encode( $value );
					$parts[] = is_string( $field ) && ! is_numeric( $field ) ? $field . ': ' . $value : $value;
				}
			}
		} elseif ( is_string( $body ) && '' !== trim( $body ) ) {
			$parts[] = trim( $body );
		}

		$message = trim( implode( '; ', array_filter( $parts ) ) );

		if ( '' === $message ) {
			$message = sprintf(
				/* translators: %d: HTTP status code */
				__( 'HTTP %d with no error detail returned by FastSpring.', 'vms-elements-fastspring-payment-gateway' ),
				$code
			);
		}

		return $message;
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
	 * Accounts (payload parsing only)
	 * -------------------------------------------------------------------- */

	/**
	 * Extract a FastSpring account ID from an order/subscription payload.
	 *
	 * @param array $payload Resource payload.
	 * @return string
	 */
	public function extract_account_id_from_payload( $payload ) {
		if ( ! is_array( $payload ) ) {
			return '';
		}

		if ( ! empty( $payload['account'] ) ) {
			if ( is_string( $payload['account'] ) ) {
				return sanitize_text_field( $payload['account'] );
			}
			if ( is_array( $payload['account'] ) ) {
				$id = $payload['account']['id'] ?? $payload['account']['account'] ?? '';
				return $id ? sanitize_text_field( (string) $id ) : '';
			}
		}

		return '';
	}

	/* -------------------------------------------------------------------- *
	 * Products (catch-all provisioning for checkout)
	 * -------------------------------------------------------------------- */

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
	 * Normalize a product API response to a single product object.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error
	 */
	public function parse_product( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['products'][0] ) && is_array( $response['products'][0] ) ) {
			return $response['products'][0];
		}

		if ( isset( $response['product'] ) ) {
			return $response;
		}

		return new WP_Error(
			'vms_efpg_product_not_found',
			__( 'FastSpring did not return product details.', 'vms-elements-fastspring-payment-gateway' ),
			array( 'body' => $response )
		);
	}

	/**
	 * Whether a product path already exists in the catalog.
	 *
	 * @param string $product_path Product slug.
	 * @return bool
	 */
	public function product_exists( $product_path ) {
		$result = $this->get_product( $product_path );
		if ( is_wp_error( $result ) ) {
			return false;
		}
		$product = $this->parse_product( $result );
		return ! is_wp_error( $product );
	}

	/**
	 * Strip read-only or undocumented fields before POST /products.
	 *
	 * FastSpring returns fields such as visibility on GET /products/{path} but rejects
	 * them on create/update ("Field was not recognized").
	 *
	 * @param array $product Raw product payload.
	 * @return array Sanitized payload (empty when product path is missing).
	 */
	public function sanitize_product_upsert_payload( $product ) {
		if ( ! is_array( $product ) ) {
			return array();
		}

		$path = isset( $product['product'] ) ? sanitize_title( (string) $product['product'] ) : '';
		if ( '' === $path ) {
			return array();
		}

		$clean = array( 'product' => $path );

		if ( isset( $product['display'] ) && is_array( $product['display'] ) ) {
			$display = $this->sanitize_localized_strings( $product['display'] );
			if ( ! empty( $display ) ) {
				$clean['display'] = $display;
			}
		}

		if ( isset( $product['pricing'] ) && is_array( $product['pricing'] ) ) {
			$pricing = $this->sanitize_product_pricing( $product['pricing'] );
			if ( ! empty( $pricing ) ) {
				$clean['pricing'] = $pricing;
			}
		}

		if ( isset( $product['description'] ) && is_array( $product['description'] ) ) {
			$description = $this->sanitize_product_description( $product['description'] );
			if ( ! empty( $description ) ) {
				$clean['description'] = $description;
			}
		}

		if ( isset( $product['fulfillment'] ) && is_array( $product['fulfillment'] ) ) {
			$fulfillment = $this->sanitize_product_fulfillment( $product['fulfillment'] );
			if ( ! empty( $fulfillment ) ) {
				$clean['fulfillment'] = $fulfillment;
			}
		}

		if ( ! empty( $product['format'] ) ) {
			$format = (string) $product['format'];
			if ( in_array( $format, array( 'digital', 'physical', 'digital-and-physical' ), true ) ) {
				$clean['format'] = $format;
			}
		}

		if ( ! empty( $product['sku'] ) && is_scalar( $product['sku'] ) ) {
			$clean['sku'] = sanitize_text_field( (string) $product['sku'] );
		}

		return $clean;
	}

	/**
	 * Keep only locale => string pairs from a localized text object.
	 *
	 * @param array $values Localized strings.
	 * @return array
	 */
	private function sanitize_localized_strings( $values ) {
		$clean = array();
		foreach ( (array) $values as $locale => $text ) {
			if ( ! is_string( $locale ) || '' === $locale || ! is_scalar( $text ) ) {
				continue;
			}
			$text = sanitize_text_field( (string) $text );
			if ( '' !== $text ) {
				$clean[ $locale ] = $text;
			}
		}
		return $clean;
	}

	/**
	 * Sanitize pricing for POST /products.
	 *
	 * @param array $pricing Pricing object.
	 * @return array
	 */
	private function sanitize_product_pricing( $pricing ) {
		$allowed = array( 'price', 'quantityBehavior', 'quantityDefault' );

		$clean = array();
		foreach ( $allowed as $key ) {
			if ( ! array_key_exists( $key, $pricing ) ) {
				continue;
			}

			$value = $pricing[ $key ];
			switch ( $key ) {
				case 'price':
					if ( ! is_array( $value ) ) {
						break;
					}
					$prices = array();
					foreach ( $value as $currency => $amount ) {
						if ( ! is_string( $currency ) || '' === $currency || ! is_numeric( $amount ) ) {
							continue;
						}
						$prices[ strtoupper( $currency ) ] = (float) $amount;
					}
					if ( ! empty( $prices ) ) {
						$clean['price'] = $prices;
					}
					break;
				case 'quantityBehavior':
					$behavior = (string) $value;
					if ( in_array( $behavior, array( 'allow', 'lock', 'hide' ), true ) ) {
						$clean['quantityBehavior'] = $behavior;
					}
					break;
				case 'quantityDefault':
					$clean[ $key ] = (int) $value;
					break;
			}
		}

		return $clean;
	}

	/**
	 * Sanitize product description blocks for POST /products.
	 *
	 * @param array $description Description object.
	 * @return array
	 */
	private function sanitize_product_description( $description ) {
		$clean = array();
		foreach ( array( 'summary', 'action', 'full' ) as $block ) {
			if ( empty( $description[ $block ] ) || ! is_array( $description[ $block ] ) ) {
				continue;
			}
			$text = $this->sanitize_localized_strings( $description[ $block ] );
			if ( ! empty( $text ) ) {
				$clean[ $block ] = $text;
			}
		}
		return $clean;
	}

	/**
	 * Sanitize fulfillment instructions for POST /products.
	 *
	 * @param array $fulfillment Fulfillment object.
	 * @return array
	 */
	private function sanitize_product_fulfillment( $fulfillment ) {
		if ( empty( $fulfillment['instructions'] ) || ! is_array( $fulfillment['instructions'] ) ) {
			return array();
		}

		$instructions = $this->sanitize_localized_strings( $fulfillment['instructions'] );
		if ( empty( $instructions ) ) {
			return array();
		}

		return array(
			'instructions' => $instructions,
		);
	}

	/**
	 * Create or update the catch-all product used to charge WooCommerce totals.
	 *
	 * @param array $product Product payload (single product).
	 * @return array|WP_Error
	 */
	private function upsert_product( $product ) {
		$sanitized = $this->sanitize_product_upsert_payload( $product );
		if ( empty( $sanitized['product'] ) ) {
			return new WP_Error(
				'vms_efpg_invalid_product',
				__( 'No valid product payload to send.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		return $this->request( 'POST', '/products', array( 'products' => array( $sanitized ) ) );
	}

	/**
	 * Ensure a catch-all product exists so single_custom_price checkout works
	 * without the merchant manually creating anything in FastSpring.
	 *
	 * The price is irrelevant: each order overrides it via the session. We only
	 * need the product to exist in the catalog. Authenticated server-to-server
	 * session requests are allowed to override the price of any product.
	 *
	 * @param string $product_path Desired product path.
	 * @param string $display      Human display name.
	 * @return string|WP_Error Product path on success.
	 */
	public function ensure_catch_all_product( $product_path, $display = '' ) {
		$product_path = sanitize_title( $product_path );
		if ( '' === $product_path ) {
			return new WP_Error( 'vms_efpg_invalid_product_path', __( 'A valid product path is required.', 'vms-elements-fastspring-payment-gateway' ) );
		}

		if ( $this->product_exists( $product_path ) ) {
			return $product_path;
		}

		$display = '' !== $display ? $display : __( 'Order total', 'vms-elements-fastspring-payment-gateway' );

		$payload = array(
			'product' => $product_path,
			'display' => array( 'en' => $display ),
			'format'  => 'digital',
			'sku'     => $product_path,
			'pricing' => array(
				'quantityBehavior' => 'lock',
				'quantityDefault'  => 1,
				'price'            => array( 'USD' => 1.00 ),
			),
		);

		$result = $this->upsert_product( $payload );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $product_path;
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
	 * Normalize an order API response to a single order object.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error
	 */
	public function parse_order( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['orders'][0] ) && is_array( $response['orders'][0] ) ) {
			return $response['orders'][0];
		}

		if ( isset( $response['id'] ) || isset( $response['order'] ) ) {
			return $response;
		}

		return new WP_Error(
			'vms_efpg_order_not_found',
			__( 'FastSpring did not return order details.', 'vms-elements-fastspring-payment-gateway' ),
			array( 'body' => $response )
		);
	}

	/**
	 * Poll FastSpring until an order is marked completed.
	 *
	 * @param string $order_id FastSpring order ID.
	 * @param array  $args {
	 *     Optional poll tuning (REST confirm uses a short poll per request).
	 *
	 *     @type int|null $max_attempts Maximum API attempts. Null = default.
	 *     @type int|null $wait_ms      Milliseconds between attempts. Null = default.
	 * }
	 * @return array|WP_Error
	 */
	public function wait_for_completed_order( $order_id, $args = array() ) {
		$order_id = sanitize_text_field( (string) $order_id );
		if ( '' === $order_id ) {
			return new WP_Error(
				'missing_fs_order',
				__( 'FastSpring order id is required.', 'vms-elements-fastspring-payment-gateway' ),
				array( 'status' => 400 )
			);
		}

		$args = wp_parse_args(
			is_array( $args ) ? $args : array(),
			array(
				'max_attempts' => null,
				'wait_ms'      => null,
			)
		);

		if ( null === $args['max_attempts'] ) {
			$attempts = (int) apply_filters( 'vms_efpg_complete_payment_poll_attempts', 12 );
			if ( class_exists( 'VMS_EFPG_Webhook' ) && VMS_EFPG_Webhook::is_localhost_environment() ) {
				$attempts = (int) apply_filters( 'vms_efpg_complete_payment_poll_attempts_localhost', max( $attempts, 20 ) );
			}
		} else {
			$attempts = (int) $args['max_attempts'];
		}

		if ( null === $args['wait_ms'] ) {
			$wait_ms = (int) apply_filters( 'vms_efpg_complete_payment_poll_ms', 250 );
		} else {
			$wait_ms = (int) $args['wait_ms'];
		}

		$attempts = max( 1, $attempts );
		$wait_ms  = max( 50, $wait_ms );

		$last_order = null;
		$last_error = null;

		for ( $i = 0; $i < $attempts; $i++ ) {
			$response = $this->get_order( $order_id );
			if ( is_wp_error( $response ) ) {
				$last_error = $response;
				break;
			}

			$order = $this->parse_order( $response );
			if ( is_wp_error( $order ) ) {
				$last_error = $order;
				break;
			}

			$last_order = $order;
			if ( ! empty( $order['completed'] ) ) {
				return $order;
			}

			if ( $i < $attempts - 1 ) {
				usleep( $wait_ms * 1000 );
			}
		}

		if ( is_wp_error( $last_error ) ) {
			return $last_error;
		}

		return new WP_Error(
			'not_completed',
			__( 'FastSpring has not marked this order as completed yet. Please wait a moment and refresh.', 'vms-elements-fastspring-payment-gateway' ),
			array(
				'status' => 409,
				'body'   => $last_order,
			)
		);
	}

	/**
	 * Poll until invoice metadata is available on a completed order.
	 *
	 * @param array $order         Order payload.
	 * @param int   $max_attempts  Maximum refresh attempts.
	 * @return array
	 */
	public function ensure_order_invoice( $order, $max_attempts = 8 ) {
		if ( ! is_array( $order ) ) {
			return $order;
		}

		$attempts = max( 1, (int) apply_filters( 'vms_efpg_invoice_poll_attempts', $max_attempts ) );
		$wait_ms  = max( 50, (int) apply_filters( 'vms_efpg_invoice_poll_ms', 250 ) );

		for ( $i = 0; $i < $attempts; $i++ ) {
			$order = $this->enrich_order_invoice_payload( $order );
			$meta  = $this->extract_order_invoice_meta( $order );
			if ( $meta['invoice_url'] || $meta['fs_invoice_id'] ) {
				return $order;
			}

			if ( empty( $order['completed'] ) ) {
				return $order;
			}

			$fs_order_id = isset( $order['id'] ) ? (string) $order['id'] : '';
			if ( '' === $fs_order_id ) {
				return $order;
			}

			if ( $i < $attempts - 1 ) {
				usleep( $wait_ms * 1000 );
				$fresh = $this->parse_order( $this->get_order( $fs_order_id ) );
				if ( ! is_wp_error( $fresh ) ) {
					$order = $fresh;
				}
			}
		}

		return $order;
	}

	/**
	 * Extract order IDs from a search/list response.
	 *
	 * @param array $result Decoded list response.
	 * @return string[]
	 */
	public function extract_order_ids( $result ) {
		if ( ! is_array( $result ) || empty( $result['orders'] ) || ! is_array( $result['orders'] ) ) {
			return array();
		}

		$ids = array();
		foreach ( $result['orders'] as $order ) {
			if ( is_string( $order ) && '' !== $order ) {
				$ids[] = $order;
			} elseif ( is_array( $order ) && ! empty( $order['id'] ) ) {
				$ids[] = (string) $order['id'];
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * Hydrate order IDs into full order objects.
	 *
	 * @param string[] $ids Order IDs.
	 * @return array
	 */
	public function hydrate_orders( $ids ) {
		$orders = array();
		foreach ( (array) $ids as $id ) {
			$result = $this->get_order( $id );
			if ( is_wp_error( $result ) ) {
				continue;
			}
			$order = $this->parse_order( $result );
			if ( ! is_wp_error( $order ) ) {
				$orders[] = $order;
			}
		}
		return $orders;
	}

	/**
	 * List orders within a date range or filters.
	 *
	 * @param array $params Query parameters.
	 * @return array|WP_Error
	 */
	public function list_orders( $params = array() ) {
		$params = $this->filter_query_params(
			$params,
			array( 'begin', 'end', 'days', 'limit', 'page', 'products', 'rebill', 'returns', 'scope' )
		);

		return $this->request( 'GET', '/orders', $params );
	}

	/* -------------------------------------------------------------------- *
	 * Payload datetime parser (shared with data store)
	 * -------------------------------------------------------------------- */

	/**
	 * Parse a FastSpring date field group into a MySQL UTC datetime string.
	 *
	 * FastSpring returns millisecond timestamps in the primary field, with
	 * *InSeconds and *DisplayISO8601 companion fields per API docs.
	 *
	 * @param array    $payload         Payload array.
	 * @param string   $base_field      Base field name (e.g. nextChargeDate, begin).
	 * @param string[] $fallback_bases  Alternate base field names to try.
	 * @return string|null MySQL datetime (Y-m-d H:i:s) or null.
	 */
	public function parse_payload_datetime( $payload, $base_field, $fallback_bases = array() ) {
		if ( ! is_array( $payload ) ) {
			return null;
		}

		$bases = array_merge( array( $base_field ), (array) $fallback_bases );
		foreach ( $bases as $base ) {
			$parsed = $this->parse_payload_datetime_base( $payload, $base );
			if ( $parsed ) {
				return $parsed;
			}
		}

		return null;
	}

	/**
	 * Parse one FastSpring date field group.
	 *
	 * @param array  $payload    Payload array.
	 * @param string $base_field Base field name.
	 * @return string|null
	 */
	private function parse_payload_datetime_base( $payload, $base_field ) {
		$seconds_key = $base_field . 'InSeconds';
		if ( isset( $payload[ $seconds_key ] ) && is_numeric( $payload[ $seconds_key ] ) && (int) $payload[ $seconds_key ] > 0 ) {
			return gmdate( 'Y-m-d H:i:s', (int) $payload[ $seconds_key ] );
		}

		$iso_key = $base_field . 'DisplayISO8601';
		if ( ! empty( $payload[ $iso_key ] ) && is_string( $payload[ $iso_key ] ) ) {
			$timestamp = strtotime( $payload[ $iso_key ] . ' UTC' );
			if ( $timestamp ) {
				return gmdate( 'Y-m-d H:i:s', $timestamp );
			}
		}

		foreach ( array( $base_field, $base_field . 'Value' ) as $key ) {
			if ( ! isset( $payload[ $key ] ) || $payload[ $key ] === '' || $payload[ $key ] === null ) {
				continue;
			}

			if ( is_numeric( $payload[ $key ] ) ) {
				$timestamp = (int) $payload[ $key ];
				if ( $timestamp > 9999999999 ) {
					$timestamp = (int) floor( $timestamp / 1000 );
				}
				if ( $timestamp > 0 ) {
					return gmdate( 'Y-m-d H:i:s', $timestamp );
				}
			}
		}

		$display_key = $base_field . 'Display';
		if ( ! empty( $payload[ $display_key ] ) && is_string( $payload[ $display_key ] ) ) {
			$timestamp = strtotime( $payload[ $display_key ] );
			if ( $timestamp ) {
				return gmdate( 'Y-m-d H:i:s', $timestamp );
			}
		}

		return null;
	}

	/* -------------------------------------------------------------------- *
	 * Invoices (receipt links for stored orders)
	 * -------------------------------------------------------------------- */

	/**
	 * Extract invoice URL and ID from a FastSpring order payload.
	 *
	 * Checkout orders include `invoiceUrl` when FastSpring generates the receipt.
	 * There is no list-all-invoices API; order receipts are indexed from stored orders.
	 *
	 * @param array $order Order payload (webhook or GET /orders response).
	 * @return array{invoice_url:?string,fs_invoice_id:?string}
	 */
	public function extract_order_invoice_meta( $order ) {
		$empty = array(
			'invoice_url'   => null,
			'fs_invoice_id' => null,
		);

		if ( ! is_array( $order ) ) {
			return $empty;
		}

		$url = isset( $order['invoiceUrl'] ) ? trim( (string) $order['invoiceUrl'] ) : '';
		if ( '' === $url ) {
			return $empty;
		}

		if ( false !== strpos( $url, '/null/' ) ) {
			return $empty;
		}

		$invoice_id = null;
		if ( preg_match( '~#/invoice/([^/?#]+)~i', $url, $matches ) ) {
			$candidate = strtoupper( trim( $matches[1], '/' ) );
			if ( 'PDF' !== $candidate && preg_match( '/^[A-Z0-9]{10,}$/', $candidate ) ) {
				$invoice_id = $candidate;
			}
		}

		return array(
			'invoice_url'   => esc_url_raw( $url ),
			'fs_invoice_id' => $invoice_id,
		);
	}

	/**
	 * Fetch a completed order from the API when invoice metadata is missing locally.
	 *
	 * @param array $order Partial order payload.
	 * @return array Merged order payload.
	 */
	public function enrich_order_invoice_payload( $order ) {
		if ( ! is_array( $order ) ) {
			return $order;
		}

		$meta = $this->extract_order_invoice_meta( $order );
		if ( $meta['invoice_url'] || $meta['fs_invoice_id'] ) {
			return $order;
		}

		$fs_order_id = isset( $order['id'] ) ? (string) $order['id'] : '';
		if ( '' === $fs_order_id || empty( $order['completed'] ) ) {
			return $order;
		}

		$fresh = $this->parse_order( $this->get_order( $fs_order_id ) );
		if ( is_wp_error( $fresh ) || empty( $fresh['invoiceUrl'] ) ) {
			return $order;
		}

		$order['invoiceUrl'] = $fresh['invoiceUrl'];
		return $order;
	}

	/**
	 * Resend an order receipt invoice email to the customer.
	 *
	 * FastSpring does not expose a public API to resend branded receipt emails.
	 * This sends the invoice links from WordPress via wp_mail().
	 *
	 * @param string $fs_order_id FastSpring order ID.
	 * @param string $recipient   Optional override recipient email.
	 * @return string|WP_Error
	 */
	public function resend_order_invoice_email( $fs_order_id, $recipient = '' ) {
		$fs_order_id = sanitize_text_field( (string) $fs_order_id );
		if ( '' === $fs_order_id ) {
			return new WP_Error(
				'vms_efpg_invoice_resend',
				__( 'A FastSpring order ID is required to resend the invoice.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		$row = VMS_EFPG_Data_Store::get_order_by_fs_id( $fs_order_id );
		if ( ! $row ) {
			$fresh = $this->parse_order( $this->get_order( $fs_order_id ) );
			if ( is_wp_error( $fresh ) ) {
				return $fresh;
			}

			$fresh   = $this->enrich_order_invoice_payload( $fresh );
			$is_test = empty( $fresh['live'] );
			VMS_EFPG_Data_Store::upsert_order( $fresh, $is_test );
			$row = VMS_EFPG_Data_Store::get_order_by_fs_id( $fs_order_id );
		}

		if ( ! $row ) {
			return new WP_Error(
				'vms_efpg_invoice_resend',
				__( 'Could not load the order for invoice resend.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		$meta = VMS_EFPG_Data_Store::get_order_invoice_meta( $row );
		if ( empty( $meta['invoice_url'] ) && empty( $meta['fs_invoice_id'] ) ) {
			return new WP_Error(
				'vms_efpg_invoice_resend',
				__( 'This order does not have an invoice URL yet. Try again after the order is completed.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		$to = $recipient ? sanitize_email( $recipient ) : sanitize_email( (string) ( $row['email'] ?? '' ) );
		if ( ! $to || ! is_email( $to ) ) {
			return new WP_Error(
				'vms_efpg_invoice_resend',
				__( 'No valid customer email address is available for this order.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		$view_url = $meta['invoice_url'];
		$pdf_url  = '';
		if ( $view_url ) {
			$pdf_url = untrailingslashit( $view_url ) . '/pdf';
		}

		return $this->deliver_invoice_email(
			array(
				'to'            => $to,
				'customer_name' => (string) ( $row['customer_name'] ?? '' ),
				'order_id'      => $fs_order_id,
				'invoice_id'    => (string) ( $meta['fs_invoice_id'] ?? '' ),
				'currency'      => (string) ( $row['currency'] ?? '' ),
				'total'         => (float) ( $row['total'] ?? 0 ),
				'view_url'      => $view_url,
				'pdf_url'       => $pdf_url,
				'pay_url'       => '',
			)
		);
	}

	/**
	 * Send an invoice notification email from WordPress.
	 *
	 * @param array $args Email args.
	 * @return string|WP_Error
	 */
	private function deliver_invoice_email( $args ) {
		$to = sanitize_email( (string) ( $args['to'] ?? '' ) );
		if ( ! $to || ! is_email( $to ) ) {
			return new WP_Error(
				'vms_efpg_invoice_resend',
				__( 'No valid recipient email address was provided.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		$reference = ! empty( $args['order_id'] ) ? (string) $args['order_id'] : (string) ( $args['invoice_id'] ?? '' );
		$subject   = $reference
			? sprintf(
				/* translators: %s: order or invoice reference */
				__( 'Your invoice for %s', 'vms-elements-fastspring-payment-gateway' ),
				$reference
			)
			: __( 'Your invoice', 'vms-elements-fastspring-payment-gateway' );

		$greeting = ! empty( $args['customer_name'] )
			? sprintf(
				/* translators: %s: customer name */
				__( 'Hello %s,', 'vms-elements-fastspring-payment-gateway' ),
				$args['customer_name']
			)
			: __( 'Hello,', 'vms-elements-fastspring-payment-gateway' );

		$total_line = '';
		if ( ! empty( $args['currency'] ) || ! empty( $args['total'] ) ) {
			$total_line = sprintf(
				/* translators: 1: currency code, 2: formatted total */
				__( 'Total: %1$s %2$s', 'vms-elements-fastspring-payment-gateway' ),
				(string) ( $args['currency'] ?? '' ),
				number_format_i18n( (float) ( $args['total'] ?? 0 ), 2 )
			);
		}

		$links = array();
		if ( ! empty( $args['view_url'] ) ) {
			$links[] = sprintf(
				'<li><a href="%1$s">%2$s</a></li>',
				esc_url( (string) $args['view_url'] ),
				esc_html__( 'View invoice', 'vms-elements-fastspring-payment-gateway' )
			);
		}
		if ( ! empty( $args['pdf_url'] ) ) {
			$links[] = sprintf(
				'<li><a href="%1$s">%2$s</a></li>',
				esc_url( (string) $args['pdf_url'] ),
				esc_html__( 'Download PDF', 'vms-elements-fastspring-payment-gateway' )
			);
		}
		if ( ! empty( $args['pay_url'] ) ) {
			$links[] = sprintf(
				'<li><a href="%1$s">%2$s</a></li>',
				esc_url( (string) $args['pay_url'] ),
				esc_html__( 'Pay invoice', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		if ( empty( $links ) ) {
			return new WP_Error(
				'vms_efpg_invoice_resend',
				__( 'No invoice links are available to include in the email.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		$body  = '<p>' . esc_html( $greeting ) . '</p>';
		$body .= '<p>' . esc_html__( 'Here are the links to your invoice:', 'vms-elements-fastspring-payment-gateway' ) . '</p>';
		if ( $total_line ) {
			$body .= '<p>' . esc_html( $total_line ) . '</p>';
		}
		$body .= '<ul>' . implode( '', $links ) . '</ul>';
		$body .= '<p>' . esc_html__( 'If you did not request this message, you can ignore it.', 'vms-elements-fastspring-payment-gateway' ) . '</p>';

		$sent = wp_mail(
			$to,
			$subject,
			$body,
			array( 'Content-Type: text/html; charset=UTF-8' )
		);

		if ( ! $sent ) {
			VMS_EFPG_Logger::log(
				'Invoice resend email failed.',
				'error',
				'invoice',
				array(
					'to'         => $to,
					'order_id'   => $args['order_id'] ?? '',
					'invoice_id' => $args['invoice_id'] ?? '',
				)
			);

			return new WP_Error(
				'vms_efpg_invoice_resend',
				__( 'WordPress could not send the invoice email. Check your site mail configuration.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		VMS_EFPG_Logger::log(
			'Invoice email resent.',
			'info',
			'invoice',
			array(
				'to'         => $to,
				'order_id'   => $args['order_id'] ?? '',
				'invoice_id' => $args['invoice_id'] ?? '',
			)
		);

		return sprintf(
			/* translators: %s: recipient email address */
			__( 'Invoice email sent to %s.', 'vms-elements-fastspring-payment-gateway' ),
			$to
		);
	}

	/* -------------------------------------------------------------------- *
	 * Sessions (legacy v1 create session for popup/classic checkout)
	 * -------------------------------------------------------------------- */

	/**
	 * Parse a Sessions V1 create response.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error
	 */
	public function parse_session_v1( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['id'] ) && is_string( $response['id'] ) ) {
			return $response;
		}

		return new WP_Error(
			'vms_efpg_session_not_found',
			__( 'FastSpring did not return session details.', 'vms-elements-fastspring-payment-gateway' ),
			array( 'body' => $response )
		);
	}

	/**
	 * Create a legacy Sessions v1 order session.
	 *
	 * @param array $payload Session payload.
	 * @return array|WP_Error
	 */
	public function create_session( $payload ) {
		$result = $this->request( 'POST', '/sessions', $payload );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_session_v1( $result );
	}

	/* -------------------------------------------------------------------- *
	 * Webhooks (permissions inspection only)
	 * -------------------------------------------------------------------- */

	/**
	 * Normalize a webhook list response.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error
	 */
	public function parse_webhooks_list( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['result'] ) && 'error' === $response['result'] ) {
			return new WP_Error(
				'vms_efpg_webhooks_error',
				$this->extract_error_message( $response, '', 200 ),
				array( 'status' => 200, 'body' => $response )
			);
		}

		if ( ! empty( $response['webhooks'] ) && is_array( $response['webhooks'] ) ) {
			return $response['webhooks'];
		}
		if ( isset( $response[0] ) && is_array( $response[0] ) ) {
			return $response;
		}

		return array();
	}

	/**
	 * Normalize event names from a webhook endpoint definition.
	 *
	 * @param mixed $events Raw events value from FastSpring.
	 * @return string[]
	 */
	public function parse_webhook_events( $events ) {
		if ( null === $events || false === $events || array() === $events || '' === $events ) {
			return array( '*' );
		}

		if ( is_string( $events ) ) {
			$trimmed = trim( $events );
			if ( '' === $trimmed ) {
				return array( '*' );
			}
			if ( in_array( strtolower( $trimmed ), array( '*', 'all', 'all events', 'all_events' ), true ) ) {
				return array( '*' );
			}
			return array_values(
				array_unique(
					array_filter(
						array_map( 'trim', preg_split( '/\s*,\s*/', $trimmed ) )
					)
				)
			);
		}

		if ( ! is_array( $events ) ) {
			return array();
		}

		$parsed = array();
		foreach ( $events as $event ) {
			if ( is_string( $event ) ) {
				$parsed[] = trim( $event );
				continue;
			}
			if ( ! is_array( $event ) ) {
				continue;
			}
			if ( ! empty( $event['type'] ) ) {
				$parsed[] = trim( (string) $event['type'] );
			} elseif ( ! empty( $event['name'] ) ) {
				$parsed[] = trim( (string) $event['name'] );
			} elseif ( ! empty( $event['event'] ) ) {
				$parsed[] = trim( (string) $event['event'] );
			}
		}

		return array_values( array_unique( array_filter( $parsed ) ) );
	}

	/**
	 * Compare two webhook receiver URLs.
	 *
	 * @param string $left  First URL.
	 * @param string $right Second URL.
	 * @return bool
	 */
	public function webhook_urls_match( $left, $right ) {
		return $this->normalize_webhook_url( $left ) === $this->normalize_webhook_url( $right );
	}

	/**
	 * Normalize a webhook URL for comparison.
	 *
	 * @param string $url Raw URL.
	 * @return string
	 */
	public function normalize_webhook_url( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return '';
		}

		$parts = wp_parse_url( $url );
		if ( empty( $parts['host'] ) ) {
			return strtolower( untrailingslashit( $url ) );
		}

		$scheme = isset( $parts['scheme'] ) ? strtolower( $parts['scheme'] ) : 'https';
		$host   = strtolower( $parts['host'] );
		$port   = isset( $parts['port'] ) ? ':' . (int) $parts['port'] : '';
		$path   = isset( $parts['path'] ) ? untrailingslashit( $parts['path'] ) : '';
		$query  = isset( $parts['query'] ) ? '?' . $parts['query'] : '';

		return $scheme . '://' . $host . $port . $path . $query;
	}

	/**
	 * Extract subscribed event types for a receiver URL from GET /webhooks.
	 *
	 * @param array|WP_Error $response     Webhooks API response.
	 * @param string         $receiver_url Plugin webhook URL.
	 * @return string[]|null Enabled event types, ['*'] when all events are selected, null when no URL match.
	 */
	public function extract_webhook_event_permissions( $response, $receiver_url ) {
		$hooks = $this->parse_webhooks_list( $response );
		if ( is_wp_error( $hooks ) ) {
			return null;
		}

		$events  = array();
		$matched = false;

		foreach ( $hooks as $hook ) {
			if ( ! is_array( $hook ) ) {
				continue;
			}

			$endpoints = array();
			if ( ! empty( $hook['endpoints'] ) && is_array( $hook['endpoints'] ) ) {
				$endpoints = $hook['endpoints'];
			} elseif ( ! empty( $hook['urls'] ) && is_array( $hook['urls'] ) ) {
				$endpoints = $hook['urls'];
			} else {
				$endpoints = array( $hook );
			}

			foreach ( $endpoints as $endpoint ) {
				if ( ! is_array( $endpoint ) ) {
					continue;
				}

				$url = $endpoint['url'] ?? ( $endpoint['endpoint'] ?? ( $hook['url'] ?? '' ) );
				if ( ! $url || ! $this->webhook_urls_match( $url, $receiver_url ) ) {
					continue;
				}

				$matched         = true;
				$endpoint_events = $endpoint['events'] ?? ( $hook['events'] ?? null );
				$parsed          = $this->parse_webhook_events( $endpoint_events );
				if ( in_array( '*', $parsed, true ) ) {
					return array( '*' );
				}
				$events = array_merge( $events, $parsed );
			}
		}

		if ( ! $matched ) {
			return null;
		}

		return array_values( array_unique( $events ) );
	}

	/**
	 * List configured webhooks.
	 *
	 * @return array|WP_Error
	 */
	public function get_webhooks() {
		return $this->request( 'GET', '/webhooks' );
	}
}
