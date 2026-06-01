<?php
/**
 * Subscriptions screen.
 *
 * Two tabs:
 *   - Customers: active customer subscriptions tracked locally.
 *   - Catalog: subscription/recurring products in the FastSpring catalog.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Subscriptions.
 */
class WP_FastSpring_Admin_Subscriptions {

	/**
	 * Render the screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'customers';
		if ( ! in_array( $tab, array( 'customers', 'catalog' ), true ) ) {
			$tab = 'customers';
		}
		$base = admin_url( 'admin.php?page=wp-fastspring-subscriptions' );

		echo '<div class="wrap wpfs-wrap">';
		WP_FastSpring_Admin_Resource_Base::render_header(
			__( 'Subscriptions', 'wp-fastspring' ),
			'customers' === $tab
				? __( 'Active and historical customer subscriptions tracked locally from webhook events.', 'wp-fastspring' )
				: __( 'Subscription products in your FastSpring catalog (one-time products live under FastSpring → Products).', 'wp-fastspring' )
		);
		?>
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'customers', $base ) ); ?>" class="nav-tab <?php echo 'customers' === $tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Customer Subscriptions', 'wp-fastspring' ); ?>
			</a>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'catalog', $base ) ); ?>" class="nav-tab <?php echo 'catalog' === $tab ? 'nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Subscription Products', 'wp-fastspring' ); ?>
			</a>
		</h2>
		<?php

		if ( 'catalog' === $tab ) {
			self::render_catalog();
		} else {
			self::render_customer_subscriptions();
		}

		WP_FastSpring_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}

	/**
	 * Tab: customer subscriptions (local DB).
	 */
	private static function render_customer_subscriptions() {
		$page     = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
		$status   = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$per_page = 20;

		$result = WP_FastSpring_Data_Store::get_subscriptions(
			array(
				'page'     => $page,
				'per_page' => $per_page,
				'status'   => $status,
				'search'   => $search,
			)
		);

		$total_pages = max( 1, (int) ceil( $result['total'] / $per_page ) );
		?>
		<form method="get" class="wpfs-filters">
			<input type="hidden" name="page" value="wp-fastspring-subscriptions" />
			<input type="hidden" name="tab" value="customers" />
			<select name="status">
				<option value=""><?php esc_html_e( 'All statuses', 'wp-fastspring' ); ?></option>
				<?php foreach ( array( 'active', 'paused', 'overdue', 'canceled', 'deactivated', 'trial' ) as $s ) : ?>
					<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by email, product...', 'wp-fastspring' ); ?>" />
			<button class="button"><?php esc_html_e( 'Filter', 'wp-fastspring' ); ?></button>
		</form>

		<table class="widefat striped wpfs-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Subscription', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Product', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Price', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Interval', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Next charge', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Status', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'wp-fastspring' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $result['rows'] ) ) : ?>
				<tr><td colspan="8"><?php esc_html_e( 'No subscriptions tracked yet.', 'wp-fastspring' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $result['rows'] as $row ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $row['fs_subscription_id'] ); ?></strong>
							<?php if ( (int) $row['is_test'] ) : ?>
								<span class="wpfs-badge wpfs-badge--warning"><?php esc_html_e( 'TEST', 'wp-fastspring' ); ?></span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $row['email'] ); ?></td>
						<td><?php echo esc_html( $row['product'] ); ?></td>
						<td><?php echo esc_html( $row['currency'] . ' ' . number_format_i18n( (float) $row['price'], 2 ) ); ?></td>
						<td>
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: length 2: unit */
									__( '%1$d %2$s', 'wp-fastspring' ),
									(int) $row['interval_length'],
									$row['interval_unit'] ? $row['interval_unit'] : '-'
								)
							);
							?>
						</td>
						<td><?php echo esc_html( $row['next_charge'] ? mysql2date( get_option( 'date_format' ), $row['next_charge'] ) : '-' ); ?></td>
						<td><span class="wpfs-status wpfs-status--<?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( $row['status'] ); ?></span></td>
						<td>
							<button type="button" class="button button-small wpfs-sync-sub" data-id="<?php echo esc_attr( $row['fs_subscription_id'] ); ?>"><?php esc_html_e( 'Sync', 'wp-fastspring' ); ?></button>
							<?php if ( 'active' === $row['status'] ) : ?>
								<button type="button" class="button button-small wpfs-cancel-sub" data-id="<?php echo esc_attr( $row['fs_subscription_id'] ); ?>"><?php esc_html_e( 'Cancel', 'wp-fastspring' ); ?></button>
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
					'page'   => 'wp-fastspring-subscriptions',
					'tab'    => 'customers',
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
	}

	/**
	 * Tab: subscription/recurring products from the FastSpring catalog.
	 */
	private static function render_catalog() {
		if ( ! WP_FastSpring_Admin_Resource_Base::require_credentials() ) {
			return;
		}

		// Handle create.
		if ( ! empty( $_POST['wpfs_save_sub_product'] ) && WP_FastSpring_Admin_Resource_Base::verify_post( 'wpfs_save_sub_product' ) ) {
			$path     = sanitize_title( wp_unslash( $_POST['product_path'] ?? '' ) );
			$name     = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
			$cur      = strtoupper( substr( preg_replace( '/[^A-Za-z]/', '', (string) wp_unslash( $_POST['currency'] ?? 'USD' ) ), 0, 3 ) );
			$price    = (float) ( $_POST['price'] ?? 0 );
			$unit     = sanitize_text_field( wp_unslash( $_POST['interval_unit'] ?? 'month' ) );
			$length   = max( 1, (int) ( $_POST['interval_length'] ?? 1 ) );
			$summary  = sanitize_textarea_field( wp_unslash( $_POST['summary'] ?? '' ) );

			$payload = array(
				'product'     => $path,
				'display'     => array( 'en' => $name ),
				'description' => array( 'summary' => array( 'en' => $summary ) ),
				'sku'         => $path,
				'pricing'     => array(
					'price'          => array( $cur => $price ),
					'interval'       => $unit,
					'intervalLength' => $length,
				),
			);
			$result = wp_fastspring()->api->upsert_product( $payload );
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		}

		// Handle delete.
		if ( isset( $_GET['delete'] ) && check_admin_referer( 'wpfs_delete_sub_product' ) ) {
			$path   = sanitize_text_field( wp_unslash( $_GET['delete'] ) );
			$result = wp_fastspring()->api->delete_product( $path );
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		}

		$list  = wp_fastspring()->api->list_products();
		$paths = array();
		if ( is_wp_error( $list ) ) {
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $list );
		} else {
			$paths = isset( $list['products'] ) ? $list['products'] : ( isset( $list[0] ) ? $list : array() );
		}

		$products = array();
		if ( ! empty( $paths ) ) {
			$details = wp_fastspring()->api->get_products( array_slice( $paths, 0, 50 ) );
			if ( ! is_wp_error( $details ) ) {
				$products = isset( $details['products'] ) ? $details['products'] : array();
			}
		}

		// Keep only subscription-type products.
		$products = array_values(
			array_filter(
				$products,
				static function ( $p ) {
					return WP_FastSpring_Admin_Products::is_subscription_product( $p );
				}
			)
		);
		?>

		<p>
			<button type="button" class="button button-primary" data-wpfs-open-form="save-sub-product">
				<?php esc_html_e( 'New subscription product', 'wp-fastspring' ); ?>
			</button>
		</p>

		<div class="wpfs-card" data-wpfs-form="save-sub-product" hidden>
			<h2><?php esc_html_e( 'Create subscription product', 'wp-fastspring' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_save_sub_product' ); ?>
				<input type="hidden" name="wpfs_save_sub_product" value="1" />
				<div class="wpfs-grid wpfs-grid--two">
					<p><label><?php esc_html_e( 'Product path (slug)', 'wp-fastspring' ); ?><br /><input type="text" required name="product_path" class="regular-text" placeholder="pro-monthly" /></label></p>
					<p><label><?php esc_html_e( 'Display name', 'wp-fastspring' ); ?><br /><input type="text" required name="display_name" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Currency', 'wp-fastspring' ); ?><br /><input type="text" name="currency" maxlength="3" value="USD" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Price per interval', 'wp-fastspring' ); ?><br /><input type="number" step="0.01" required name="price" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Interval unit', 'wp-fastspring' ); ?><br />
						<select name="interval_unit">
							<option value="day"><?php esc_html_e( 'Day', 'wp-fastspring' ); ?></option>
							<option value="week"><?php esc_html_e( 'Week', 'wp-fastspring' ); ?></option>
							<option value="month" selected><?php esc_html_e( 'Month', 'wp-fastspring' ); ?></option>
							<option value="year"><?php esc_html_e( 'Year', 'wp-fastspring' ); ?></option>
						</select>
					</label></p>
					<p><label><?php esc_html_e( 'Interval length', 'wp-fastspring' ); ?><br /><input type="number" min="1" name="interval_length" class="regular-text" value="1" /></label></p>
					<p class="wpfs-grid--full"><label><?php esc_html_e( 'Summary', 'wp-fastspring' ); ?><br /><textarea name="summary" rows="2" class="regular-text"></textarea></label></p>
				</div>
				<p>
					<button class="button button-primary"><?php esc_html_e( 'Save subscription product', 'wp-fastspring' ); ?></button>
					<button type="button" class="button" data-wpfs-close-form="save-sub-product"><?php esc_html_e( 'Cancel', 'wp-fastspring' ); ?></button>
				</p>
			</form>
		</div>

		<table class="widefat striped wpfs-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Path', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Display', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Price', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Interval', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'wp-fastspring' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $products ) ) : ?>
				<tr><td colspan="5"><em><?php esc_html_e( 'No subscription products in your FastSpring catalog yet.', 'wp-fastspring' ); ?></em></td></tr>
			<?php else : ?>
				<?php foreach ( $products as $p ) :
					$path     = $p['product'] ?? '';
					$display  = $p['display'] ?? array();
					$display_first = is_array( $display ) ? reset( $display ) : (string) $display;
					$pricing  = $p['pricing'] ?? array();
					$prices   = $pricing['price'] ?? array();
					$interval = ( isset( $pricing['intervalLength'] ) ? (int) $pricing['intervalLength'] : 1 ) . ' ' . ( $pricing['interval'] ?? $pricing['intervalUnit'] ?? 'month' );
					$delete_url = wp_nonce_url(
						add_query_arg(
							array( 'page' => 'wp-fastspring-subscriptions', 'tab' => 'catalog', 'delete' => $path ),
							admin_url( 'admin.php' )
						),
						'wpfs_delete_sub_product'
					);
					?>
					<tr>
						<td><code><?php echo esc_html( $path ); ?></code></td>
						<td><?php echo esc_html( $display_first ); ?></td>
						<td>
							<?php
							$out = array();
							foreach ( (array) $prices as $cur => $val ) {
								$out[] = $cur . ' ' . number_format_i18n( (float) $val, 2 );
							}
							echo esc_html( implode( ', ', $out ) );
							?>
						</td>
						<td><?php echo esc_html( $interval ); ?></td>
						<td>
							<?php WP_FastSpring_Admin_Resource_Base::render_view_button( $p ); ?>
							<a class="button button-small" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Delete subscription product?', 'wp-fastspring' ); ?>');"><?php esc_html_e( 'Delete', 'wp-fastspring' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}
}
