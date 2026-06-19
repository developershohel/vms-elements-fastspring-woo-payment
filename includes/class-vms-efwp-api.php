<?php
/**
 * FastSpring REST API client.
 *
 * Documentation: https://developer.fastspring.com/reference/getting-started-with-the-api
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_API.
 *
 * Authenticates against api.fastspring.com using HTTP Basic Auth with the
 * API username/password configured in the FastSpring dashboard.
 */
class VMS_EFWP_API {

	const BASE_URL = 'https://api.fastspring.com';

	/**
	 * Settings.
	 *
	 * @var VMS_EFWP_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param VMS_EFWP_Settings $settings Settings handler.
	 */
	public function __construct( VMS_EFWP_Settings $settings ) {
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
			return new WP_Error( 'vms_efwp_missing_credentials', __( 'FastSpring API credentials are missing for the active mode.', 'vms-elements-fastspring-woo-payment' ) );
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
	 * Paginate an in-memory list for admin screens when the API has no page/limit support.
	 *
	 * @param array $items    Full item list.
	 * @param int   $page     Current page (1-based).
	 * @param int   $per_page Items per page.
	 * @return array Keys: items, total, page, per_page, total_pages, has_next.
	 */
	public static function paginate_items( $items, $page = 1, $per_page = 50 ) {
		$items    = array_values( (array) $items );
		$page     = max( 1, (int) $page );
		$per_page = max( 1, (int) $per_page );
		$total    = count( $items );
		$offset   = ( $page - 1 ) * $per_page;

		return array(
			'items'       => array_slice( $items, $offset, $per_page ),
			'total'       => $total,
			'page'        => $page,
			'per_page'    => $per_page,
			'total_pages' => (int) max( 1, ceil( $total / $per_page ) ),
			'has_next'    => ( $offset + $per_page ) < $total,
		);
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
					'User-Agent'    => 'VMS-Elements-Fastspring-Woo-Payment/' . VMS_EFWP_VERSION . '; ' . home_url(),
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
			VMS_EFWP_Logger::error( 'API transport error: ' . $response->get_error_message(), 'api', array( 'url' => $url ) );
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( $code >= 400 ) {
			$data    = json_decode( $body, true );
			$message = $this->extract_error_message( $data, $body, $code );
			VMS_EFWP_Logger::error(
				sprintf( 'API %s %s -> %d: %s', $method, $path, $code, $message ),
				'api',
				array( 'method' => $method, 'path' => $path, 'body' => $body )
			);
			return new WP_Error( 'vms_efwp_api_' . $code, $message, array( 'status' => $code, 'body' => $data ) );
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
			VMS_EFWP_Logger::error(
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
				'vms_efwp_api_error',
				$this->extract_error_message( $data, '', 200 ),
				array( 'status' => 200, 'body' => $data )
			);
		}

		foreach ( array( 'accounts', 'coupons', 'subscriptions', 'products', 'orders', 'returns' ) as $bucket ) {
			if ( empty( $data[ $bucket ] ) || ! is_array( $data[ $bucket ] ) ) {
				continue;
			}
			foreach ( $data[ $bucket ] as $item ) {
				if ( is_array( $item ) && isset( $item['result'] ) && 'error' === $item['result'] ) {
					return new WP_Error(
						'vms_efwp_api_error',
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
			} elseif ( ! empty( $data['coupon'] ) && is_scalar( $data['coupon'] ) ) {
				$prefix = (string) $data['coupon'] . ': ';
			}

			if ( isset( $data['error'] ) ) {
				$parts[] = $prefix . ( is_scalar( $data['error'] ) ? (string) $data['error'] : wp_json_encode( $data['error'] ) );
			} elseif ( isset( $data['message'] ) && is_scalar( $data['message'] ) ) {
				$parts[] = (string) $data['message'];
			} else {
				// Flat field => message maps (common for /sessions validation).
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
				__( 'HTTP %d with no error detail returned by FastSpring.', 'vms-elements-fastspring-woo-payment' ),
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
	 * Accounts
	 * -------------------------------------------------------------------- */

	/**
	 * List/search accounts.
	 *
	 * @param array $params Optional query parameters (email, page, limit).
	 * @return array|WP_Error
	 */
	public function get_accounts( $params = array() ) {
		$params = $this->filter_query_params(
			$params,
			array( 'email', 'begin', 'end', 'days', 'products', 'subscriptions', 'refunds', 'limit', 'page' )
		);

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

	/**
	 * Extract account IDs from a GET /accounts list response.
	 *
	 * FastSpring returns an array of account ID strings unless a row is already
	 * a full account object (e.g. from a detail lookup).
	 *
	 * @param array $result Decoded list response.
	 * @return string[]
	 */
	public function extract_account_ids( $result ) {
		if ( ! is_array( $result ) || empty( $result['accounts'] ) || ! is_array( $result['accounts'] ) ) {
			return array();
		}

		$ids = array();
		foreach ( $result['accounts'] as $row ) {
			if ( is_string( $row ) && '' !== $row ) {
				$ids[] = $row;
				continue;
			}
			if ( is_array( $row ) ) {
				$id = $row['id'] ?? $row['account'] ?? '';
				if ( is_string( $id ) && '' !== $id ) {
					$ids[] = $id;
				}
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * Fetch full account objects for a list of account IDs.
	 *
	 * @param string[] $account_ids Account IDs.
	 * @return array[] Account detail arrays (errors are skipped).
	 */
	public function hydrate_accounts( $account_ids ) {
		$accounts = array();
		foreach ( (array) $account_ids as $account_id ) {
			$detail = $this->get_account( $account_id );
			if ( is_wp_error( $detail ) ) {
				VMS_EFWP_Logger::warning(
					'Could not load FastSpring account: ' . $detail->get_error_message(),
					'api',
					array( 'account_id' => $account_id )
				);
				continue;
			}
			$accounts[] = $detail;
		}

		return $accounts;
	}

	/**
	 * Parse the authenticated account management portal URL.
	 *
	 * @param array|WP_Error $response Response from get_account_management_url().
	 * @return string|WP_Error Portal URL.
	 */
	public function parse_account_management_url( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['accounts'][0]['url'] ) && is_string( $response['accounts'][0]['url'] ) ) {
			if ( isset( $response['accounts'][0]['result'] ) && 'error' === $response['accounts'][0]['result'] ) {
				return new WP_Error(
					'vms_efwp_account_portal_error',
					$this->extract_error_message( $response['accounts'][0], '', 200 )
				);
			}
			return $response['accounts'][0]['url'];
		}

		if ( isset( $response['url'] ) && is_string( $response['url'] ) ) {
			return $response['url'];
		}

		return new WP_Error(
			'vms_efwp_account_portal_url_missing',
			__( 'FastSpring did not return an account management URL.', 'vms-elements-fastspring-woo-payment' )
		);
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
		unset( $params );
		return $this->request( 'GET', '/coupons' );
	}

	/**
	 * Get a single coupon.
	 *
	 * @param string $coupon_id Coupon id.
	 * @param array  $params    Optional query args (e.g. expand).
	 * @return array|WP_Error
	 */
	public function get_coupon( $coupon_id, $params = array() ) {
		return $this->request( 'GET', '/coupons/' . rawurlencode( $coupon_id ), $params );
	}

	/**
	 * List all codes for a coupon.
	 *
	 * @param string $coupon_id Coupon path.
	 * @return array|WP_Error
	 */
	public function get_coupon_codes( $coupon_id ) {
		return $this->request( 'GET', '/coupons/' . rawurlencode( $coupon_id ) . '/codes' );
	}

	/**
	 * Add coupon codes to an existing coupon (additive).
	 *
	 * Per FastSpring docs this is POST /coupons/{coupon_id} with a codes array.
	 *
	 * @param string       $coupon_id Coupon path.
	 * @param string|array $codes     One code or list of codes.
	 * @return array|WP_Error
	 */
	public function add_coupon_codes( $coupon_id, $codes ) {
		$codes = array_values(
			array_filter(
				array_map(
					static function ( $code ) {
						return is_string( $code ) ? trim( $code ) : '';
					},
					(array) $codes
				)
			)
		);

		return $this->request(
			'POST',
			'/coupons/' . rawurlencode( $coupon_id ),
			array( 'codes' => $codes )
		);
	}

	/**
	 * Back-compat alias for add_coupon_codes().
	 *
	 * @param string $coupon_id Coupon id.
	 * @param array  $payload   Must contain a `codes` array.
	 * @return array|WP_Error
	 */
	public function generate_coupon_codes( $coupon_id, $payload = array() ) {
		$codes = isset( $payload['codes'] ) ? $payload['codes'] : $payload;
		return $this->add_coupon_codes( $coupon_id, $codes );
	}

	/**
	 * Create or update a coupon (upsert).
	 *
	 * @param array $payload Coupon definition.
	 * @return array|WP_Error
	 */
	public function create_coupon( $payload ) {
		return $this->request( 'POST', '/coupons', $payload );
	}

	/**
	 * Update coupon settings without replacing checkout codes.
	 *
	 * @param string $coupon_id Coupon path.
	 * @param array  $payload   Updates (codes are stripped to preserve existing codes).
	 * @return array|WP_Error
	 */
	public function update_coupon( $coupon_id, $payload ) {
		$payload['coupon'] = $coupon_id;
		unset( $payload['codes'] );
		return $this->create_coupon( $payload );
	}

	/**
	 * Delete all codes for a coupon.
	 *
	 * @param string $coupon_id Coupon path.
	 * @return array|WP_Error
	 */
	public function delete_coupon_codes( $coupon_id ) {
		return $this->request( 'DELETE', '/coupons/' . rawurlencode( $coupon_id ) . '/codes' );
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

	/**
	 * Extract coupon path identifiers from a GET /coupons list response.
	 *
	 * @param array $result Decoded list response.
	 * @return string[]
	 */
	public function extract_coupon_paths( $result ) {
		if ( ! is_array( $result ) || empty( $result['coupons'] ) || ! is_array( $result['coupons'] ) ) {
			return array();
		}

		$paths = array();
		foreach ( $result['coupons'] as $row ) {
			if ( is_string( $row ) && '' !== $row ) {
				$paths[] = $row;
				continue;
			}
			if ( is_array( $row ) ) {
				$path = $row['coupon'] ?? $row['id'] ?? '';
				if ( is_string( $path ) && '' !== $path ) {
					$paths[] = $path;
				}
			}
		}

		return array_values( array_unique( $paths ) );
	}

	/**
	 * Fetch full coupon objects for path identifiers.
	 *
	 * @param string[] $coupon_paths Coupon paths.
	 * @return array[]
	 */
	public function hydrate_coupons( $coupon_paths ) {
		$coupons = array();
		foreach ( (array) $coupon_paths as $path ) {
			$detail = $this->get_coupon( $path );
			if ( is_wp_error( $detail ) ) {
				VMS_EFWP_Logger::warning(
					'Could not load FastSpring coupon: ' . $detail->get_error_message(),
					'api',
					array( 'coupon' => $path )
				);
				continue;
			}
			$coupons[] = $detail;
		}

		return $coupons;
	}

	/* -------------------------------------------------------------------- *
	 * Products
	 * -------------------------------------------------------------------- */

	/**
	 * List all product paths.
	 *
	 * FastSpring returns every product path in one response. Pagination is not supported.
	 *
	 * @param array $params Ignored — kept for backward compatibility.
	 * @return array|WP_Error
	 */
	public function list_products( $params = array() ) {
		unset( $params );
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
		if ( 1 === count( $paths ) ) {
			return $this->get_product( $paths[0] );
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
			'vms_efwp_product_not_found',
			__( 'FastSpring did not return product details.', 'vms-elements-fastspring-woo-payment' ),
			array( 'body' => $response )
		);
	}

	/**
	 * Normalize a product API response to a list of product objects.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array
	 */
	public function parse_products( $response ) {
		if ( is_wp_error( $response ) || ! is_array( $response ) ) {
			return array();
		}

		if ( ! empty( $response['products'] ) && is_array( $response['products'] ) ) {
			return array_values(
				array_filter(
					$response['products'],
					static function ( $row ) {
						return is_array( $row ) && ! empty( $row['product'] );
					}
				)
			);
		}

		if ( ! empty( $response['product'] ) ) {
			return array( $response );
		}

		return array();
	}

	/**
	 * Extract product paths from a list response.
	 *
	 * @param array $result Decoded list response.
	 * @return string[]
	 */
	public function extract_product_paths( $result ) {
		if ( ! is_array( $result ) || empty( $result['products'] ) || ! is_array( $result['products'] ) ) {
			return array();
		}

		$paths = array();
		foreach ( $result['products'] as $path ) {
			if ( is_string( $path ) && '' !== $path ) {
				$paths[] = $path;
			} elseif ( is_array( $path ) && ! empty( $path['product'] ) ) {
				$paths[] = (string) $path['product'];
			}
		}

		return array_values( array_unique( $paths ) );
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

		if ( isset( $product['attributes'] ) && is_array( $product['attributes'] ) ) {
			$attributes = array();
			foreach ( $product['attributes'] as $key => $value ) {
				if ( ! is_string( $key ) || '' === $key || ! is_scalar( $value ) ) {
					continue;
				}
				$attributes[ $key ] = (string) $value;
			}
			if ( ! empty( $attributes ) ) {
				$clean['attributes'] = $attributes;
			}
		}

		if ( ! empty( $product['image'] ) && is_string( $product['image'] ) ) {
			$clean['image'] = esc_url_raw( $product['image'] );
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

		if ( isset( $product['badge'] ) && is_array( $product['badge'] ) ) {
			$badge = $this->sanitize_localized_strings( $product['badge'] );
			if ( ! empty( $badge ) ) {
				$clean['badge'] = $badge;
			}
		}

		if ( array_key_exists( 'rank', $product ) ) {
			$clean['rank'] = max( 0, (int) $product['rank'] );
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
		$allowed = array(
			'price',
			'trial',
			'interval',
			'intervalLength',
			'quantityBehavior',
			'quantityDefault',
			'paymentCollected',
			'paidTrial',
			'trialPrice',
			'quantityDiscounts',
			'discountReason',
			'discountDuration',
			'reminderNotification',
			'overdueNotification',
			'cancellation',
		);

		$clean = array();
		foreach ( $allowed as $key ) {
			if ( ! array_key_exists( $key, $pricing ) ) {
				continue;
			}

			$value = $pricing[ $key ];
			switch ( $key ) {
				case 'price':
				case 'trialPrice':
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
						$clean[ $key ] = $prices;
					}
					break;
				case 'interval':
					$interval = (string) $value;
					if ( in_array( $interval, array( 'week', 'month', 'year' ), true ) ) {
						$clean['interval'] = $interval;
					}
					break;
				case 'quantityBehavior':
					$behavior = (string) $value;
					if ( in_array( $behavior, array( 'allow', 'lock', 'hide' ), true ) ) {
						$clean['quantityBehavior'] = $behavior;
					}
					break;
				case 'paymentCollected':
				case 'paidTrial':
					$clean[ $key ] = (bool) $value;
					break;
				case 'trial':
				case 'intervalLength':
				case 'quantityDefault':
				case 'discountDuration':
					$clean[ $key ] = (int) $value;
					break;
				case 'discountReason':
					if ( is_array( $value ) ) {
						$reason = $this->sanitize_localized_strings( $value );
						if ( ! empty( $reason ) ) {
							$clean['discountReason'] = $reason;
						}
					}
					break;
				case 'quantityDiscounts':
					if ( is_array( $value ) ) {
						$clean['quantityDiscounts'] = $value;
					}
					break;
				case 'reminderNotification':
				case 'overdueNotification':
				case 'cancellation':
					if ( is_array( $value ) ) {
						$clean[ $key ] = $value;
					}
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
	 * Create or update one or many products.
	 *
	 * @param array $products Products array (each indexed by product path).
	 * @return array|WP_Error
	 */
	public function upsert_products( $products ) {
		$clean = array();
		foreach ( (array) $products as $product ) {
			$sanitized = $this->sanitize_product_upsert_payload( $product );
			if ( ! empty( $sanitized['product'] ) ) {
				$clean[] = $sanitized;
			}
		}

		if ( empty( $clean ) ) {
			return new WP_Error(
				'vms_efwp_invalid_product',
				__( 'No valid product payload to send.', 'vms-elements-fastspring-woo-payment' )
			);
		}

		return $this->request( 'POST', '/products', array( 'products' => $clean ) );
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
			return new WP_Error( 'vms_efwp_invalid_product_path', __( 'A valid product path is required.', 'vms-elements-fastspring-woo-payment' ) );
		}

		if ( $this->product_exists( $product_path ) ) {
			return $product_path;
		}

		$display = '' !== $display ? $display : __( 'Order total', 'vms-elements-fastspring-woo-payment' );

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

	/**
	 * List localized prices for all products.
	 *
	 * @param array $params Query (country, currency, page, limit).
	 * @return array|WP_Error
	 */
	public function list_product_prices( $params = array() ) {
		$params = $this->filter_query_params( $params, array( 'country', 'currency', 'page', 'limit' ) );

		return $this->request( 'GET', '/products/price', $params );
	}

	/**
	 * Retrieve offers configured for a product.
	 *
	 * @param string $product_path Product path.
	 * @param array  $params       Optional type filter.
	 * @return array|WP_Error
	 */
	public function get_product_offers( $product_path, $params = array() ) {
		return $this->request( 'GET', '/products/offers/' . rawurlencode( $product_path ), $params );
	}

	/**
	 * Create or update offers for a product.
	 *
	 * @param string $product_path Product path.
	 * @param array  $offers       Offers array.
	 * @return array|WP_Error
	 */
	public function upsert_product_offers( $product_path, $offers ) {
		return $this->request(
			'POST',
			'/products/offers/' . rawurlencode( $product_path ),
			array(
				'products' => array(
					array(
						'product' => $product_path,
						'offers'  => array_values( $offers ),
					),
				),
			)
		);
	}

	/**
	 * Parse offers from a get_product_offers() response.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array
	 */
	public function parse_product_offers( $response ) {
		$product = $this->parse_product( $response );
		if ( is_wp_error( $product ) ) {
			return array();
		}
		return isset( $product['offers'] ) && is_array( $product['offers'] ) ? $product['offers'] : array();
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
			'vms_efwp_order_not_found',
			__( 'FastSpring did not return order details.', 'vms-elements-fastspring-woo-payment' ),
			array( 'body' => $response )
		);
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

	/**
	 * Search orders.
	 *
	 * @param array $params Query parameters.
	 * @return array|WP_Error
	 */
	public function search_orders( $params = array() ) {
		return $this->list_orders( $params );
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
	 * List or search subscriptions.
	 *
	 * Without filters (or only begin/end), returns subscription IDs.
	 * With other filters, returns full subscription objects.
	 *
	 * @param array $params Query parameters.
	 * @return array|WP_Error
	 */
	public function list_subscriptions( $params = array() ) {
		$params = $this->filter_query_params(
			$params,
			array( 'accountId', 'begin', 'end', 'event', 'products', 'scope', 'status' )
		);

		return $this->request( 'GET', '/subscriptions', $params );
	}

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
	 * Normalize a subscription API response to a single subscription object.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error
	 */
	public function parse_subscription( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['subscriptions'][0] ) && is_array( $response['subscriptions'][0] ) ) {
			return $response['subscriptions'][0];
		}

		if ( isset( $response['id'] ) || isset( $response['subscription'] ) ) {
			return $response;
		}

		return new WP_Error(
			'vms_efwp_subscription_not_found',
			__( 'FastSpring did not return subscription details.', 'vms-elements-fastspring-woo-payment' ),
			array( 'body' => $response )
		);
	}

	/**
	 * Normalize a subscription API response to a list of subscription objects.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array
	 */
	public function parse_subscriptions( $response ) {
		if ( is_wp_error( $response ) || ! is_array( $response ) ) {
			return array();
		}

		if ( ! empty( $response['subscriptions'] ) && is_array( $response['subscriptions'] ) ) {
			$subscriptions = array();
			foreach ( $response['subscriptions'] as $subscription ) {
				if ( is_array( $subscription ) && ( ! empty( $subscription['id'] ) || ! empty( $subscription['subscription'] ) ) ) {
					$subscriptions[] = $subscription;
				}
			}
			if ( ! empty( $subscriptions ) ) {
				return $subscriptions;
			}
		}

		if ( isset( $response['id'] ) || isset( $response['subscription'] ) ) {
			return array( $response );
		}

		return array();
	}

	/**
	 * Extract subscription IDs from a list/search response.
	 *
	 * @param array $result Decoded list response.
	 * @return string[]
	 */
	public function extract_subscription_ids( $result ) {
		if ( ! is_array( $result ) || empty( $result['subscriptions'] ) || ! is_array( $result['subscriptions'] ) ) {
			return array();
		}

		$ids = array();
		foreach ( $result['subscriptions'] as $subscription ) {
			if ( is_string( $subscription ) && '' !== $subscription ) {
				$ids[] = $subscription;
			} elseif ( is_array( $subscription ) ) {
				$id = $subscription['id'] ?? $subscription['subscription'] ?? '';
				if ( $id ) {
					$ids[] = (string) $id;
				}
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * Hydrate subscription IDs into full subscription objects.
	 *
	 * @param string[] $ids Subscription IDs.
	 * @return array
	 */
	public function hydrate_subscriptions( $ids ) {
		$ids = array_values( array_filter( (array) $ids ) );
		if ( empty( $ids ) ) {
			return array();
		}

		$result = $this->get_subscription( $ids );
		if ( is_wp_error( $result ) ) {
			return array();
		}

		return $this->parse_subscriptions( $result );
	}

	/**
	 * Extract a data job id from a report/job response.
	 *
	 * @param array $response API response.
	 * @return string
	 */
	public function extract_data_job_id( $response ) {
		if ( ! is_array( $response ) ) {
			return '';
		}

		if ( 'async' === ( $response['mode'] ?? '' ) && ! empty( $response['job']['id'] ) ) {
			return (string) $response['job']['id'];
		}

		if ( ! empty( $response['id'] ) && is_scalar( $response['id'] ) ) {
			return (string) $response['id'];
		}

		foreach ( array( 'requestId', 'request_id' ) as $key ) {
			if ( ! empty( $response[ $key ] ) && is_scalar( $response[ $key ] ) ) {
				return (string) $response[ $key ];
			}
		}

		return '';
	}

	/**
	 * Extract a report request id from a create_report() response.
	 *
	 * @param array $response API response.
	 * @return string
	 */
	public function extract_report_request_id( $response ) {
		return $this->extract_data_job_id( $response );
	}

	/**
	 * Whether a data job response indicates the report is ready to download.
	 *
	 * @param array|WP_Error $job Job response.
	 * @return bool
	 */
	public function is_data_job_ready( $job ) {
		if ( is_wp_error( $job ) || ! is_array( $job ) ) {
			return false;
		}

		$value = strtoupper( (string) ( $job['status'] ?? $job['state'] ?? '' ) );
		return in_array( $value, array( 'COMPLETE', 'COMPLETED', 'READY', 'DONE' ), true );
	}

	/**
	 * Whether a report status response indicates the report is ready.
	 *
	 * @param array|WP_Error $status Status response.
	 * @return bool
	 */
	public function is_report_ready( $status ) {
		return $this->is_data_job_ready( $status );
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
	 * Parse subscription entries (charge history) from an API response.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error
	 */
	public function parse_subscription_entries( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['entries'] ) && is_array( $response['entries'] ) ) {
			return $response['entries'];
		}

		if ( isset( $response[0] ) && is_array( $response[0] ) ) {
			return $response;
		}

		return array();
	}

	/**
	 * Get subscription plan change history.
	 *
	 * @param string $subscription_id ID.
	 * @param array  $params          Optional scope/order query args.
	 * @return array|WP_Error
	 */
	public function get_subscription_history( $subscription_id, $params = array() ) {
		return $this->request( 'GET', '/subscriptions/' . rawurlencode( $subscription_id ) . '/history', $params );
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
	 * Convenience: update a single subscription.
	 *
	 * @param string $subscription_id Subscription ID.
	 * @param array  $changes         Update fields.
	 * @return array|WP_Error
	 */
	public function update_subscription( $subscription_id, $changes ) {
		$changes['subscription'] = $subscription_id;
		return $this->update_subscriptions( array( $changes ) );
	}

	/**
	 * Charge managed subscriptions (rebill).
	 *
	 * @param string|array $subscription_ids One or more subscription IDs.
	 * @return array|WP_Error
	 */
	public function charge_subscriptions( $subscription_ids ) {
		$subscription_ids = array_values( array_filter( (array) $subscription_ids ) );
		$payload          = array(
			'subscriptions' => array_map(
				static function ( $id ) {
					return array( 'subscription' => $id );
				},
				$subscription_ids
			),
		);
		return $this->request( 'POST', '/subscriptions/charge', $payload );
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
			$path = add_query_arg( 'billingPeriod', 0, $path );
		}
		return $this->request( 'DELETE', $path );
	}

	/**
	 * Pause a subscription.
	 *
	 * @param string $subscription_id ID.
	 * @param array  $args              Args (pausePeriodCount required by API).
	 * @return array|WP_Error
	 */
	public function pause_subscription( $subscription_id, $args = array() ) {
		if ( empty( $args['pausePeriodCount'] ) ) {
			$args['pausePeriodCount'] = 1;
		}
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
	 * Resume a canceled subscription that has not yet deactivated.
	 *
	 * Uses POST /subscriptions with deactivation=null per FastSpring docs.
	 *
	 * @param string $subscription_id ID.
	 * @return array|WP_Error
	 */
	public function uncancel_subscription( $subscription_id ) {
		return $this->update_subscription(
			$subscription_id,
			array(
				'deactivation' => null,
			)
		);
	}

	/**
	 * Convert a deactivated trial subscription to paid (creates buyer session).
	 *
	 * @param string $subscription_id ID.
	 * @return array|WP_Error
	 */
	public function convert_subscription( $subscription_id ) {
		return $this->request( 'POST', '/subscriptions/' . rawurlencode( $subscription_id ) . '/convert' );
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
	 * Parse a single invoice from a GET or POST response.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error
	 */
	public function parse_invoice( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['id'] ) && is_string( $response['id'] ) ) {
			return $response;
		}

		if ( ! empty( $response['code'] ) && ! empty( $response['message'] ) ) {
			return new WP_Error(
				'vms_efwp_invoice_error',
				(string) $response['message'],
				array( 'body' => $response )
			);
		}

		return new WP_Error(
			'vms_efwp_invoice_not_found',
			__( 'FastSpring did not return invoice details.', 'vms-elements-fastspring-woo-payment' ),
			array( 'body' => $response )
		);
	}

	/**
	 * Retrieve a single invoice.
	 *
	 * @param string $invoice_id ID.
	 * @return array|WP_Error
	 */
	public function get_invoice( $invoice_id ) {
		$result = $this->request( 'GET', '/invoices/' . rawurlencode( $invoice_id ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_invoice( $result );
	}

	/**
	 * Create and finalize a payment invoice.
	 *
	 * @param array $payload InvoiceRequest body.
	 * @return array|WP_Error
	 */
	public function create_payment_invoice( $payload ) {
		$result = $this->request( 'POST', '/invoices/paymentInvoice', $payload );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_invoice( $result );
	}

	/**
	 * Legacy invoice search (not documented in current Invoices API).
	 *
	 * @deprecated 1.0.7 Use invoice ID lookup via get_invoice() instead.
	 * @param array $params Query.
	 * @return array|WP_Error
	 */
	public function search_invoices( $params = array() ) {
		unset( $params );
		return $this->request( 'GET', '/invoices' );
	}

	/* -------------------------------------------------------------------- *
	 * Quotes
	 * -------------------------------------------------------------------- */

	/**
	 * Parse a single quote from an API response.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error
	 */
	public function parse_quote( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['id'] ) && is_string( $response['id'] ) ) {
			return $response;
		}

		if ( ! empty( $response['message'] ) && is_string( $response['message'] ) ) {
			return new WP_Error(
				'vms_efwp_quote_error',
				$response['message'],
				array( 'body' => $response )
			);
		}

		return new WP_Error(
			'vms_efwp_quote_not_found',
			__( 'FastSpring did not return quote details.', 'vms-elements-fastspring-woo-payment' ),
			array( 'body' => $response )
		);
	}

	/**
	 * Extract quote objects from a list response.
	 *
	 * @param array $response Decoded list response.
	 * @return array
	 */
	public function parse_quotes_list( $response ) {
		if ( ! is_array( $response ) ) {
			return array();
		}

		if ( ! empty( $response['_embedded']['quotes'] ) && is_array( $response['_embedded']['quotes'] ) ) {
			return $response['_embedded']['quotes'];
		}

		if ( ! empty( $response['quotes'] ) && is_array( $response['quotes'] ) ) {
			return $response['quotes'];
		}

		if ( isset( $response[0] ) ) {
			return $response;
		}

		return array();
	}

	/**
	 * List quotes.
	 *
	 * @param array $params Query parameters (createdEmail, onlyQuoteId, statuses[]).
	 * @return array|WP_Error
	 */
	public function list_quotes( $params = array() ) {
		$statuses = array();
		if ( ! empty( $params['statuses'] ) ) {
			$statuses = array_values( array_filter( (array) $params['statuses'] ) );
		}

		$params = $this->filter_query_params( $params, array( 'createdEmail', 'onlyQuoteId' ) );

		$path = '/quotes';
		foreach ( $statuses as $status ) {
			$path = add_query_arg( 'statuses', $status, $path );
		}

		return $this->request( 'GET', $path, $params );
	}

	/**
	 * Search/list quotes.
	 *
	 * @deprecated 1.0.7 Use list_quotes() instead.
	 * @param array $params Query parameters.
	 * @return array|WP_Error
	 */
	public function get_quotes( $params = array() ) {
		return $this->list_quotes( $params );
	}

	/**
	 * Get a single quote.
	 *
	 * @param string $quote_id ID.
	 * @return array|WP_Error
	 */
	public function get_quote( $quote_id ) {
		$result = $this->request( 'GET', '/quotes/' . rawurlencode( $quote_id ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_quote( $result );
	}

	/**
	 * Create a quote.
	 *
	 * @param array $payload CreateQuoteRequest body.
	 * @return array|WP_Error
	 */
	public function create_quote( $payload ) {
		$result = $this->request( 'POST', '/quotes', $payload );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_quote( $result );
	}

	/**
	 * Update a quote.
	 *
	 * @param string $quote_id ID.
	 * @param array  $payload  UpdateQuoteRequest body.
	 * @return array|WP_Error
	 */
	public function update_quote( $quote_id, $payload ) {
		$result = $this->request(
			'PUT',
			'/quotes/' . rawurlencode( $quote_id ),
			array(
				'updateQuoteRequest' => $payload,
			)
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_quote( $result );
	}

	/**
	 * Cancel a quote.
	 *
	 * @param string $quote_id ID.
	 * @return array|WP_Error
	 */
	public function cancel_quote( $quote_id ) {
		$result = $this->request( 'POST', '/quotes/' . rawurlencode( $quote_id ) . '/cancel' );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_quote( $result );
	}

	/* -------------------------------------------------------------------- *
	 * Returns / refunds
	 * -------------------------------------------------------------------- */

	/**
	 * Normalize a return row from the API (ID field is `return`).
	 *
	 * @param array $row Return row.
	 * @return array
	 */
	public function parse_return( $row ) {
		if ( ! is_array( $row ) ) {
			return array();
		}

		if ( ! empty( $row['return'] ) && empty( $row['id'] ) ) {
			$row['id'] = $row['return'];
		}

		return $row;
	}

	/**
	 * Parse a returns API response into normalized return rows.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error
	 */
	public function parse_returns_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( empty( $response['returns'] ) || ! is_array( $response['returns'] ) ) {
			return new WP_Error(
				'vms_efwp_returns_not_found',
				__( 'FastSpring did not return any return details.', 'vms-elements-fastspring-woo-payment' ),
				array( 'body' => $response )
			);
		}

		$rows = array();
		foreach ( $response['returns'] as $row ) {
			if ( is_array( $row ) && isset( $row['result'] ) && 'error' === $row['result'] ) {
				return new WP_Error(
					'vms_efwp_return_error',
					$this->extract_error_message( $row, '', 200 ),
					array( 'status' => 200, 'body' => $response )
				);
			}
			$rows[] = $this->parse_return( $row );
		}

		return $rows;
	}

	/**
	 * Create one or more order returns.
	 *
	 * @param array $returns Array of return request objects.
	 * @return array|WP_Error Normalized return rows.
	 */
	public function create_returns( $returns ) {
		$result = $this->request( 'POST', '/returns', array( 'returns' => array_values( $returns ) ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_returns_response( $result );
	}

	/**
	 * Issue a single return / refund.
	 *
	 * @param array $return_spec Single return request object.
	 * @return array|WP_Error Normalized return rows.
	 */
	public function create_return( $return_spec ) {
		return $this->create_returns( array( $return_spec ) );
	}

	/**
	 * Retrieve one or more returns by ID.
	 *
	 * @param string|string[] $return_ids Return ID or comma-separated IDs.
	 * @return array|WP_Error Normalized return rows.
	 */
	public function get_returns( $return_ids ) {
		$ids = is_array( $return_ids ) ? $return_ids : preg_split( '/\s*,\s*/', (string) $return_ids );
		$ids = array_values( array_filter( array_map( 'trim', $ids ) ) );
		if ( empty( $ids ) ) {
			return new WP_Error( 'vms_efwp_missing_return_id', __( 'A return ID is required.', 'vms-elements-fastspring-woo-payment' ) );
		}

		$path = '/returns/' . implode( ',', array_map( 'rawurlencode', $ids ) );
		$result = $this->request( 'GET', $path );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_returns_response( $result );
	}

	/**
	 * Get a single return.
	 *
	 * @param string $return_id ID.
	 * @return array|WP_Error Single normalized return row.
	 */
	public function get_return( $return_id ) {
		$rows = $this->get_returns( $return_id );
		if ( is_wp_error( $rows ) ) {
			return $rows;
		}

		if ( empty( $rows[0] ) ) {
			return new WP_Error(
				'vms_efwp_return_not_found',
				__( 'FastSpring did not return return details.', 'vms-elements-fastspring-woo-payment' )
			);
		}

		return $rows[0];
	}

	/**
	 * Legacy return search (not documented in current Returns API).
	 *
	 * @deprecated 1.0.7 Use get_returns() with a return ID instead.
	 * @param array $params Query.
	 * @return array|WP_Error
	 */
	public function search_returns( $params = array() ) {
		return $this->request( 'GET', '/returns', $params );
	}

	/* -------------------------------------------------------------------- *
	 * Sessions
	 * -------------------------------------------------------------------- */

	/**
	 * Build a Sessions V2 checkout API path.
	 *
	 * @param string $checkout_path Store/checkout path (storeId/checkoutId).
	 * @param string $suffix        Optional path suffix (e.g. /sessions/{id}).
	 * @return string|WP_Error
	 */
	private function checkout_session_path( $checkout_path, $suffix = '' ) {
		$checkout_path = trim( (string) $checkout_path, '/' );
		if ( '' === $checkout_path ) {
			return new WP_Error(
				'vms_efwp_checkout_path_required',
				__( 'A checkout path (storeId/checkoutId) is required for the Sessions API.', 'vms-elements-fastspring-woo-payment' )
			);
		}

		$parts = array_filter( explode( '/', $checkout_path ) );
		if ( count( $parts ) < 2 ) {
			return new WP_Error(
				'vms_efwp_checkout_path_invalid',
				__( 'Checkout path must be in the format storeId/checkoutId.', 'vms-elements-fastspring-woo-payment' )
			);
		}

		return '/v2/checkouts/' . implode( '/', array_map( 'rawurlencode', $parts ) ) . $suffix;
	}

	/**
	 * Resolve a checkout path, defaulting to plugin settings.
	 *
	 * @param string $checkout_path Optional explicit path.
	 * @return string|WP_Error
	 */
	private function resolve_checkout_path( $checkout_path = '' ) {
		$checkout_path = trim( (string) $checkout_path );
		if ( '' === $checkout_path ) {
			$checkout_path = $this->settings->checkout_path();
		}
		if ( '' === $checkout_path ) {
			return new WP_Error(
				'vms_efwp_checkout_path_missing',
				__( 'Configure storefront and popup checkout path in settings, or provide a checkout path.', 'vms-elements-fastspring-woo-payment' )
			);
		}

		return $checkout_path;
	}

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
			'vms_efwp_session_not_found',
			__( 'FastSpring did not return session details.', 'vms-elements-fastspring-woo-payment' ),
			array( 'body' => $response )
		);
	}

	/**
	 * Parse a Sessions V2 response.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error
	 */
	public function parse_checkout_session( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['id'] ) && is_string( $response['id'] ) ) {
			return $response;
		}

		if ( ! empty( $response['message'] ) && is_string( $response['message'] ) ) {
			return new WP_Error(
				'vms_efwp_session_error',
				$response['message'],
				array( 'body' => $response )
			);
		}

		return new WP_Error(
			'vms_efwp_session_not_found',
			__( 'FastSpring did not return session details.', 'vms-elements-fastspring-woo-payment' ),
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

	/**
	 * Retrieve a legacy Sessions v1 session (undocumented; maintained for admin lookup).
	 *
	 * @param string $session_id ID.
	 * @return array|WP_Error
	 */
	public function get_session( $session_id ) {
		$result = $this->request( 'GET', '/sessions/' . rawurlencode( $session_id ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_session_v1( $result );
	}

	/**
	 * Create a Sessions V2 checkout session.
	 *
	 * @param string $checkout_path Store/checkout path.
	 * @param array  $payload       CreateSessionRequest body.
	 * @return array|WP_Error
	 */
	public function create_checkout_session( $checkout_path, $payload ) {
		$checkout_path = $this->resolve_checkout_path( $checkout_path );
		if ( is_wp_error( $checkout_path ) ) {
			return $checkout_path;
		}

		$path = $this->checkout_session_path( $checkout_path, '/sessions' );
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		$result = $this->request( 'POST', $path, $payload );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_checkout_session( $result );
	}

	/**
	 * Retrieve a Sessions V2 checkout session.
	 *
	 * @param string $checkout_path Store/checkout path.
	 * @param string $session_id    Session ID.
	 * @return array|WP_Error
	 */
	public function get_checkout_session( $checkout_path, $session_id ) {
		$checkout_path = $this->resolve_checkout_path( $checkout_path );
		if ( is_wp_error( $checkout_path ) ) {
			return $checkout_path;
		}

		$path = $this->checkout_session_path( $checkout_path, '/sessions/' . rawurlencode( $session_id ) );
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		$result = $this->request( 'GET', $path );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_checkout_session( $result );
	}

	/**
	 * Update a Sessions V2 checkout session.
	 *
	 * @param string $checkout_path Store/checkout path.
	 * @param string $session_id    Session ID.
	 * @param array  $payload       CreateSessionRequest body.
	 * @return array|WP_Error
	 */
	public function update_checkout_session( $checkout_path, $session_id, $payload ) {
		$checkout_path = $this->resolve_checkout_path( $checkout_path );
		if ( is_wp_error( $checkout_path ) ) {
			return $checkout_path;
		}

		$path = $this->checkout_session_path( $checkout_path, '/sessions/' . rawurlencode( $session_id ) );
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		$result = $this->request( 'PUT', $path, $payload );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_checkout_session( $result );
	}

	/**
	 * Retrieve payment methods for a Sessions V2 session.
	 *
	 * @param string $checkout_path Store/checkout path.
	 * @param string $session_id    Session ID.
	 * @return array|WP_Error
	 */
	public function get_checkout_session_payment_methods( $checkout_path, $session_id ) {
		$checkout_path = $this->resolve_checkout_path( $checkout_path );
		if ( is_wp_error( $checkout_path ) ) {
			return $checkout_path;
		}

		$path = $this->checkout_session_path( $checkout_path, '/sessions/' . rawurlencode( $session_id ) . '/payment-methods' );
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		return $this->request( 'GET', $path );
	}

	/**
	 * Add an item to a Sessions V2 session cart.
	 *
	 * @param string $checkout_path Store/checkout path.
	 * @param string $session_id    Session ID.
	 * @param array  $item          OrderItemRequest body.
	 * @return array|WP_Error
	 */
	public function add_checkout_session_item( $checkout_path, $session_id, $item ) {
		$checkout_path = $this->resolve_checkout_path( $checkout_path );
		if ( is_wp_error( $checkout_path ) ) {
			return $checkout_path;
		}

		$path = $this->checkout_session_path( $checkout_path, '/sessions/' . rawurlencode( $session_id ) . '/cart/items' );
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		return $this->request( 'POST', $path, $item );
	}

	/**
	 * Update an item in a Sessions V2 session cart.
	 *
	 * @param string $checkout_path Store/checkout path.
	 * @param string $session_id    Session ID.
	 * @param string $product_path  Product path.
	 * @param array  $item          OrderItemRequest body.
	 * @return array|WP_Error
	 */
	public function update_checkout_session_item( $checkout_path, $session_id, $product_path, $item ) {
		$checkout_path = $this->resolve_checkout_path( $checkout_path );
		if ( is_wp_error( $checkout_path ) ) {
			return $checkout_path;
		}

		$path = $this->checkout_session_path(
			$checkout_path,
			'/sessions/' . rawurlencode( $session_id ) . '/cart/items/' . rawurlencode( $product_path )
		);
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		return $this->request( 'PUT', $path, $item );
	}

	/**
	 * Remove an item from a Sessions V2 session cart.
	 *
	 * @param string $checkout_path Store/checkout path.
	 * @param string $session_id    Session ID.
	 * @param string $product_path  Product path.
	 * @return array|WP_Error
	 */
	public function remove_checkout_session_item( $checkout_path, $session_id, $product_path ) {
		$checkout_path = $this->resolve_checkout_path( $checkout_path );
		if ( is_wp_error( $checkout_path ) ) {
			return $checkout_path;
		}

		$path = $this->checkout_session_path(
			$checkout_path,
			'/sessions/' . rawurlencode( $session_id ) . '/cart/items/' . rawurlencode( $product_path )
		);
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		return $this->request( 'DELETE', $path );
	}

	/**
	 * Update customer details on a Sessions V2 session.
	 *
	 * @param string $checkout_path Store/checkout path.
	 * @param string $session_id    Session ID.
	 * @param array  $customer        CustomerRequest body.
	 * @return array|WP_Error
	 */
	public function update_checkout_session_customer( $checkout_path, $session_id, $customer ) {
		$checkout_path = $this->resolve_checkout_path( $checkout_path );
		if ( is_wp_error( $checkout_path ) ) {
			return $checkout_path;
		}

		$path = $this->checkout_session_path( $checkout_path, '/sessions/' . rawurlencode( $session_id ) . '/customer' );
		if ( is_wp_error( $path ) ) {
			return $path;
		}

		return $this->request( 'PUT', $path, $customer );
	}

	/**
	 * Create a Sessions V2 checkout session (legacy alias).
	 *
	 * @deprecated 1.0.7 Use create_checkout_session() with checkout path.
	 * @param array  $payload       Payload.
	 * @param string $checkout_path Optional checkout path.
	 * @return array|WP_Error
	 */
	public function create_session_v2( $payload, $checkout_path = '' ) {
		return $this->create_checkout_session( $checkout_path, $payload );
	}

	/**
	 * Update a Sessions V2 session (legacy alias).
	 *
	 * @deprecated 1.0.7 Use update_checkout_session().
	 * @param string $session_id    ID.
	 * @param array  $payload         Updates.
	 * @param string $checkout_path Optional checkout path.
	 * @return array|WP_Error
	 */
	public function update_session_v2( $session_id, $payload, $checkout_path = '' ) {
		return $this->update_checkout_session( $checkout_path, $session_id, $payload );
	}

	/**
	 * Get a Sessions V2 session (legacy alias).
	 *
	 * @deprecated 1.0.7 Use get_checkout_session().
	 * @param string $session_id    ID.
	 * @param string $checkout_path Optional checkout path.
	 * @return array|WP_Error
	 */
	public function get_session_v2( $session_id, $checkout_path = '' ) {
		return $this->get_checkout_session( $checkout_path, $session_id );
	}

	/* -------------------------------------------------------------------- *
	 * Events
	 * -------------------------------------------------------------------- */

	/**
	 * Normalize an event row from list/update responses.
	 *
	 * @param array $row Event row.
	 * @return array
	 */
	public function parse_event( $row ) {
		if ( ! is_array( $row ) ) {
			return array();
		}

		if ( ! empty( $row['event'] ) && empty( $row['id'] ) ) {
			$row['id'] = $row['event'];
		}

		return $row;
	}

	/**
	 * Parse a list events API response.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error Keys: events, total, page, nextPage, limit.
	 */
	public function parse_events_list( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['result'] ) && 'error' === $response['result'] ) {
			return new WP_Error(
				'vms_efwp_events_error',
				$this->extract_error_message( $response, '', 200 ),
				array( 'status' => 200, 'body' => $response )
			);
		}

		$events = array();
		if ( ! empty( $response['events'] ) && is_array( $response['events'] ) ) {
			$events = $response['events'];
		} elseif ( ! empty( $response['data'] ) && is_array( $response['data'] ) ) {
			$events = $response['data'];
		} elseif ( isset( $response[0] ) && is_array( $response[0] ) ) {
			$events = $response;
		}

		$parsed = array();
		foreach ( $events as $event ) {
			$parsed[] = $this->parse_event( $event );
		}

		return array(
			'events'   => $parsed,
			'total'    => isset( $response['total'] ) ? (int) $response['total'] : count( $parsed ),
			'page'     => isset( $response['page'] ) ? (int) $response['page'] : 1,
			'nextPage' => $response['nextPage'] ?? null,
			'limit'    => isset( $response['limit'] ) ? (int) $response['limit'] : null,
		);
	}

	/**
	 * List processed or unprocessed events.
	 *
	 * @param string $type   processed|unprocessed.
	 * @param array  $params Query params: days (required, max 30), begin, end.
	 * @return array|WP_Error Parsed list payload.
	 */
	public function list_events( $type = 'unprocessed', $params = array() ) {
		$type = in_array( $type, array( 'processed', 'unprocessed' ), true ) ? $type : 'unprocessed';

		$params = $this->filter_query_params( $params, array( 'days', 'begin', 'end' ) );

		if ( empty( $params['days'] ) ) {
			$params['days'] = 7;
		}
		$params['days'] = max( 1, min( 30, (int) $params['days'] ) );

		foreach ( array( 'begin', 'end' ) as $date_key ) {
			if ( empty( $params[ $date_key ] ) ) {
				unset( $params[ $date_key ] );
			}
		}

		$result = $this->request( 'GET', '/events/' . $type, $params );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_events_list( $result );
	}

	/**
	 * List events of a given type.
	 *
	 * @param string $type   Type: processed | unprocessed.
	 * @param array  $params Query params (days, begin, end).
	 * @return array|WP_Error
	 */
	public function get_events( $type = 'unprocessed', $params = array() ) {
		return $this->list_events( $type, $params );
	}

	/**
	 * Update an event processed status.
	 *
	 * @param string $event_id  Event ID.
	 * @param bool   $processed Processed flag.
	 * @return array|WP_Error
	 */
	public function update_event( $event_id, $processed ) {
		$result = $this->request(
			'POST',
			'/events/' . rawurlencode( $event_id ),
			array(
				'processed' => (bool) $processed,
			)
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_event( $result );
	}

	/**
	 * Mark an event as processed.
	 *
	 * @param string $event_id ID.
	 * @return array|WP_Error
	 */
	public function mark_event_processed( $event_id ) {
		return $this->update_event( $event_id, true );
	}

	/**
	 * Mark an event as unprocessed.
	 *
	 * @param string $event_id ID.
	 * @return array|WP_Error
	 */
	public function mark_event_unprocessed( $event_id ) {
		return $this->update_event( $event_id, false );
	}

	/* -------------------------------------------------------------------- *
	 * Data / Reports
	 * -------------------------------------------------------------------- */

	/**
	 * Build a Data API report request body.
	 *
	 * @param array $args Request fields (filter dates, columns, async, etc).
	 * @return array
	 */
	public function build_data_report_request( $args = array() ) {
		$request = array();
		$filter  = array();

		$start = $args['startDate'] ?? $args['begin'] ?? '';
		$end   = $args['endDate'] ?? $args['end'] ?? '';
		if ( $start ) {
			$filter['startDate'] = $start;
		}
		if ( $end ) {
			$filter['endDate'] = $end;
		}

		foreach ( array( 'syncDate', 'countryISO', 'productNames', 'productPaths', 'segments', 'countryNames' ) as $key ) {
			if ( ! empty( $args[ $key ] ) ) {
				$filter[ $key ] = $args[ $key ];
			}
		}

		if ( ! empty( $filter ) ) {
			$request['filter'] = $filter;
		}

		foreach ( array( 'reportColumns', 'groupBy', 'pageCount', 'pageNumber', 'async', 'notificationEmails' ) as $key ) {
			if ( array_key_exists( $key, $args ) && '' !== $args[ $key ] && array() !== $args[ $key ] ) {
				$request[ $key ] = $args[ $key ];
			}
		}

		if ( ! array_key_exists( 'async', $request ) ) {
			$request['async'] = false;
		}

		return $request;
	}

	/**
	 * Parse a single data job row.
	 *
	 * @param array $row Job row.
	 * @return array
	 */
	public function parse_data_job( $row ) {
		if ( ! is_array( $row ) ) {
			return array();
		}

		return $row;
	}

	/**
	 * Parse a list jobs API response.
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error
	 */
	public function parse_data_jobs_list( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$jobs = array();
		if ( isset( $response[0] ) && is_array( $response[0] ) && ! empty( $response[0]['id'] ) ) {
			$jobs = $response;
		} elseif ( ! empty( $response['jobs'] ) && is_array( $response['jobs'] ) ) {
			$jobs = $response['jobs'];
		} elseif ( ! empty( $response['id'] ) ) {
			$jobs = array( $response );
		}

		$parsed = array();
		foreach ( $jobs as $job ) {
			$parsed[] = $this->parse_data_job( $job );
		}

		return $parsed;
	}

	/**
	 * Parse a generate report API response (sync or async).
	 *
	 * @param array|WP_Error $response API response.
	 * @return array|WP_Error
	 */
	public function parse_report_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['status'] ) && 'BAD_REQUEST' === $response['status'] ) {
			return new WP_Error(
				'vms_efwp_data_error',
				$this->extract_error_message( $response, '', 400 ),
				array( 'status' => 400, 'body' => $response )
			);
		}

		if ( ! empty( $response['report'] ) && is_array( $response['report'] ) ) {
			return array(
				'mode'    => 'sync',
				'report'  => $response['report'],
				'request' => $response['request'] ?? array(),
			);
		}

		if ( ! empty( $response['id'] ) ) {
			return array(
				'mode' => 'async',
				'job'  => $this->parse_data_job( $response ),
			);
		}

		return $response;
	}

	/**
	 * Generate a subscription report.
	 *
	 * @param array $body Report request body.
	 * @return array|WP_Error
	 */
	public function generate_subscription_report( $body = array() ) {
		$result = $this->request( 'POST', '/data/v1/subscription', $body );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_report_response( $result );
	}

	/**
	 * Generate a revenue report.
	 *
	 * @param array $body Report request body.
	 * @return array|WP_Error
	 */
	public function generate_revenue_report( $body = array() ) {
		$result = $this->request( 'POST', '/data/v1/revenue', $body );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_report_response( $result );
	}

	/**
	 * Trigger a Data report.
	 *
	 * @param string $type   Report type: revenue|subscription.
	 * @param array  $params Report parameters (begin/end or full request body fields).
	 * @return array|WP_Error
	 */
	public function create_report( $type, $params = array() ) {
		$type = in_array( $type, array( 'revenue', 'subscription' ), true ) ? $type : 'revenue';
		$body = $this->build_data_report_request( $params );

		if ( 'subscription' === $type ) {
			return $this->generate_subscription_report( $body );
		}

		return $this->generate_revenue_report( $body );
	}

	/**
	 * List all data jobs.
	 *
	 * @return array|WP_Error
	 */
	public function list_data_jobs() {
		$result = $this->request( 'GET', '/data/v1/jobs' );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->parse_data_jobs_list( $result );
	}

	/**
	 * Retrieve a data job by id.
	 *
	 * @param string $job_id Job id.
	 * @return array|WP_Error
	 */
	public function get_data_job( $job_id ) {
		$result = $this->request( 'GET', '/data/v1/jobs/' . rawurlencode( $job_id ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( isset( $result['status'] ) && 'BAD_REQUEST' === $result['status'] ) {
			return new WP_Error(
				'vms_efwp_data_error',
				$this->extract_error_message( $result, '', 400 ),
				array( 'status' => 400, 'body' => $result )
			);
		}

		return $this->parse_data_job( $result );
	}

	/**
	 * Check the status of a previously requested report job.
	 *
	 * @param string $request_id Job id.
	 * @return array|WP_Error
	 */
	public function get_report_status( $request_id ) {
		return $this->get_data_job( $request_id );
	}

	/**
	 * Reset the Data API cache.
	 *
	 * @return string|WP_Error
	 */
	public function reset_data_cache() {
		return $this->request(
			'GET',
			'/data/v1/util/cache',
			array(),
			array(
				'headers'      => array( 'Accept' => 'text/plain, application/json' ),
				'raw_response' => true,
			)
		);
	}

	/**
	 * Download a finished report payload.
	 *
	 * @param string $job_id Job id.
	 * @return string|WP_Error
	 */
	public function download_report( $job_id ) {
		return $this->request(
			'GET',
			'/data/v1/downloads/' . rawurlencode( $job_id ),
			array(),
			array(
				'headers'      => array( 'Accept' => 'text/plain, application/json' ),
				'raw_response' => true,
			)
		);
	}

	/* -------------------------------------------------------------------- *
	 * Webhooks (HMAC management)
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
				'vms_efwp_webhooks_error',
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

	/**
	 * Update the HMAC secret for a webhook URL (POST /webhooks/keys).
	 *
	 * @param string $url    Webhook receiver URL.
	 * @param string $secret HMAC secret.
	 * @return array|WP_Error
	 */
	public function update_webhook_key_secret( $url, $secret ) {
		return $this->request(
			'POST',
			'/webhooks/keys',
			array(
				'url'        => $url,
				'hmacSecret' => $secret,
			)
		);
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
