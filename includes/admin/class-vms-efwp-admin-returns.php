<?php
/**
 * Returns / refunds screen.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Returns.
 */
class VMS_EFWP_Admin_Returns {

	/**
	 * FastSpring return reason codes.
	 *
	 * @return array<string, string>
	 */
	private static function return_reasons() {
		return array(
			'DUPLICATE_ORDER'      => __( 'Duplicate order', 'vms-elements-fastspring-woo-payment' ),
			'PRODUCT_NOT_RECEIVED' => __( 'Product not received', 'vms-elements-fastspring-woo-payment' ),
			'PRODUCT_DIFFERENCE'   => __( 'Product not as expected', 'vms-elements-fastspring-woo-payment' ),
			'FRAUDULENT'           => __( 'Fraudulent transaction', 'vms-elements-fastspring-woo-payment' ),
			'ORDER_ERROR'          => __( 'Incorrect order or order error', 'vms-elements-fastspring-woo-payment' ),
			'DISCOUNT'             => __( 'Discount or coupon', 'vms-elements-fastspring-woo-payment' ),
			'COMPATIBILITY_ISSUE'  => __( 'Compatibility issue', 'vms-elements-fastspring-woo-payment' ),
			'TAX_REFUND'           => __( 'Tax return', 'vms-elements-fastspring-woo-payment' ),
			'OTHER'                => __( 'Other reason', 'vms-elements-fastspring-woo-payment' ),
			'NONE'                 => __( 'None', 'vms-elements-fastspring-woo-payment' ),
		);
	}

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
			'create' => __( 'Issue Refund', 'vms-elements-fastspring-woo-payment' ),
		);
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'lookup';
		}
		$base = admin_url( 'admin.php?page=vms-efwp-returns' );

		echo '<div class="wrap vefwp-wrap">';
		VMS_EFWP_Admin_Resource_Base::render_header(
			__( 'Returns', 'vms-elements-fastspring-woo-payment' ),
			__( 'Create full or partial order refunds via the FastSpring Returns API.', 'vms-elements-fastspring-woo-payment' ),
			array(
				'<a class="button button-primary" href="' . esc_url( add_query_arg( 'tab', 'create', $base ) ) . '">' . esc_html__( 'Issue refund', 'vms-elements-fastspring-woo-payment' ) . '</a>',
			)
		);

		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		if ( 'create' === $tab && VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_create_return' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_create_return' ) ) {
			self::handle_create_return();
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
	 * Handle POST create return.
	 */
	private static function handle_create_return() {
		$returns = self::build_returns_from_post();
		if ( is_wp_error( $returns ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $returns );
			return;
		}

		$result = vms_efwp()->api->create_returns( $returns );
		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
			return;
		}

		VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );

		if ( ! empty( $result[0]['id'] ) ) {
			printf(
				'<p><a class="button" href="%s">%s</a></p>',
				esc_url(
					add_query_arg(
						array(
							'page'      => 'vms-efwp-returns',
							'tab'       => 'lookup',
							'return_id' => $result[0]['id'],
						),
						admin_url( 'admin.php' )
					)
				),
				esc_html__( 'View return details', 'vms-elements-fastspring-woo-payment' )
			);
		}
	}

	/**
	 * Build return request objects from POST.
	 *
	 * @return array|WP_Error
	 */
	private static function build_returns_from_post() {
		$returns = array();
		$primary = self::build_single_return_from_post(
			VMS_EFWP_Admin_Resource_Base::post_text( 'order_id' ),
			VMS_EFWP_Admin_Resource_Base::post_text( 'refund_type', 'FULL' ),
			VMS_EFWP_Admin_Resource_Base::post_text( 'reason', 'DUPLICATE_ORDER' ),
			VMS_EFWP_Admin_Resource_Base::post_textarea( 'note' ),
			VMS_EFWP_Admin_Resource_Base::post_text( 'notification', 'ORIGINAL' ),
			VMS_EFWP_Admin_Resource_Base::post_text( 'product_path' ),
			VMS_EFWP_Admin_Resource_Base::post_float( 'amount' ),
			VMS_EFWP_Admin_Resource_Base::post_text( 'additional_items' )
		);

		if ( is_wp_error( $primary ) ) {
			return $primary;
		}
		$returns[] = $primary;

		$extra = trim( VMS_EFWP_Admin_Resource_Base::post_textarea( 'additional_returns' ) );
		if ( $extra ) {
			foreach ( preg_split( '/\r\n|\r|\n/', $extra ) as $line ) {
				$line = trim( $line );
				if ( '' === $line ) {
					continue;
				}

				$parts = array_map( 'trim', explode( '|', $line ) );
				$spec  = self::build_single_return_from_post(
					$parts[0] ?? '',
					$parts[1] ?? 'FULL',
					$parts[2] ?? 'DUPLICATE_ORDER',
					$parts[3] ?? '',
					$parts[4] ?? 'ORIGINAL',
					$parts[5] ?? '',
					isset( $parts[6] ) ? (float) $parts[6] : 0,
					$parts[7] ?? ''
				);
				if ( is_wp_error( $spec ) ) {
					return $spec;
				}
				$returns[] = $spec;
			}
		}

		return $returns;
	}

	/**
	 * Build one return request object.
	 *
	 * @param string $order_id         Order ID or reference.
	 * @param string $refund_type      FULL or PARTIAL.
	 * @param string $reason           Reason code.
	 * @param string $note             Optional note.
	 * @param string $notification     ORIGINAL or NONE.
	 * @param string $product_path     Primary partial item product path.
	 * @param float  $amount           Primary partial item amount.
	 * @param string $additional_items Comma-separated product:amount pairs.
	 * @return array|WP_Error
	 */
	private static function build_single_return_from_post( $order_id, $refund_type, $reason, $note, $notification, $product_path, $amount, $additional_items ) {
		if ( ! $order_id ) {
			return new WP_Error(
				'vms_efwp_return_validation',
				__( 'An order ID or reference is required for each return.', 'vms-elements-fastspring-woo-payment' )
			);
		}

		$refund_type = strtoupper( $refund_type );
		if ( ! in_array( $refund_type, array( 'FULL', 'PARTIAL' ), true ) ) {
			$refund_type = 'FULL';
		}

		$reasons = array_keys( self::return_reasons() );
		if ( ! in_array( $reason, $reasons, true ) ) {
			$reason = 'DUPLICATE_ORDER';
		}

		$return = array(
			'order'        => $order_id,
			'reason'       => $reason,
			'notification' => in_array( $notification, array( 'ORIGINAL', 'NONE' ), true ) ? $notification : 'ORIGINAL',
			'refundType'   => $refund_type,
		);

		if ( '' !== $note ) {
			$return['note'] = $note;
		}

		if ( 'PARTIAL' === $refund_type ) {
			$items = self::parse_partial_items( $product_path, $amount, $additional_items );
			if ( empty( $items ) ) {
				return new WP_Error(
					'vms_efwp_return_validation',
					__( 'Partial returns require at least one product and amount.', 'vms-elements-fastspring-woo-payment' )
				);
			}
			$return['items'] = $items;
		}

		return $return;
	}

	/**
	 * Parse partial return line items.
	 *
	 * @param string $product_path     Product path.
	 * @param float  $amount           Amount.
	 * @param string $additional_items Extra items.
	 * @return array
	 */
	private static function parse_partial_items( $product_path, $amount, $additional_items ) {
		$items = array();

		if ( $product_path && $amount > 0 ) {
			$items[] = array(
				'product' => $product_path,
				'amount'  => $amount,
			);
		}

		if ( $additional_items ) {
			foreach ( array_filter( array_map( 'trim', explode( ',', $additional_items ) ) ) as $pair ) {
				$parts = array_map( 'trim', explode( ':', $pair ) );
				$path  = $parts[0] ?? '';
				$amt   = isset( $parts[1] ) ? (float) $parts[1] : 0;
				if ( $path && $amt > 0 ) {
					$items[] = array(
						'product' => $path,
						'amount'  => $amt,
					);
				}
			}
		}

		return $items;
	}

	/**
	 * Render create return form.
	 */
	private static function render_create_form() {
		$reasons = self::return_reasons();
		?>
		<div class="vefwp-card">
			<h2><?php esc_html_e( 'Issue refund', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Creates one or more order returns via POST /returns.', 'vms-elements-fastspring-woo-payment' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_create_return' ); ?>
				<input type="hidden" name="wpfs_create_return" value="1" />

				<h3><?php esc_html_e( 'Primary return', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Order ID or reference', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" required name="order_id" class="regular-text" /></label></p>
					<p>
						<label><?php esc_html_e( 'Refund type', 'vms-elements-fastspring-woo-payment' ); ?><br />
							<select name="refund_type" id="vefwp-return-refund-type">
								<option value="FULL"><?php esc_html_e( 'Full refund', 'vms-elements-fastspring-woo-payment' ); ?></option>
								<option value="PARTIAL"><?php esc_html_e( 'Partial refund', 'vms-elements-fastspring-woo-payment' ); ?></option>
							</select>
						</label>
					</p>
					<p>
						<label><?php esc_html_e( 'Reason', 'vms-elements-fastspring-woo-payment' ); ?><br />
							<select name="reason">
								<?php foreach ( $reasons as $code => $label ) : ?>
									<option value="<?php echo esc_attr( $code ); ?>" <?php selected( 'DUPLICATE_ORDER', $code ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</p>
					<p>
						<label><?php esc_html_e( 'Customer notification', 'vms-elements-fastspring-woo-payment' ); ?><br />
							<select name="notification">
								<option value="ORIGINAL"><?php esc_html_e( 'Notify customer', 'vms-elements-fastspring-woo-payment' ); ?></option>
								<option value="NONE"><?php esc_html_e( 'No notification', 'vms-elements-fastspring-woo-payment' ); ?></option>
							</select>
						</label>
					</p>
				</div>
				<p><label><?php esc_html_e( 'Note', 'vms-elements-fastspring-woo-payment' ); ?><br /><textarea name="note" rows="2" class="large-text" placeholder="<?php esc_attr_e( 'As requested by customer', 'vms-elements-fastspring-woo-payment' ); ?>"></textarea></label></p>

				<div data-vefwp-partial-return-fields hidden>
					<h3><?php esc_html_e( 'Partial return items', 'vms-elements-fastspring-woo-payment' ); ?></h3>
					<div class="vefwp-grid vefwp-grid--two">
						<p><label><?php esc_html_e( 'Product path', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="product_path" class="regular-text" placeholder="your-product-path" /></label></p>
						<p><label><?php esc_html_e( 'Amount', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" step="0.01" min="0" name="amount" class="regular-text" /></label></p>
					</div>
					<p><label><?php esc_html_e( 'Additional items', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="additional_items" class="large-text" placeholder="addon-product:15.75, another-product:5" /></label></p>
					<p class="description"><?php esc_html_e( 'Comma-separated product:amount pairs for additional partial line items.', 'vms-elements-fastspring-woo-payment' ); ?></p>
				</div>

				<h3><?php esc_html_e( 'Combined returns (optional)', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<p><label><?php esc_html_e( 'Additional return lines', 'vms-elements-fastspring-woo-payment' ); ?><br /><textarea name="additional_returns" rows="4" class="large-text" placeholder="order-id|FULL|DUPLICATE_ORDER|Note|ORIGINAL&#10;order-id|PARTIAL|OTHER|Partial refund|ORIGINAL|product-path|15.75|extra:5"></textarea></label></p>
				<p class="description"><?php esc_html_e( 'One return per line: order|refundType|reason|note|notification|product|amount|extraItems', 'vms-elements-fastspring-woo-payment' ); ?></p>

				<p><button class="button button-primary"><?php esc_html_e( 'Submit refund', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Lookup one or more returns.
	 */
	private static function render_lookup() {
		$return_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'return_id' );
		VMS_EFWP_Admin_Resource_Base::render_lookup_form(
			'vms-efwp-returns',
			'return_id',
			__( 'Return ID', 'vms-elements-fastspring-woo-payment' ),
			__( 'FastSpring return ID (comma-separated for multiple)', 'vms-elements-fastspring-woo-payment' ),
			$return_id,
			array( 'tab' => 'lookup' )
		);

		if ( ! $return_id ) {
			printf(
				'<p class="description">%s</p>',
				esc_html__( 'Retrieve return details via GET /returns/{return_id}.', 'vms-elements-fastspring-woo-payment' )
			);
			return;
		}

		$rows = vms_efwp()->api->get_returns( $return_id );
		if ( is_wp_error( $rows ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $rows );
			return;
		}

		foreach ( $rows as $row ) {
			self::render_return_summary( $row );
			VMS_EFWP_Admin_Resource_Base::render_api_detail_card( $row, __( 'Return details', 'vms-elements-fastspring-woo-payment' ) );
		}
	}

	/**
	 * Render return summary card.
	 *
	 * @param array $row Return row.
	 */
	private static function render_return_summary( $row ) {
		$original = $row['original'] ?? array();
		$customer = $row['customer'] ?? array();
		?>
		<div class="vefwp-card vefwp-card--wide">
			<h2><?php esc_html_e( 'Return summary', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<table class="widefat striped vefwp-table vefwp-table--meta">
				<tbody>
					<tr><th><?php esc_html_e( 'Return ID', 'vms-elements-fastspring-woo-payment' ); ?></th><td><code><?php echo esc_html( $row['id'] ?? ( $row['return'] ?? '' ) ); ?></code></td></tr>
					<tr><th><?php esc_html_e( 'Reference', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $row['reference'] ?? '' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Completed', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo ! empty( $row['completed'] ) ? esc_html__( 'Yes', 'vms-elements-fastspring-woo-payment' ) : esc_html__( 'No', 'vms-elements-fastspring-woo-payment' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Amount', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $row['totalReturnDisplay'] ?? ( ( $row['currency'] ?? '' ) . ' ' . number_format_i18n( (float) ( $row['totalReturn'] ?? 0 ), 2 ) ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Reason', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $row['reason'] ?? '' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Note', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $row['note'] ?? '' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Original order', 'vms-elements-fastspring-woo-payment' ); ?></th><td><code><?php echo esc_html( $original['id'] ?? ( $original['order'] ?? '' ) ); ?></code></td></tr>
					<tr><th><?php esc_html_e( 'Order reference', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $original['reference'] ?? '' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Customer', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( trim( ( $customer['first'] ?? '' ) . ' ' . ( $customer['last'] ?? '' ) . ' <' . ( $customer['email'] ?? '' ) . '>' ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Changed', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $row['changedDisplayISO8601'] ?? ( $row['changedDisplay'] ?? '' ) ); ?></td></tr>
				</tbody>
			</table>

			<?php if ( ! empty( $row['items'] ) && is_array( $row['items'] ) ) : ?>
				<h3><?php esc_html_e( 'Returned items', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<table class="widefat striped vefwp-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Product', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Display', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Qty', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Refund type', 'vms-elements-fastspring-woo-payment' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $row['items'] as $item ) : ?>
							<tr>
								<td><code><?php echo esc_html( $item['product'] ?? '' ); ?></code></td>
								<td><?php echo esc_html( $item['display'] ?? '' ); ?></td>
								<td><?php echo esc_html( (string) ( $item['quantity'] ?? 0 ) ); ?></td>
								<td><?php echo esc_html( $item['refundType'] ?? '' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}
}
