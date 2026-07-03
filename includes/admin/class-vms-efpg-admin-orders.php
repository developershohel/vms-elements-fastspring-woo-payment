<?php
/**
 * Orders screen.
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFPG_Admin_Orders.
 */
class VMS_EFPG_Admin_Orders {

	/**
	 * Render orders.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( VMS_EFPG_Admin_Resource_Base::is_post_submit( 'vms_efpg_resend_invoice' ) && VMS_EFPG_Admin_Resource_Base::verify_post( 'vms_efpg_resend_invoice' ) ) {
			VMS_EFPG_Admin_Invoice_Actions::handle_resend_invoice();
		}

		?>
		<div class="wrap vms-efpg-wrap">
			<?php
			VMS_EFPG_Admin_Resource_Base::render_header(
				__( 'Orders', 'vms-elements-fastspring-payment-gateway' ),
				sprintf(
					/* translators: %s: site home URL */
					__( 'All FastSpring orders for this site (%s). Stored via webhooks and checkout completion.', 'vms-elements-fastspring-payment-gateway' ),
					VMS_EFPG_Data_Store::get_site_url()
				)
			);

			self::render_stored();

			VMS_EFPG_Admin_Resource_Base::render_json_modal();
			?>
		</div>
		<?php
	}

	/**
	 * Tab: locally stored orders.
	 */
	private static function render_stored() {
		VMS_EFPG_Data_Store::sync_site_orders();

		$page     = max( 1, VMS_EFPG_Admin_Resource_Base::get_filter_int( 'paged', 1 ) );
		$status   = VMS_EFPG_Admin_Resource_Base::get_filter_text( 'status' );
		$search   = VMS_EFPG_Admin_Resource_Base::get_filter_text( 's' );
		$per_page = 20;

		$result = VMS_EFPG_Data_Store::get_orders(
			array(
				'page'       => $page,
				'per_page'   => $per_page,
				'status'     => $status,
				'search'     => $search,
				'scope_site' => true,
			)
		);

		$total_pages = max( 1, (int) ceil( $result['total'] / $per_page ) );
		$context     = VMS_EFPG_Data_Store::get_site_context();
		?>
		<p class="description vms-efpg-site-context">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: stored order count, 2: WooCommerce FastSpring order count, 3: site URL */
					__( 'Showing %1$d stored orders for %3$s (%2$d WooCommerce FastSpring payments). Each checkout tags orders with this site URL so ten sites sharing one FastSpring account stay isolated.', 'vms-elements-fastspring-payment-gateway' ),
					(int) $result['total'],
					(int) $context['wc_orders'],
					$context['site_url']
				)
			);
			?>
		</p>
		<form method="get" class="vms-efpg-filters">
			<input type="hidden" name="page" value="vms-efpg-orders" />
			<select name="status">
				<option value=""><?php esc_html_e( 'All statuses', 'vms-elements-fastspring-payment-gateway' ); ?></option>
				<?php foreach ( array( 'completed', 'refunded', 'pending', 'cancelled' ) as $s ) : ?>
					<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by email, order id...', 'vms-elements-fastspring-payment-gateway' ); ?>" />
			<button class="button"><?php esc_html_e( 'Filter', 'vms-elements-fastspring-payment-gateway' ); ?></button>
		</form>

		<table class="widefat striped vms-efpg-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Order', 'vms-elements-fastspring-payment-gateway' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'vms-elements-fastspring-payment-gateway' ); ?></th>
					<th><?php esc_html_e( 'Total', 'vms-elements-fastspring-payment-gateway' ); ?></th>
					<th><?php esc_html_e( 'Status', 'vms-elements-fastspring-payment-gateway' ); ?></th>
					<th><?php esc_html_e( 'Date', 'vms-elements-fastspring-payment-gateway' ); ?></th>
					<th><?php esc_html_e( 'Invoice', 'vms-elements-fastspring-payment-gateway' ); ?></th>
					<th><?php esc_html_e( 'WC', 'vms-elements-fastspring-payment-gateway' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $result['rows'] ) ) : ?>
				<tr><td colspan="7"><?php esc_html_e( 'No orders yet. Make a sale on FastSpring or trigger a test webhook.', 'vms-elements-fastspring-payment-gateway' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $result['rows'] as $row ) : ?>
					<?php
					$display     = VMS_EFPG_Data_Store::get_order_invoice_display( $row );
					$invoice_url = $display['invoice_url'];
					$lookup_url  = '';
					?>
					<tr>
						<td>
							<strong><?php echo esc_html( $row['fs_order_id'] ); ?></strong>
							<?php if ( ! empty( $row['fs_reference'] ) ) : ?>
								<div class="row-actions"><?php echo esc_html( $row['fs_reference'] ); ?></div>
							<?php endif; ?>
							<?php if ( (int) $row['is_test'] ) : ?>
								<span class="vms-efpg-badge vms-efpg-badge--warning"><?php esc_html_e( 'TEST', 'vms-elements-fastspring-payment-gateway' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php echo esc_html( $row['customer_name'] ); ?>
							<div class="row-actions"><?php echo esc_html( $row['email'] ); ?></div>
						</td>
						<td><?php echo esc_html( $row['currency'] . ' ' . number_format_i18n( (float) $row['total'], 2 ) ); ?></td>
						<td><span class="vms-efpg-status vms-efpg-status--<?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( $row['status'] ); ?></span></td>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $row['created_at'] ) ); ?></td>
						<td>
							<code><?php echo esc_html( $display['order_id'] ); ?></code>
							<?php if ( ! empty( $display['payment_invoice_id'] ) ) : ?>
								<div class="row-actions"><span class="description"><?php esc_html_e( 'Payment invoice', 'vms-elements-fastspring-payment-gateway' ); ?>:</span> <?php echo esc_html( $display['payment_invoice_id'] ); ?></div>
							<?php endif; ?>
							<?php if ( $lookup_url ) : ?>
								<div class="row-actions"><a href="<?php echo esc_url( $lookup_url ); ?>"><?php esc_html_e( 'Lookup receipt', 'vms-elements-fastspring-payment-gateway' ); ?></a></div>
							<?php endif; ?>
							<?php if ( $invoice_url ) : ?>
								<div class="row-actions"><a href="<?php echo esc_url( $invoice_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View', 'vms-elements-fastspring-payment-gateway' ); ?></a></div>
								<?php if ( ! empty( $row['email'] ) ) : ?>
									<span class="row-actions">
										<?php
										VMS_EFPG_Admin_Invoice_Actions::render_resend_invoice_button(
											array(
												'fs_order_id'     => $row['fs_order_id'],
												'invoice_id'      => $display['payment_invoice_id'],
												'recipient_email' => $row['email'],
												'page'            => 'vms-efpg-orders',
												'tab'             => 'stored',
											)
										);
										?>
									</span>
								<?php endif; ?>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( ! empty( $row['wc_order_id'] ) ) : ?>
								<a href="<?php echo esc_url( admin_url( 'post.php?post=' . (int) $row['wc_order_id'] . '&action=edit' ) ); ?>">#<?php echo (int) $row['wc_order_id']; ?></a>
							<?php else : ?>
								&mdash;
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>

		<?php
		if ( $total_pages > 1 ) {
			$base_url = add_query_arg(
				array(
					'page'   => 'vms-efpg-orders',
					'status' => $status,
					's'      => $search,
				),
				admin_url( 'admin.php' )
			);
			echo wp_kses_post(
				'<div class="tablenav"><div class="tablenav-pages">' . paginate_links(
					array(
						'base'    => add_query_arg( 'paged', '%#%', $base_url ),
						'format'  => '',
						'current' => $page,
						'total'   => $total_pages,
					)
				) . '</div></div>'
			);
		}
	}
}
