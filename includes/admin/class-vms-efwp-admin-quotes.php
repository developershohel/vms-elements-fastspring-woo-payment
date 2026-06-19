<?php
/**
 * Quotes screen.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Quotes.
 */
class VMS_EFWP_Admin_Quotes {

	/**
	 * Quote statuses from FastSpring docs.
	 *
	 * @return string[]
	 */
	private static function quote_statuses() {
		return array( 'OPEN', 'CANCELED', 'AWAITING_PAYMENT', 'COMPLETED', 'EXPIRED' );
	}

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab  = VMS_EFWP_Admin_Resource_Base::get_filter_key( 'tab', 'list' );
		$tabs = array(
			'list'   => __( 'Quotes', 'vms-elements-fastspring-woo-payment' ),
			'lookup' => __( 'Lookup', 'vms-elements-fastspring-woo-payment' ),
			'create' => __( 'Create Quote', 'vms-elements-fastspring-woo-payment' ),
		);
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'list';
		}
		$base = admin_url( 'admin.php?page=vms-efwp-quotes' );

		echo '<div class="wrap vefwp-wrap">';
		VMS_EFWP_Admin_Resource_Base::render_header(
			__( 'Quotes', 'vms-elements-fastspring-woo-payment' ),
			__( 'B2B price quotes and custom sales cycles.', 'vms-elements-fastspring-woo-payment' ),
			array(
				'<a class="button button-primary" href="' . esc_url( add_query_arg( 'tab', 'create', $base ) ) . '">' . esc_html__( 'New quote', 'vms-elements-fastspring-woo-payment' ) . '</a>',
			)
		);

		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		if ( 'create' === $tab && VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_create_quote' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_create_quote' ) ) {
			self::handle_create_quote();
		}

		if ( 'lookup' === $tab && VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_update_quote' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_update_quote' ) ) {
			self::handle_update_quote();
		}

		VMS_EFWP_Admin_Resource_Base::render_nav_tabs( $tabs, $tab, $base );

		if ( 'create' === $tab ) {
			self::render_quote_form( 'create' );
		} elseif ( 'lookup' === $tab ) {
			self::render_lookup();
		} else {
			self::render_list();
		}

		VMS_EFWP_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}

	/**
	 * Handle POST create quote.
	 */
	private static function handle_create_quote() {
		$payload = self::build_quote_payload_from_post();
		if ( is_wp_error( $payload ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $payload );
			return;
		}

		$result = vms_efwp()->api->create_quote( $payload );
		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
			return;
		}

		VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
		self::render_quote_link_notice( $result );

		if ( ! empty( $result['id'] ) ) {
			printf(
				'<p><a class="button" href="%s">%s</a></p>',
				esc_url(
					add_query_arg(
						array(
							'page'     => 'vms-efwp-quotes',
							'tab'      => 'lookup',
							'quote_id' => $result['id'],
						),
						admin_url( 'admin.php' )
					)
				),
				esc_html__( 'View quote details', 'vms-elements-fastspring-woo-payment' )
			);
		}
	}

	/**
	 * Handle POST update quote.
	 */
	private static function handle_update_quote() {
		$quote_id = VMS_EFWP_Admin_Resource_Base::post_text( 'quote_id' );
		if ( ! $quote_id ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html__( 'Missing quote ID.', 'vms-elements-fastspring-woo-payment' ) );
			return;
		}

		$payload = self::build_quote_payload_from_post( true );
		if ( is_wp_error( $payload ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $payload );
			return;
		}

		VMS_EFWP_Admin_Resource_Base::render_result_notice( vms_efwp()->api->update_quote( $quote_id, $payload ) );
	}

	/**
	 * Build CreateQuoteRequest / UpdateQuoteRequest payload from POST.
	 *
	 * @param bool $is_update Whether this is an update request.
	 * @return array|WP_Error
	 */
	private static function build_quote_payload_from_post( $is_update = false ) {
		$name  = VMS_EFWP_Admin_Resource_Base::post_text( 'name' );
		$email = VMS_EFWP_Admin_Resource_Base::post_email( 'email' );
		$first = VMS_EFWP_Admin_Resource_Base::post_text( 'first' );
		$last  = VMS_EFWP_Admin_Resource_Base::post_text( 'last' );

		if ( ! $name || ! $email || ! $first || ! $last ) {
			return new WP_Error(
				'vms_efwp_quote_validation',
				__( 'Quote name, recipient email, first name, and last name are required.', 'vms-elements-fastspring-woo-payment' )
			);
		}

		$country    = strtoupper( VMS_EFWP_Admin_Resource_Base::post_text( 'country' ) );
		$postal     = VMS_EFWP_Admin_Resource_Base::post_text( 'postal_code' );
		$items      = self::parse_quote_items_from_post();
		$currency   = strtoupper( VMS_EFWP_Admin_Resource_Base::post_text( 'currency', 'USD' ) );
		$expiration = max( 1, min( 90, VMS_EFWP_Admin_Resource_Base::post_int( 'expiration_days', 30 ) ) );
		$fulfillment = VMS_EFWP_Admin_Resource_Base::post_text( 'fulfillment_term', 'ON_PAYMENT' );

		if ( empty( $items ) ) {
			return new WP_Error(
				'vms_efwp_quote_validation',
				__( 'At least one quote line item is required.', 'vms-elements-fastspring-woo-payment' )
			);
		}

		if ( strlen( $country ) !== 2 || ! $postal ) {
			return new WP_Error(
				'vms_efwp_quote_validation',
				__( 'Recipient country (2-letter code) and postal code are required.', 'vms-elements-fastspring-woo-payment' )
			);
		}

		$payload = array(
			'name'               => $name,
			'recipient'          => array_filter(
				array(
					'email'   => $email,
					'first'   => $first,
					'last'    => $last,
					'company' => VMS_EFWP_Admin_Resource_Base::post_text( 'company' ),
					'phone'   => VMS_EFWP_Admin_Resource_Base::post_text( 'phone' ),
				),
				static function ( $value ) {
					return is_string( $value ) && '' !== $value;
				}
			),
			'recipientAddress'   => array_filter(
				array(
					'addressLine1' => VMS_EFWP_Admin_Resource_Base::post_text( 'address_line1' ),
					'addressLine2' => VMS_EFWP_Admin_Resource_Base::post_text( 'address_line2' ),
					'city'         => VMS_EFWP_Admin_Resource_Base::post_text( 'city' ),
					'region'       => VMS_EFWP_Admin_Resource_Base::post_text( 'region' ),
					'country'      => $country,
					'postalCode'   => $postal,
				)
			),
			'items'              => $items,
			'currency'           => $currency,
			'expirationDateDays' => $expiration,
			'fulfillmentTerm'    => in_array( $fulfillment, array( 'ON_PAYMENT', 'ON_QUOTE_ACCEPTANCE' ), true ) ? $fulfillment : 'ON_PAYMENT',
			'notes'              => VMS_EFWP_Admin_Resource_Base::post_textarea( 'notes' ),
		);

		$coupon = VMS_EFWP_Admin_Resource_Base::post_text( 'coupon' );
		if ( $coupon ) {
			$payload['coupon'] = $coupon;
		}

		$net_terms = VMS_EFWP_Admin_Resource_Base::post_int( 'net_terms_days' );
		if ( $net_terms > 0 ) {
			$payload['netTermsDays'] = $net_terms;
		}

		$tax_id = VMS_EFWP_Admin_Resource_Base::post_text( 'tax_id' );
		if ( $tax_id ) {
			$payload['taxId'] = $tax_id;
		}

		$tag_key   = VMS_EFWP_Admin_Resource_Base::post_text( 'tag_key' );
		$tag_value = VMS_EFWP_Admin_Resource_Base::post_text( 'tag_value' );
		if ( $tag_key && $tag_value ) {
			$payload['tags'] = array(
				array(
					'key'   => $tag_key,
					'value' => $tag_value,
				),
			);
		}

		$notes = VMS_EFWP_Admin_Resource_Base::post_textarea( 'notes' );
		if ( '' !== $notes || $is_update ) {
			$payload['notes'] = $notes;
		}

		return $payload;
	}

	/**
	 * Parse quote line items from POST.
	 *
	 * @return array
	 */
	private static function parse_quote_items_from_post() {
		$items        = array();
		$product_path = VMS_EFWP_Admin_Resource_Base::post_text( 'product_path' );
		$quantity     = max( 1, VMS_EFWP_Admin_Resource_Base::post_int( 'quantity', 1 ) );
		$unit_price   = VMS_EFWP_Admin_Resource_Base::post_float( 'unit_list_price' );

		if ( $product_path ) {
			$item = array(
				'product'  => $product_path,
				'quantity' => $quantity,
			);
			if ( $unit_price > 0 ) {
				$item['unitListPrice'] = $unit_price;
			}
			$items[] = $item;
		}

		$extra = VMS_EFWP_Admin_Resource_Base::post_text( 'additional_products' );
		if ( $extra ) {
			foreach ( array_filter( array_map( 'trim', explode( ',', $extra ) ) ) as $path_qty_price ) {
				$parts = array_map( 'trim', explode( ':', $path_qty_price ) );
				$path  = $parts[0] ?? '';
				$qty   = isset( $parts[1] ) ? max( 1, (int) $parts[1] ) : 1;
				$price = isset( $parts[2] ) ? (float) $parts[2] : 0;
				if ( ! $path ) {
					continue;
				}
				$item = array(
					'product'  => $path,
					'quantity' => $qty,
				);
				if ( $price > 0 ) {
					$item['unitListPrice'] = $price;
				}
				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Render create/update quote form.
	 *
	 * @param string     $mode  create|update.
	 * @param array|null $quote Prefill data for update.
	 */
	private static function render_quote_form( $mode, $quote = null ) {
		$is_update = 'update' === $mode;
		$recipient = is_array( $quote ) ? ( $quote['recipient'] ?? array() ) : array();
		$address   = is_array( $quote ) ? ( $quote['recipientAddress'] ?? array() ) : array();
		$first_item = is_array( $quote ) && ! empty( $quote['items'][0] ) ? $quote['items'][0] : array();
		$tag        = is_array( $quote ) && ! empty( $quote['tags'][0] ) ? $quote['tags'][0] : array();
		$additional = '';
		if ( is_array( $quote ) && ! empty( $quote['items'] ) && count( $quote['items'] ) > 1 ) {
			$parts = array();
			foreach ( array_slice( $quote['items'], 1 ) as $item ) {
				$line = $item['product'] ?? '';
				if ( isset( $item['quantity'] ) ) {
					$line .= ':' . (int) $item['quantity'];
				}
				if ( isset( $item['unitListPrice'] ) ) {
					$line .= ':' . (float) $item['unitListPrice'];
				}
				if ( $line ) {
					$parts[] = $line;
				}
			}
			$additional = implode( ', ', $parts );
		}
		?>
		<div class="vefwp-card">
			<h2><?php echo $is_update ? esc_html__( 'Update quote', 'vms-elements-fastspring-woo-payment' ) : esc_html__( 'Create quote', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<p class="description">
				<?php
				echo $is_update
					? esc_html__( 'Updates an existing quote via PUT /quotes/{quote_id}.', 'vms-elements-fastspring-woo-payment' )
					: esc_html__( 'Creates a new quote via POST /quotes.', 'vms-elements-fastspring-woo-payment' );
				?>
			</p>
			<form method="post">
				<?php wp_nonce_field( $is_update ? 'wpfs_update_quote' : 'wpfs_create_quote' ); ?>
				<input type="hidden" name="<?php echo $is_update ? 'wpfs_update_quote' : 'wpfs_create_quote'; ?>" value="1" />
				<?php if ( $is_update && ! empty( $quote['id'] ) ) : ?>
					<input type="hidden" name="quote_id" value="<?php echo esc_attr( $quote['id'] ); ?>" />
				<?php endif; ?>

				<h3><?php esc_html_e( 'Quote', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="name" required class="regular-text" value="<?php echo esc_attr( $quote['name'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Currency', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="currency" maxlength="3" value="<?php echo esc_attr( $quote['currency'] ?? 'USD' ); ?>" class="regular-text" required /></label></p>
					<p><label><?php esc_html_e( 'Expiration (days)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" name="expiration_days" min="1" max="90" value="<?php echo esc_attr( (string) ( $quote['expirationDateDays'] ?? 30 ) ); ?>" class="small-text" /></label></p>
					<p>
						<label><?php esc_html_e( 'Fulfillment term', 'vms-elements-fastspring-woo-payment' ); ?><br />
							<select name="fulfillment_term">
								<?php foreach ( array( 'ON_PAYMENT', 'ON_QUOTE_ACCEPTANCE' ) as $term ) : ?>
									<option value="<?php echo esc_attr( $term ); ?>" <?php selected( $quote['fulfillmentTerm'] ?? 'ON_PAYMENT', $term ); ?>><?php echo esc_html( $term ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</p>
					<p><label><?php esc_html_e( 'Coupon', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="coupon" class="regular-text" value="<?php echo esc_attr( $quote['coupon'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Net terms (days)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" name="net_terms_days" min="0" value="<?php echo esc_attr( (string) ( $quote['netTermsDays'] ?? '' ) ); ?>" class="small-text" /></label></p>
					<p><label><?php esc_html_e( 'Tax ID', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="tax_id" class="regular-text" value="<?php echo esc_attr( $quote['taxId'] ?? '' ); ?>" /></label></p>
				</div>
				<p><label><?php esc_html_e( 'Notes', 'vms-elements-fastspring-woo-payment' ); ?><br /><textarea name="notes" rows="2" class="large-text"><?php echo esc_textarea( $quote['notes'] ?? '' ); ?></textarea></label></p>
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Tag key', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="tag_key" class="regular-text" value="<?php echo esc_attr( $tag['key'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Tag value', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="tag_value" class="regular-text" value="<?php echo esc_attr( $tag['value'] ?? '' ); ?>" /></label></p>
				</div>

				<h3><?php esc_html_e( 'Recipient', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Email', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="email" required name="email" class="regular-text" value="<?php echo esc_attr( $recipient['email'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Company', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="company" class="regular-text" value="<?php echo esc_attr( $recipient['company'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'First name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" required name="first" class="regular-text" value="<?php echo esc_attr( $recipient['first'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Last name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" required name="last" class="regular-text" value="<?php echo esc_attr( $recipient['last'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Phone', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="phone" class="regular-text" value="<?php echo esc_attr( $recipient['phone'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Address line 1', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="address_line1" class="regular-text" value="<?php echo esc_attr( $address['addressLine1'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'City', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="city" class="regular-text" value="<?php echo esc_attr( $address['city'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Region', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="region" class="regular-text" value="<?php echo esc_attr( $address['region'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Postal code', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" required name="postal_code" class="regular-text" value="<?php echo esc_attr( $address['postalCode'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Country', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" required name="country" maxlength="2" placeholder="US" class="regular-text" value="<?php echo esc_attr( $address['country'] ?? '' ); ?>" /></label></p>
				</div>

				<h3><?php esc_html_e( 'Line items', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Product path', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="product_path" required class="regular-text" value="<?php echo esc_attr( $first_item['product'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Quantity', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" name="quantity" min="1" value="<?php echo esc_attr( (string) ( $first_item['quantity'] ?? 1 ) ); ?>" class="small-text" /></label></p>
					<p><label><?php esc_html_e( 'Unit list price', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" step="0.01" name="unit_list_price" class="regular-text" value="<?php echo esc_attr( (string) ( $first_item['unitListPrice'] ?? '' ) ); ?>" /></label></p>
				</div>
				<p><label><?php esc_html_e( 'Additional products', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="additional_products" class="large-text" value="<?php echo esc_attr( $additional ); ?>" placeholder="addon-product:2:10.00, another-product" /></label></p>
				<p class="description"><?php esc_html_e( 'Optional comma-separated products. Use path:quantity:unitListPrice for custom pricing.', 'vms-elements-fastspring-woo-payment' ); ?></p>

				<p><button class="button button-primary"><?php echo $is_update ? esc_html__( 'Update quote', 'vms-elements-fastspring-woo-payment' ) : esc_html__( 'Create quote', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
			</form>
		</div>
		<?php
	}

	/**
	 * List quotes.
	 */
	private static function render_list() {
		$created_email = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'created_email' );
		$status        = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'status' );
		$only_ids      = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'only_ids' );

		$params = array();
		if ( $created_email ) {
			$params['createdEmail'] = $created_email;
		}
		if ( $status ) {
			$params['statuses'] = array( $status );
		}
		if ( $only_ids ) {
			$params['onlyQuoteId'] = 'true';
		}

		$result = vms_efwp()->api->list_quotes( $params );
		$quotes = array();
		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$quotes = vms_efwp()->api->parse_quotes_list( $result );
		}
		?>
		<form method="get" class="vefwp-filters">
			<input type="hidden" name="page" value="vms-efwp-quotes" />
			<input type="hidden" name="tab" value="list" />
			<input type="search" name="created_email" value="<?php echo esc_attr( $created_email ); ?>" placeholder="<?php esc_attr_e( 'Creator email...', 'vms-elements-fastspring-woo-payment' ); ?>" />
			<select name="status">
				<option value=""><?php esc_html_e( 'All statuses', 'vms-elements-fastspring-woo-payment' ); ?></option>
				<?php foreach ( self::quote_statuses() as $s ) : ?>
					<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( $s ); ?></option>
				<?php endforeach; ?>
			</select>
			<label><input type="checkbox" name="only_ids" value="1" <?php checked( $only_ids, '1' ); ?> /> <?php esc_html_e( 'IDs only', 'vms-elements-fastspring-woo-payment' ); ?></label>
			<button class="button"><?php esc_html_e( 'Filter', 'vms-elements-fastspring-woo-payment' ); ?></button>
		</form>

		<table class="widefat striped vefwp-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Quote', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Name', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Recipient', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Total', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Status', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Expires', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'vms-elements-fastspring-woo-payment' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $quotes ) ) : ?>
				<?php VMS_EFWP_Admin_Resource_Base::render_empty_row( __( 'No quotes found.', 'vms-elements-fastspring-woo-payment' ), 7 ); ?>
			<?php else : ?>
				<?php foreach ( $quotes as $quote ) : ?>
					<?php
					$quote_id = is_string( $quote ) ? $quote : ( $quote['id'] ?? '' );
					$status_val = is_array( $quote ) ? ( $quote['status'] ?? '' ) : '';
					?>
					<tr>
						<td><code><?php echo esc_html( $quote_id ); ?></code></td>
						<td><?php echo esc_html( is_array( $quote ) ? ( $quote['name'] ?? '' ) : '' ); ?></td>
						<td><?php echo esc_html( is_array( $quote ) ? ( $quote['recipient']['email'] ?? '' ) : '' ); ?></td>
						<td>
							<?php
							if ( is_array( $quote ) ) {
								echo esc_html( $quote['totalDisplay'] ?? ( ( $quote['currency'] ?? '' ) . ' ' . number_format_i18n( (float) ( $quote['total'] ?? 0 ), 2 ) ) );
							} else {
								echo '&mdash;';
							}
							?>
						</td>
						<td>
							<?php if ( $status_val ) : ?>
								<span class="vefwp-status vefwp-status--<?php echo esc_attr( strtolower( $status_val ) ); ?>"><?php echo esc_html( $status_val ); ?></span>
							<?php else : ?>
								&mdash;
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( is_array( $quote ) ? ( $quote['expires'] ?? '' ) : '' ); ?></td>
						<td class="vefwp-row-actions">
							<?php if ( is_array( $quote ) ) : ?>
								<?php VMS_EFWP_Admin_Resource_Base::render_view_button( $quote ); ?>
							<?php endif; ?>
							<?php if ( $quote_id ) : ?>
								<a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-quotes', 'tab' => 'lookup', 'quote_id' => $quote_id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Lookup', 'vms-elements-fastspring-woo-payment' ); ?></a>
								<?php self::render_cancel_button( $quote_id, $status_val ); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Lookup a single quote.
	 */
	private static function render_lookup() {
		$quote_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'quote_id' );
		VMS_EFWP_Admin_Resource_Base::render_lookup_form(
			'vms-efwp-quotes',
			'quote_id',
			__( 'Quote ID', 'vms-elements-fastspring-woo-payment' ),
			__( 'FastSpring quote ID', 'vms-elements-fastspring-woo-payment' ),
			$quote_id,
			array( 'tab' => 'lookup' )
		);
		if ( ! $quote_id ) {
			printf(
				'<p class="description">%s</p>',
				esc_html__( 'Retrieve a quote by ID via GET /quotes/{quote_id}.', 'vms-elements-fastspring-woo-payment' )
			);
			return;
		}

		$quote = vms_efwp()->api->get_quote( $quote_id );
		if ( is_wp_error( $quote ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $quote );
			return;
		}

		self::render_quote_summary( $quote );
		self::render_quote_link_notice( $quote );
		self::render_cancel_button( $quote['id'] ?? '', $quote['status'] ?? '', true );

		if ( self::quote_is_editable( $quote['status'] ?? '' ) ) {
			self::render_quote_form( 'update', $quote );
		}

		VMS_EFWP_Admin_Resource_Base::render_api_detail_card( $quote, __( 'Quote details', 'vms-elements-fastspring-woo-payment' ) );
	}

	/**
	 * Whether a quote can still be updated or canceled.
	 *
	 * @param string $status Quote status.
	 * @return bool
	 */
	private static function quote_is_editable( $status ) {
		return in_array( $status, array( 'OPEN', 'AWAITING_PAYMENT' ), true );
	}

	/**
	 * Render cancel button when applicable.
	 *
	 * @param string $quote_id Quote ID.
	 * @param string $status   Quote status.
	 * @param bool   $block    Render as block button.
	 */
	private static function render_cancel_button( $quote_id, $status, $block = false ) {
		if ( ! $quote_id || ! self::quote_is_editable( $status ) ) {
			return;
		}
		printf(
			'<button type="button" class="button%1$s vefwp-cancel-quote" data-id="%2$s">%3$s</button>',
			$block ? '' : ' button-small',
			esc_attr( $quote_id ),
			esc_html__( 'Cancel quote', 'vms-elements-fastspring-woo-payment' )
		);
	}

	/**
	 * Render quote summary.
	 *
	 * @param array $quote Quote object.
	 */
	private static function render_quote_summary( $quote ) {
		$recipient = $quote['recipient'] ?? array();
		?>
		<div class="vefwp-card vefwp-card--wide">
			<h2><?php esc_html_e( 'Quote summary', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<table class="widefat striped vefwp-table vefwp-table--meta">
				<tbody>
					<tr><th><?php esc_html_e( 'Quote ID', 'vms-elements-fastspring-woo-payment' ); ?></th><td><code><?php echo esc_html( $quote['id'] ?? '' ); ?></code></td></tr>
					<tr><th><?php esc_html_e( 'Name', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $quote['name'] ?? '' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Status', 'vms-elements-fastspring-woo-payment' ); ?></th><td><span class="vefwp-status vefwp-status--<?php echo esc_attr( strtolower( $quote['status'] ?? '' ) ); ?>"><?php echo esc_html( $quote['status'] ?? '' ); ?></span></td></tr>
					<tr><th><?php esc_html_e( 'Recipient', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( trim( ( $recipient['first'] ?? '' ) . ' ' . ( $recipient['last'] ?? '' ) . ' <' . ( $recipient['email'] ?? '' ) . '>' ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Total', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $quote['totalDisplay'] ?? ( ( $quote['currency'] ?? '' ) . ' ' . number_format_i18n( (float) ( $quote['total'] ?? 0 ), 2 ) ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Expires', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $quote['expires'] ?? '' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Fulfillment', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $quote['fulfillmentTerm'] ?? '' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Coupon', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $quote['coupon'] ?? '' ); ?></td></tr>
				</tbody>
			</table>

			<?php if ( ! empty( $quote['items'] ) && is_array( $quote['items'] ) ) : ?>
				<h3><?php esc_html_e( 'Line items', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<table class="widefat striped vefwp-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Product', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Display', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Qty', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Subscription', 'vms-elements-fastspring-woo-payment' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $quote['items'] as $item ) : ?>
							<tr>
								<td><code><?php echo esc_html( $item['product'] ?? '' ); ?></code></td>
								<td><?php echo esc_html( $item['display'] ?? '' ); ?></td>
								<td><?php echo esc_html( (string) ( $item['quantity'] ?? 1 ) ); ?></td>
								<td><?php echo ! empty( $item['subscription'] ) ? esc_html__( 'Yes', 'vms-elements-fastspring-woo-payment' ) : esc_html__( 'No', 'vms-elements-fastspring-woo-payment' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render buyer quote URL when present.
	 *
	 * @param array $quote Quote object.
	 */
	private static function render_quote_link_notice( $quote ) {
		if ( empty( $quote['quoteUrl'] ) ) {
			return;
		}
		?>
		<div class="vefwp-card vefwp-card--actions">
			<a class="button button-primary" href="<?php echo esc_url( $quote['quoteUrl'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open quote URL', 'vms-elements-fastspring-woo-payment' ); ?></a>
		</div>
		<?php
	}
}
