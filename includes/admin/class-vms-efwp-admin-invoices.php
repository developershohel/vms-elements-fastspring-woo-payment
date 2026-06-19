<?php
/**
 * Invoices screen.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Invoices.
 */
class VMS_EFWP_Admin_Invoices {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab  = VMS_EFWP_Admin_Resource_Base::get_filter_key( 'tab', 'lookup' );
		$tabs = array(
			'lookup' => __( 'Lookup', 'vms-elements-fastspring-woo-payment' ),
			'create' => __( 'Create Invoice', 'vms-elements-fastspring-woo-payment' ),
		);
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'lookup';
		}
		$base = admin_url( 'admin.php?page=vms-efwp-invoices' );

		echo '<div class="wrap vefwp-wrap">';
		VMS_EFWP_Admin_Resource_Base::render_header(
			__( 'Invoices', 'vms-elements-fastspring-woo-payment' ),
			__( 'Create and retrieve FastSpring payment invoices.', 'vms-elements-fastspring-woo-payment' ),
			array(
				'<a class="button button-primary" href="' . esc_url( add_query_arg( 'tab', 'create', $base ) ) . '">' . esc_html__( 'Create invoice', 'vms-elements-fastspring-woo-payment' ) . '</a>',
			)
		);

		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		if ( 'create' === $tab && VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_create_invoice' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_create_invoice' ) ) {
			self::handle_create_invoice();
		}

		VMS_EFWP_Admin_Resource_Base::render_nav_tabs( $tabs, $tab, $base );

		if ( 'create' === $tab ) {
			self::render_create_form();
		} else {
			self::render_lookup();
		}

		VMS_EFWP_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}

	/**
	 * Handle POST create invoice.
	 */
	private static function handle_create_invoice() {
		$payload = self::build_invoice_payload_from_post();
		if ( is_wp_error( $payload ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $payload );
			return;
		}

		$result = vms_efwp()->api->create_payment_invoice( $payload );
		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
			return;
		}

		VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
		self::render_invoice_links_notice( $result );

		if ( ! empty( $result['id'] ) ) {
			printf(
				'<p><a class="button" href="%s">%s</a></p>',
				esc_url(
					add_query_arg(
						array(
							'page'       => 'vms-efwp-invoices',
							'tab'        => 'lookup',
							'invoice_id' => $result['id'],
						),
						admin_url( 'admin.php' )
					)
				),
				esc_html__( 'View invoice details', 'vms-elements-fastspring-woo-payment' )
			);
		}
	}

	/**
	 * Build InvoiceRequest payload from POST fields.
	 *
	 * @return array|WP_Error
	 */
	private static function build_invoice_payload_from_post() {
		$currency = strtoupper( VMS_EFWP_Admin_Resource_Base::post_text( 'currency_code', 'USD' ) );
		$email    = VMS_EFWP_Admin_Resource_Base::post_email( 'bill_email' );
		$first    = VMS_EFWP_Admin_Resource_Base::post_text( 'bill_first_name' );
		$last     = VMS_EFWP_Admin_Resource_Base::post_text( 'bill_last_name' );

		if ( ! $email || ! $first || ! $last ) {
			return new WP_Error(
				'vms_efwp_invoice_validation',
				__( 'Bill-to email, first name, and last name are required.', 'vms-elements-fastspring-woo-payment' )
			);
		}

		$items = self::parse_invoice_items_from_post();
		if ( empty( $items ) ) {
			return new WP_Error(
				'vms_efwp_invoice_validation',
				__( 'At least one invoice line item is required.', 'vms-elements-fastspring-woo-payment' )
			);
		}

		$bill_contact = array(
			'contactType' => 'billTo',
			'contact'     => array_filter(
				array(
					'email'       => $email,
					'firstName'   => $first,
					'lastName'    => $last,
					'companyName' => VMS_EFWP_Admin_Resource_Base::post_text( 'bill_company' ),
					'phoneNumber' => VMS_EFWP_Admin_Resource_Base::post_text( 'bill_phone' ),
				)
			),
		);

		$bill_address = self::build_address_from_post( 'bill_' );
		if ( ! empty( $bill_address ) ) {
			$bill_contact['address'] = $bill_address;
		}

		$contacts = array( $bill_contact );

		if ( VMS_EFWP_Admin_Resource_Base::post_text( 'same_as_bill_to' ) ) {
			$deliver_contact = $bill_contact;
			$deliver_contact['contactType'] = 'deliverTo';
			$contacts[]                     = $deliver_contact;
		} else {
			$deliver_email = VMS_EFWP_Admin_Resource_Base::post_email( 'deliver_email' );
			$deliver_first = VMS_EFWP_Admin_Resource_Base::post_text( 'deliver_first_name' );
			$deliver_last  = VMS_EFWP_Admin_Resource_Base::post_text( 'deliver_last_name' );
			if ( $deliver_email && $deliver_first && $deliver_last ) {
				$deliver_contact = array(
					'contactType' => 'deliverTo',
					'contact'     => array_filter(
						array(
							'email'       => $deliver_email,
							'firstName'   => $deliver_first,
							'lastName'    => $deliver_last,
							'companyName' => VMS_EFWP_Admin_Resource_Base::post_text( 'deliver_company' ),
							'phoneNumber' => VMS_EFWP_Admin_Resource_Base::post_text( 'deliver_phone' ),
						)
					),
				);
				$deliver_address = self::build_address_from_post( 'deliver_' );
				if ( ! empty( $deliver_address ) ) {
					$deliver_contact['address'] = $deliver_address;
				}
				$contacts[] = $deliver_contact;
			}
		}

		$payload = array(
			'currencyCode'   => $currency,
			'contacts'       => $contacts,
			'invoiceItems'   => $items,
			'paymentMethod'  => VMS_EFWP_Admin_Resource_Base::post_text( 'payment_method', 'CARD' ),
			'mode'           => vms_efwp()->settings->is_sandbox() ? 'TEST' : 'LIVE',
			'languageCode'   => VMS_EFWP_Admin_Resource_Base::post_text( 'language_code', 'en' ),
			'invoiceNote'    => VMS_EFWP_Admin_Resource_Base::post_textarea( 'invoice_note' ),
		);

		if ( VMS_EFWP_Admin_Resource_Base::post_text( 'auto_convert_currency' ) ) {
			$payload['autoConvertPaymentCurrency'] = true;
		}

		$due_date = VMS_EFWP_Admin_Resource_Base::post_text( 'due_date' );
		if ( $due_date ) {
			$payload['dueDate'] = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $due_date . ' UTC' ) );
		}

		$tags_json = trim( VMS_EFWP_Admin_Resource_Base::post_textarea( 'tags_json' ) );
		if ( $tags_json ) {
			$decoded = json_decode( $tags_json, true );
			if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
				return new WP_Error(
					'vms_efwp_invoice_validation',
					__( 'Tags JSON must be a valid JSON object string.', 'vms-elements-fastspring-woo-payment' )
				);
			}
			$payload['tagsJson'] = wp_json_encode( $decoded );
		}

		return array_filter(
			$payload,
			static function ( $value ) {
				return null !== $value && '' !== $value;
			}
		);
	}

	/**
	 * Parse invoice line items from POST.
	 *
	 * @return array
	 */
	private static function parse_invoice_items_from_post() {
		$items        = array();
		$product_path = VMS_EFWP_Admin_Resource_Base::post_text( 'product_path' );
		$quantity     = max( 1, VMS_EFWP_Admin_Resource_Base::post_int( 'quantity', 1 ) );

		if ( $product_path ) {
			$use_catalog = true;
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verify_post() runs before build_invoice_payload_from_post().
			if ( isset( $_POST['use_catalog_pricing'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$use_catalog = (bool) $_POST['use_catalog_pricing'];
			}

			$items[] = array_filter(
				array(
					'productPath'             => $product_path,
					'quantity'                => $quantity,
					'useCatalogPricing'       => $use_catalog,
					'sku'                     => VMS_EFWP_Admin_Resource_Base::post_text( 'sku' ),
					'display'                 => VMS_EFWP_Admin_Resource_Base::post_text( 'item_display' ),
					'summary'                 => VMS_EFWP_Admin_Resource_Base::post_text( 'item_summary' ),
					'extendedItemDescription' => VMS_EFWP_Admin_Resource_Base::post_textarea( 'item_description' ),
				),
				static function ( $value ) {
					return null !== $value && '' !== $value;
				}
			);
		}

		$extra = VMS_EFWP_Admin_Resource_Base::post_text( 'additional_products' );
		if ( $extra ) {
			foreach ( array_filter( array_map( 'trim', explode( ',', $extra ) ) ) as $path_qty ) {
				$parts = array_map( 'trim', explode( ':', $path_qty ) );
				$path  = $parts[0] ?? '';
				$qty   = isset( $parts[1] ) ? max( 1, (int) $parts[1] ) : 1;
				if ( $path ) {
					$items[] = array(
						'productPath'       => $path,
						'quantity'          => $qty,
						'useCatalogPricing' => true,
					);
				}
			}
		}

		return $items;
	}

	/**
	 * Build an address array from prefixed POST fields.
	 *
	 * @param string $prefix Field prefix (e.g. bill_).
	 * @return array
	 */
	private static function build_address_from_post( $prefix ) {
		return array_filter(
			array(
				'addressLine1' => VMS_EFWP_Admin_Resource_Base::post_text( $prefix . 'address_line1' ),
				'addressLine2' => VMS_EFWP_Admin_Resource_Base::post_text( $prefix . 'address_line2' ),
				'city'         => VMS_EFWP_Admin_Resource_Base::post_text( $prefix . 'city' ),
				'region'       => VMS_EFWP_Admin_Resource_Base::post_text( $prefix . 'region' ),
				'postalCode'   => VMS_EFWP_Admin_Resource_Base::post_text( $prefix . 'postal_code' ),
				'country'      => strtoupper( VMS_EFWP_Admin_Resource_Base::post_text( $prefix . 'country' ) ),
			)
		);
	}

	/**
	 * Render create invoice form.
	 */
	private static function render_create_form() {
		?>
		<div class="vefwp-card">
			<h2><?php esc_html_e( 'Create payment invoice', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Creates and finalizes a payment invoice via POST /invoices/paymentInvoice.', 'vms-elements-fastspring-woo-payment' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_create_invoice' ); ?>
				<input type="hidden" name="wpfs_create_invoice" value="1" />

				<h3><?php esc_html_e( 'Invoice', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Currency', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="currency_code" maxlength="3" value="USD" class="regular-text" required /></label></p>
					<p><label><?php esc_html_e( 'Language', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="language_code" value="en" class="regular-text" /></label></p>
					<p>
						<label><?php esc_html_e( 'Payment method', 'vms-elements-fastspring-woo-payment' ); ?><br />
							<select name="payment_method">
								<?php foreach ( array( 'CARD', 'PAYPAL', 'WIRE', 'ACH' ) as $method ) : ?>
									<option value="<?php echo esc_attr( $method ); ?>" <?php selected( 'CARD', $method ); ?>><?php echo esc_html( $method ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</p>
					<p><label><?php esc_html_e( 'Due date', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="date" name="due_date" class="regular-text" /></label></p>
					<p><label><input type="checkbox" name="auto_convert_currency" value="1" checked /> <?php esc_html_e( 'Auto-convert payment currency', 'vms-elements-fastspring-woo-payment' ); ?></label></p>
				</div>
				<p><label><?php esc_html_e( 'Invoice note', 'vms-elements-fastspring-woo-payment' ); ?><br /><textarea name="invoice_note" rows="2" class="large-text"></textarea></label></p>
				<p><label><?php esc_html_e( 'Tags JSON', 'vms-elements-fastspring-woo-payment' ); ?><br /><textarea name="tags_json" rows="2" class="large-text" placeholder='{"purchaseOrder":"PO-123"}'></textarea></label></p>

				<h3><?php esc_html_e( 'Bill to', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Email', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="email" name="bill_email" required class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Company', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="bill_company" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'First name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="bill_first_name" required class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Last name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="bill_last_name" required class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Phone', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="bill_phone" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Address line 1', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="bill_address_line1" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'City', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="bill_city" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Region', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="bill_region" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Postal code', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="bill_postal_code" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Country', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="bill_country" maxlength="2" placeholder="US" class="regular-text" /></label></p>
				</div>

				<h3><?php esc_html_e( 'Deliver to', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<p><label><input type="checkbox" name="same_as_bill_to" value="1" checked /> <?php esc_html_e( 'Same as bill to', 'vms-elements-fastspring-woo-payment' ); ?></label></p>
				<div class="vefwp-grid vefwp-grid--two" data-vefwp-deliver-fields hidden>
					<p><label><?php esc_html_e( 'Email', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="email" name="deliver_email" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Company', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="deliver_company" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'First name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="deliver_first_name" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Last name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="deliver_last_name" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Phone', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="deliver_phone" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Address line 1', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="deliver_address_line1" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'City', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="deliver_city" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Region', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="deliver_region" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Postal code', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="deliver_postal_code" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Country', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="deliver_country" maxlength="2" placeholder="US" class="regular-text" /></label></p>
				</div>

				<h3><?php esc_html_e( 'Line items', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Product path', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="product_path" required class="regular-text" placeholder="sample-product" /></label></p>
					<p><label><?php esc_html_e( 'Quantity', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" name="quantity" min="1" value="1" class="small-text" /></label></p>
					<p><label><input type="checkbox" name="use_catalog_pricing" value="1" checked /> <?php esc_html_e( 'Use catalog pricing', 'vms-elements-fastspring-woo-payment' ); ?></label></p>
					<p><label><?php esc_html_e( 'SKU', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="sku" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Display name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="item_display" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Summary', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="item_summary" class="regular-text" /></label></p>
				</div>
				<p><label><?php esc_html_e( 'Extended description', 'vms-elements-fastspring-woo-payment' ); ?><br /><textarea name="item_description" rows="2" class="large-text"></textarea></label></p>
				<p><label><?php esc_html_e( 'Additional products', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="additional_products" class="large-text" placeholder="addon-product:2, another-product" /></label></p>
				<p class="description"><?php esc_html_e( 'Optional comma-separated product paths. Use path:quantity for custom quantities.', 'vms-elements-fastspring-woo-payment' ); ?></p>

				<p><button class="button button-primary"><?php esc_html_e( 'Create invoice', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Lookup a single invoice.
	 */
	private static function render_lookup() {
		$invoice_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'invoice_id' );
		VMS_EFWP_Admin_Resource_Base::render_lookup_form(
			'vms-efwp-invoices',
			'invoice_id',
			__( 'Invoice ID', 'vms-elements-fastspring-woo-payment' ),
			__( 'FastSpring invoice ID', 'vms-elements-fastspring-woo-payment' ),
			$invoice_id,
			array( 'tab' => 'lookup' )
		);
		if ( ! $invoice_id ) {
			printf(
				'<p class="description">%s</p>',
				esc_html__( 'Retrieve a payment invoice by ID via GET /invoices/{invoiceId}.', 'vms-elements-fastspring-woo-payment' )
			);
			return;
		}

		$result = vms_efwp()->api->get_invoice( $invoice_id );
		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
			return;
		}

		self::render_invoice_summary( $result );
		self::render_invoice_links_notice( $result );
		VMS_EFWP_Admin_Resource_Base::render_api_detail_card( $result, __( 'Invoice details', 'vms-elements-fastspring-woo-payment' ) );
	}

	/**
	 * Render invoice summary table.
	 *
	 * @param array $invoice Invoice object.
	 */
	private static function render_invoice_summary( $invoice ) {
		$status   = $invoice['status'] ?? '';
		$currency = $invoice['currency'] ?? ( $invoice['paymentCurrencyCode'] ?? '' );
		$total    = $invoice['totalOrderValue'] ?? ( $invoice['paymentTotals']['payableTotal'] ?? 0 );
		$bill_to  = $invoice['purchaser']['email'] ?? '';
		$ship_to  = $invoice['receiver']['email'] ?? '';
		?>
		<div class="vefwp-card vefwp-card--wide">
			<h2><?php esc_html_e( 'Invoice summary', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<table class="widefat striped vefwp-table vefwp-table--meta">
				<tbody>
					<tr><th><?php esc_html_e( 'Invoice ID', 'vms-elements-fastspring-woo-payment' ); ?></th><td><code><?php echo esc_html( $invoice['id'] ?? '' ); ?></code></td></tr>
					<tr><th><?php esc_html_e( 'Status', 'vms-elements-fastspring-woo-payment' ); ?></th><td><span class="vefwp-status vefwp-status--<?php echo esc_attr( strtolower( $status ) ); ?>"><?php echo esc_html( $status ); ?></span></td></tr>
					<tr><th><?php esc_html_e( 'Type', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $invoice['invoiceType'] ?? '' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Order reference', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $invoice['orderReference'] ?? ( $invoice['paymentOrderReference'] ?? '' ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Total', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( trim( $currency . ' ' . number_format_i18n( (float) $total, 2 ) ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Created', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $invoice['createdOn'] ?? '' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Due', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $invoice['dueDate'] ?? ( $invoice['paymentDueDate'] ?? '' ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Bill to', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $bill_to ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Deliver to', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $ship_to ); ?></td></tr>
				</tbody>
			</table>

			<?php if ( ! empty( $invoice['items'] ) && is_array( $invoice['items'] ) ) : ?>
				<h3><?php esc_html_e( 'Line items', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<table class="widefat striped vefwp-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Product', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Display', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Qty', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Line total', 'vms-elements-fastspring-woo-payment' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $invoice['items'] as $item ) : ?>
							<tr>
								<td><code><?php echo esc_html( $item['productPath'] ?? '' ); ?></code></td>
								<td><?php echo esc_html( $item['display'] ?? '' ); ?></td>
								<td><?php echo esc_html( (string) ( $item['quantity'] ?? 1 ) ); ?></td>
								<td><?php echo esc_html( number_format_i18n( (float) ( $item['totalPreShippingPrice'] ?? 0 ), 2 ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render payment / PDF links when present.
	 *
	 * @param array $invoice Invoice object.
	 */
	private static function render_invoice_links_notice( $invoice ) {
		$links = array_filter(
			array(
				'paymentInvoiceWebPayLink' => __( 'Pay invoice', 'vms-elements-fastspring-woo-payment' ),
				'paymentInvoiceWebLink'    => __( 'View invoice', 'vms-elements-fastspring-woo-payment' ),
				'paymentInvoicePdfLink'    => __( 'Download PDF', 'vms-elements-fastspring-woo-payment' ),
			),
			static function ( $label, $key ) use ( $invoice ) {
				return ! empty( $invoice[ $key ] );
			},
			ARRAY_FILTER_USE_BOTH
		);

		if ( empty( $links ) ) {
			return;
		}
		?>
		<div class="vefwp-card vefwp-card--actions">
			<?php foreach ( $links as $key => $label ) : ?>
				<a class="button" href="<?php echo esc_url( $invoice[ $key ] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
