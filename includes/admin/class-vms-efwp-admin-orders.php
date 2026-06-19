<?php
/**
 * Orders screen.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Orders.
 */
class VMS_EFWP_Admin_Orders {

	/**
	 * Render orders.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab  = VMS_EFWP_Admin_Resource_Base::get_filter_key( 'tab', 'stored' );
		$tabs = array(
			'stored'     => __( 'Stored Orders', 'vms-elements-fastspring-woo-payment' ),
			'api_search' => __( 'API Search', 'vms-elements-fastspring-woo-payment' ),
			'lookup'     => __( 'Lookup', 'vms-elements-fastspring-woo-payment' ),
		);
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'stored';
		}

		$base = admin_url( 'admin.php?page=vms-efwp-orders' );
		?>
		<div class="wrap vefwp-wrap">
			<?php
			VMS_EFWP_Admin_Resource_Base::render_header(
				__( 'Orders', 'vms-elements-fastspring-woo-payment' ),
				__( 'Orders received via webhooks and live FastSpring API lookups.', 'vms-elements-fastspring-woo-payment' )
			);
			VMS_EFWP_Admin_Resource_Base::render_nav_tabs( $tabs, $tab, $base );

			if ( 'api_search' === $tab ) {
				self::render_api_search();
			} elseif ( 'lookup' === $tab ) {
				self::render_lookup();
			} else {
				self::render_stored();
			}

			VMS_EFWP_Admin_Resource_Base::render_json_modal();
			?>
		</div>
		<?php
	}

	/**
	 * Tab: locally stored orders.
	 */
	private static function render_stored() {
		$page     = max( 1, VMS_EFWP_Admin_Resource_Base::get_filter_int( 'paged', 1 ) );
		$status   = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'status' );
		$search   = VMS_EFWP_Admin_Resource_Base::get_filter_text( 's' );
		$per_page = 20;

		$result = VMS_EFWP_Data_Store::get_orders(
			array(
				'page'     => $page,
				'per_page' => $per_page,
				'status'   => $status,
				'search'   => $search,
			)
		);

		$total_pages = max( 1, (int) ceil( $result['total'] / $per_page ) );
		?>
		<form method="get" class="vefwp-filters">
			<input type="hidden" name="page" value="vms-efwp-orders" />
			<input type="hidden" name="tab" value="stored" />
			<select name="status">
				<option value=""><?php esc_html_e( 'All statuses', 'vms-elements-fastspring-woo-payment' ); ?></option>
				<?php foreach ( array( 'completed', 'refunded', 'pending', 'cancelled' ) as $s ) : ?>
					<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by email, order id...', 'vms-elements-fastspring-woo-payment' ); ?>" />
			<button class="button"><?php esc_html_e( 'Filter', 'vms-elements-fastspring-woo-payment' ); ?></button>
		</form>

		<table class="widefat striped vefwp-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Order', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Total', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Status', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Date', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'WC', 'vms-elements-fastspring-woo-payment' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $result['rows'] ) ) : ?>
				<tr><td colspan="6"><?php esc_html_e( 'No orders yet. Make a sale on FastSpring or trigger a test webhook.', 'vms-elements-fastspring-woo-payment' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $result['rows'] as $row ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $row['fs_order_id'] ); ?></strong>
							<?php if ( ! empty( $row['fs_reference'] ) ) : ?>
								<div class="row-actions"><?php echo esc_html( $row['fs_reference'] ); ?></div>
							<?php endif; ?>
							<?php if ( (int) $row['is_test'] ) : ?>
								<span class="vefwp-badge vefwp-badge--warning"><?php esc_html_e( 'TEST', 'vms-elements-fastspring-woo-payment' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php echo esc_html( $row['customer_name'] ); ?>
							<div class="row-actions"><?php echo esc_html( $row['email'] ); ?></div>
						</td>
						<td><?php echo esc_html( $row['currency'] . ' ' . number_format_i18n( (float) $row['total'], 2 ) ); ?></td>
						<td><span class="vefwp-status vefwp-status--<?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( $row['status'] ); ?></span></td>
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
					'page'   => 'vms-efwp-orders',
					'tab'    => 'stored',
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

	/**
	 * Tab: search orders via FastSpring API.
	 */
	private static function render_api_search() {
		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			return;
		}

		$api      = vms_efwp()->api;
		$begin    = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'begin', gmdate( 'Y-m-d', strtotime( '-30 days' ) ) );
		$end      = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'end', gmdate( 'Y-m-d' ) );
		$products = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'products' );
		$scope    = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'scope', 'all' );
		$page     = max( 1, VMS_EFWP_Admin_Resource_Base::get_filter_int( 'paged', 1 ) );

		$params = array(
			'begin' => $begin,
			'end'   => $end,
			'page'  => $page,
			'limit' => 50,
		);
		if ( $products ) {
			$params['products'] = $products;
		}
		if ( in_array( $scope, array( 'all', 'live', 'test' ), true ) ) {
			$params['scope'] = $scope;
		}

		$result = $api->search_orders( $params );
		$orders = array();
		$next   = false;

		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$next = ! empty( $result['nextPage'] );
			if ( ! empty( $result['orders'] ) && is_array( $result['orders'] ) && is_array( $result['orders'][0] ?? null ) ) {
				$orders = $result['orders'];
			} else {
				$orders = $api->hydrate_orders( $api->extract_order_ids( $result ) );
			}
		}
		?>
		<form method="get" class="vefwp-filters">
			<input type="hidden" name="page" value="vms-efwp-orders" />
			<input type="hidden" name="tab" value="api_search" />
			<label><?php esc_html_e( 'Begin', 'vms-elements-fastspring-woo-payment' ); ?> <input type="date" name="begin" value="<?php echo esc_attr( $begin ); ?>" /></label>
			<label><?php esc_html_e( 'End', 'vms-elements-fastspring-woo-payment' ); ?> <input type="date" name="end" value="<?php echo esc_attr( $end ); ?>" /></label>
			<label><?php esc_html_e( 'Products', 'vms-elements-fastspring-woo-payment' ); ?> <input type="text" name="products" value="<?php echo esc_attr( $products ); ?>" placeholder="<?php esc_attr_e( 'product-path', 'vms-elements-fastspring-woo-payment' ); ?>" /></label>
			<select name="scope">
				<option value="all" <?php selected( $scope, 'all' ); ?>><?php esc_html_e( 'All', 'vms-elements-fastspring-woo-payment' ); ?></option>
				<option value="live" <?php selected( $scope, 'live' ); ?>><?php esc_html_e( 'Live only', 'vms-elements-fastspring-woo-payment' ); ?></option>
				<option value="test" <?php selected( $scope, 'test' ); ?>><?php esc_html_e( 'Test only', 'vms-elements-fastspring-woo-payment' ); ?></option>
			</select>
			<button class="button button-primary"><?php esc_html_e( 'Search API', 'vms-elements-fastspring-woo-payment' ); ?></button>
		</form>

		<table class="widefat striped vefwp-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Order', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Total', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Status', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Date', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'vms-elements-fastspring-woo-payment' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $orders ) ) : ?>
				<?php VMS_EFWP_Admin_Resource_Base::render_empty_row( __( 'No orders found for this search.', 'vms-elements-fastspring-woo-payment' ), 6 ); ?>
			<?php else : ?>
				<?php foreach ( $orders as $order ) : ?>
					<?php
					$order_id = $order['id'] ?? '';
					$customer = $order['customer'] ?? array();
					$email    = $customer['email'] ?? ( $order['email'] ?? '' );
					$name     = trim( ( $customer['first'] ?? $customer['firstName'] ?? '' ) . ' ' . ( $customer['last'] ?? $customer['lastName'] ?? '' ) );
					$status   = ! empty( $order['completed'] ) ? 'completed' : ( $order['status'] ?? 'pending' );
					$is_test  = ! empty( $order['test'] ) || ! empty( $order['isTest'] );
					?>
					<tr>
						<td>
							<strong><?php echo esc_html( $order_id ); ?></strong>
							<?php if ( $is_test ) : ?>
								<span class="vefwp-badge vefwp-badge--warning"><?php esc_html_e( 'TEST', 'vms-elements-fastspring-woo-payment' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php echo esc_html( $name ); ?>
							<div class="row-actions"><?php echo esc_html( $email ); ?></div>
						</td>
						<td><?php echo esc_html( ( $order['currency'] ?? '' ) . ' ' . number_format_i18n( (float) ( $order['total'] ?? 0 ), 2 ) ); ?></td>
						<td><span class="vefwp-status vefwp-status--<?php echo esc_attr( sanitize_key( (string) $status ) ); ?>"><?php echo esc_html( $status ); ?></span></td>
						<td><?php echo esc_html( $order['changed'] ?? ( $order['changedDisplay'] ?? '' ) ); ?></td>
						<td class="vefwp-row-actions">
							<?php VMS_EFWP_Admin_Resource_Base::render_view_button( $order ); ?>
							<?php if ( $order_id ) : ?>
								<button type="button" class="button button-small vefwp-sync-order" data-id="<?php echo esc_attr( $order_id ); ?>" data-test="<?php echo $is_test ? '1' : '0'; ?>"><?php esc_html_e( 'Save locally', 'vms-elements-fastspring-woo-payment' ); ?></button>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $page > 1 || $next ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php
					$base_args = array(
						'page'     => 'vms-efwp-orders',
						'tab'      => 'api_search',
						'begin'    => $begin,
						'end'      => $end,
						'products' => $products,
						'scope'    => $scope,
					);
					if ( $page > 1 ) {
						printf(
							'<a class="button" href="%s">&larr; %s</a> ',
							esc_url( add_query_arg( array_merge( $base_args, array( 'paged' => $page - 1 ) ), admin_url( 'admin.php' ) ) ),
							esc_html__( 'Previous', 'vms-elements-fastspring-woo-payment' )
						);
					}
					printf( '<span class="displaying-num">%s</span> ', esc_html( sprintf( __( 'Page %d', 'vms-elements-fastspring-woo-payment' ), $page ) ) );
					if ( $next ) {
						printf(
							'<a class="button" href="%s">%s &rarr;</a>',
							esc_url( add_query_arg( array_merge( $base_args, array( 'paged' => $page + 1 ) ), admin_url( 'admin.php' ) ) ),
							esc_html__( 'Next', 'vms-elements-fastspring-woo-payment' )
						);
					}
					?>
				</div>
			</div>
		<?php endif;
	}

	/**
	 * Tab: lookup a single order by ID.
	 */
	private static function render_lookup() {
		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			return;
		}

		$order_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'order_id' );
		VMS_EFWP_Admin_Resource_Base::render_lookup_form(
			'vms-efwp-orders',
			'order_id',
			__( 'Order ID', 'vms-elements-fastspring-woo-payment' ),
			__( 'FastSpring order ID', 'vms-elements-fastspring-woo-payment' ),
			$order_id,
			array( 'tab' => 'lookup' )
		);

		if ( ! $order_id ) {
			return;
		}

		$api   = vms_efwp()->api;
		$raw   = $api->get_order( $order_id );
		$order = $api->parse_order( $raw );

		if ( is_wp_error( $order ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $order );
			return;
		}

		VMS_EFWP_Admin_Resource_Base::render_api_detail_card( $order, __( 'Order details', 'vms-elements-fastspring-woo-payment' ) );

		$is_test = ! empty( $order['test'] ) || ! empty( $order['isTest'] );
		printf(
			'<p><button type="button" class="button button-primary vefwp-sync-order" data-id="%s" data-test="%s">%s</button></p>',
			esc_attr( $order_id ),
			$is_test ? '1' : '0',
			esc_html__( 'Save to local database', 'vms-elements-fastspring-woo-payment' )
		);
	}
}
