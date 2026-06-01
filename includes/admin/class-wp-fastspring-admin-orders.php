<?php
/**
 * Orders screen.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Orders.
 */
class WP_FastSpring_Admin_Orders {

	/**
	 * Render orders.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page     = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
		$status   = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$per_page = 20;

		$result = WP_FastSpring_Data_Store::get_orders(
			array(
				'page'     => $page,
				'per_page' => $per_page,
				'status'   => $status,
				'search'   => $search,
			)
		);

		$total_pages = max( 1, (int) ceil( $result['total'] / $per_page ) );
		?>
		<div class="wrap wpfs-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'FastSpring Orders', 'wp-fastspring' ); ?></h1>

			<form method="get" class="wpfs-filters">
				<input type="hidden" name="page" value="wp-fastspring-orders" />
				<select name="status">
					<option value=""><?php esc_html_e( 'All statuses', 'wp-fastspring' ); ?></option>
					<?php foreach ( array( 'completed', 'refunded', 'pending', 'cancelled' ) as $s ) : ?>
						<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
					<?php endforeach; ?>
				</select>
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by email, order id...', 'wp-fastspring' ); ?>" />
				<button class="button"><?php esc_html_e( 'Filter', 'wp-fastspring' ); ?></button>
			</form>

			<table class="widefat striped wpfs-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Order', 'wp-fastspring' ); ?></th>
						<th><?php esc_html_e( 'Customer', 'wp-fastspring' ); ?></th>
						<th><?php esc_html_e( 'Total', 'wp-fastspring' ); ?></th>
						<th><?php esc_html_e( 'Status', 'wp-fastspring' ); ?></th>
						<th><?php esc_html_e( 'Date', 'wp-fastspring' ); ?></th>
						<th><?php esc_html_e( 'WC', 'wp-fastspring' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $result['rows'] ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'No orders yet. Make a sale on FastSpring or trigger a test webhook.', 'wp-fastspring' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $result['rows'] as $row ) : ?>
						<tr>
							<td>
								<strong><?php echo esc_html( $row['fs_order_id'] ); ?></strong>
								<?php if ( ! empty( $row['fs_reference'] ) ) : ?>
									<div class="row-actions"><?php echo esc_html( $row['fs_reference'] ); ?></div>
								<?php endif; ?>
								<?php if ( (int) $row['is_test'] ) : ?>
									<span class="wpfs-badge wpfs-badge--warning"><?php esc_html_e( 'TEST', 'wp-fastspring' ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<?php echo esc_html( $row['customer_name'] ); ?>
								<div class="row-actions"><?php echo esc_html( $row['email'] ); ?></div>
							</td>
							<td><?php echo esc_html( $row['currency'] . ' ' . number_format_i18n( (float) $row['total'], 2 ) ); ?></td>
							<td><span class="wpfs-status wpfs-status--<?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( $row['status'] ); ?></span></td>
							<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $row['created_at'] ) ); ?></td>
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
						'page'   => 'wp-fastspring-orders',
						'status' => $status,
						's'      => $search,
					),
					admin_url( 'admin.php' )
				);
				echo '<div class="tablenav"><div class="tablenav-pages">' . paginate_links(
					array(
						'base'    => add_query_arg( 'paged', '%#%', $base_url ),
						'format'  => '',
						'current' => $page,
						'total'   => $total_pages,
					)
				) . '</div></div>';
			}
			?>
		</div>
		<?php
	}
}
