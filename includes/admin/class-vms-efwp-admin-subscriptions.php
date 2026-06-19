<?php
/**
 * Subscriptions screen.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Subscriptions.
 */
class VMS_EFWP_Admin_Subscriptions {

	/**
	 * Render the screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab  = VMS_EFWP_Admin_Resource_Base::get_filter_key( 'tab', 'stored' );
		$tabs = array(
			'stored'     => __( 'Stored', 'vms-elements-fastspring-woo-payment' ),
			'api_search' => __( 'API Search', 'vms-elements-fastspring-woo-payment' ),
			'lookup'     => __( 'Lookup', 'vms-elements-fastspring-woo-payment' ),
			'catalog'    => __( 'Subscription Products', 'vms-elements-fastspring-woo-payment' ),
		);
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'stored';
		}
		$base = admin_url( 'admin.php?page=vms-efwp-subscriptions' );

		echo '<div class="wrap vefwp-wrap">';
		VMS_EFWP_Admin_Resource_Base::render_header(
			__( 'Subscriptions', 'vms-elements-fastspring-woo-payment' ),
			'catalog' === $tab
				? __( 'Subscription products in your FastSpring catalog.', 'vms-elements-fastspring-woo-payment' )
				: __( 'Manage customer subscriptions from webhooks and the FastSpring API.', 'vms-elements-fastspring-woo-payment' )
		);

		if ( VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			if ( 'lookup' === $tab && VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_update_subscription' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_update_subscription' ) ) {
				self::handle_update_subscription();
			}
		}

		VMS_EFWP_Admin_Resource_Base::render_nav_tabs( $tabs, $tab, $base );

		if ( 'catalog' === $tab ) {
			self::render_catalog();
		} elseif ( 'lookup' === $tab ) {
			self::render_lookup();
		} elseif ( 'api_search' === $tab ) {
			self::render_api_search();
		} else {
			self::render_stored();
			self::render_subscription_entries();
			self::render_subscription_history();
		}

		VMS_EFWP_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}

	/**
	 * Handle POST subscription update.
	 */
	private static function handle_update_subscription() {
		$id      = VMS_EFWP_Admin_Resource_Base::post_text( 'subscription_id' );
		$changes = array();

		$product = VMS_EFWP_Admin_Resource_Base::post_text( 'product' );
		if ( $product ) {
			$changes['product'] = $product;
		}

		$quantity = VMS_EFWP_Admin_Resource_Base::post_text( 'quantity' );
		if ( '' !== $quantity ) {
			$changes['quantity'] = max( 0, (int) $quantity );
		}

		$next = VMS_EFWP_Admin_Resource_Base::post_text( 'next' );
		if ( $next ) {
			$changes['next'] = $next;
		}

		if ( VMS_EFWP_Admin_Resource_Base::post_text( 'resume_canceled' ) ) {
			$changes['deactivation'] = null;
		}

		if ( empty( $changes ) ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html__( 'No subscription changes were provided.', 'vms-elements-fastspring-woo-payment' ) );
			return;
		}

		VMS_EFWP_Admin_Resource_Base::render_result_notice( vms_efwp()->api->update_subscription( $id, $changes ) );
	}

	/**
	 * Stored subscriptions from local DB.
	 */
	private static function render_stored() {
		$page     = max( 1, VMS_EFWP_Admin_Resource_Base::get_filter_int( 'paged', 1 ) );
		$status   = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'status' );
		$search   = VMS_EFWP_Admin_Resource_Base::get_filter_text( 's' );
		$per_page = 20;

		$result = VMS_EFWP_Data_Store::get_subscriptions(
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
			<input type="hidden" name="page" value="vms-efwp-subscriptions" />
			<input type="hidden" name="tab" value="stored" />
			<select name="status">
				<option value=""><?php esc_html_e( 'All statuses', 'vms-elements-fastspring-woo-payment' ); ?></option>
				<?php foreach ( array( 'active', 'paused', 'overdue', 'canceled', 'deactivated', 'trial' ) as $s ) : ?>
					<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by email, product...', 'vms-elements-fastspring-woo-payment' ); ?>" />
			<button class="button"><?php esc_html_e( 'Filter', 'vms-elements-fastspring-woo-payment' ); ?></button>
		</form>

		<table class="widefat striped vefwp-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Subscription', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Product', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Price', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Interval', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Next charge', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Status', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'vms-elements-fastspring-woo-payment' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $result['rows'] ) ) : ?>
				<tr><td colspan="8"><?php esc_html_e( 'No subscriptions tracked yet.', 'vms-elements-fastspring-woo-payment' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $result['rows'] as $row ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( $row['fs_subscription_id'] ); ?></strong>
							<?php if ( (int) $row['is_test'] ) : ?>
								<span class="vefwp-badge vefwp-badge--warning"><?php esc_html_e( 'TEST', 'vms-elements-fastspring-woo-payment' ); ?></span>
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
									__( '%1$d %2$s', 'vms-elements-fastspring-woo-payment' ),
									(int) $row['interval_length'],
									$row['interval_unit'] ? $row['interval_unit'] : '-'
								)
							);
							?>
						</td>
						<td><?php echo esc_html( $row['next_charge'] ? mysql2date( get_option( 'date_format' ), $row['next_charge'] ) : '-' ); ?></td>
						<td><span class="vefwp-status vefwp-status--<?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( $row['status'] ); ?></span></td>
						<td><?php self::render_row_actions( $row['fs_subscription_id'], $row['status'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>

		<?php
		if ( $total_pages > 1 ) {
			$base_url = add_query_arg(
				array(
					'page'   => 'vms-efwp-subscriptions',
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
	 * API search tab.
	 */
	private static function render_api_search() {
		$api        = vms_efwp()->api;
		$account_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'account_id' );
		$status     = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'status' );
		$scope      = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'scope', 'all' );
		$products   = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'products' );
		$event      = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'event' );
		$begin      = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'begin' );
		$end        = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'end' );
		$page       = max( 1, VMS_EFWP_Admin_Resource_Base::get_filter_int( 'paged', 1 ) );

		$params = array_filter(
			array(
				'accountId' => $account_id,
				'status'    => $status,
				'scope'     => in_array( $scope, array( 'all', 'live', 'test' ), true ) ? $scope : 'all',
				'products'  => $products,
				'event'     => $event,
				'begin'     => $begin,
				'end'       => $end,
			)
		);

		$result        = $api->list_subscriptions( $params );
		$subscriptions = array();
		$has_next      = false;

		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
		} else {
			if ( ! empty( $result['subscriptions'] ) && is_array( $result['subscriptions'][0] ?? null ) ) {
				$subscriptions = $api->parse_subscriptions( $result );
			} else {
				$subscriptions = $api->hydrate_subscriptions( $api->extract_subscription_ids( $result ) );
			}

			$paged         = VMS_EFWP_API::paginate_items( $subscriptions, $page, 50 );
			$subscriptions = $paged['items'];
			$has_next      = $paged['has_next'];
		}
		?>
		<form method="get" class="vefwp-filters">
			<input type="hidden" name="page" value="vms-efwp-subscriptions" />
			<input type="hidden" name="tab" value="api_search" />
			<label><?php esc_html_e( 'Account ID', 'vms-elements-fastspring-woo-payment' ); ?> <input type="text" name="account_id" value="<?php echo esc_attr( $account_id ); ?>" class="regular-text" /></label>
			<select name="status">
				<option value=""><?php esc_html_e( 'All statuses', 'vms-elements-fastspring-woo-payment' ); ?></option>
				<?php foreach ( array( 'active', 'canceled', 'deactivated', 'overdue', 'trial' ) as $s ) : ?>
					<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<select name="scope">
				<option value="all" <?php selected( $scope, 'all' ); ?>><?php esc_html_e( 'All', 'vms-elements-fastspring-woo-payment' ); ?></option>
				<option value="live" <?php selected( $scope, 'live' ); ?>><?php esc_html_e( 'Live', 'vms-elements-fastspring-woo-payment' ); ?></option>
				<option value="test" <?php selected( $scope, 'test' ); ?>><?php esc_html_e( 'Test', 'vms-elements-fastspring-woo-payment' ); ?></option>
			</select>
			<label><?php esc_html_e( 'Products', 'vms-elements-fastspring-woo-payment' ); ?> <input type="text" name="products" value="<?php echo esc_attr( $products ); ?>" /></label>
			<select name="event">
				<option value=""><?php esc_html_e( 'Any event', 'vms-elements-fastspring-woo-payment' ); ?></option>
				<?php foreach ( array( 'created', 'charged', 'canceled', 'deactivated', 'trialstarted', 'trialended' ) as $ev ) : ?>
					<option value="<?php echo esc_attr( $ev ); ?>" <?php selected( $event, $ev ); ?>><?php echo esc_html( $ev ); ?></option>
				<?php endforeach; ?>
			</select>
			<label><?php esc_html_e( 'Begin', 'vms-elements-fastspring-woo-payment' ); ?> <input type="date" name="begin" value="<?php echo esc_attr( $begin ); ?>" /></label>
			<label><?php esc_html_e( 'End', 'vms-elements-fastspring-woo-payment' ); ?> <input type="date" name="end" value="<?php echo esc_attr( $end ); ?>" /></label>
			<button class="button button-primary"><?php esc_html_e( 'Search API', 'vms-elements-fastspring-woo-payment' ); ?></button>
		</form>

		<table class="widefat striped vefwp-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Subscription', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Product', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'State', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Next charge', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'vms-elements-fastspring-woo-payment' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $subscriptions ) ) : ?>
				<?php VMS_EFWP_Admin_Resource_Base::render_empty_row( __( 'No subscriptions found.', 'vms-elements-fastspring-woo-payment' ), 6 ); ?>
			<?php else : ?>
				<?php foreach ( $subscriptions as $sub ) : ?>
					<?php
					$sub_id = $sub['id'] ?? $sub['subscription'] ?? '';
					$state  = $sub['state'] ?? $sub['status'] ?? '';
					$email  = $sub['customer']['email'] ?? $sub['email'] ?? '';
					?>
					<tr>
						<td>
							<strong><?php echo esc_html( $sub_id ); ?></strong>
							<?php if ( empty( $sub['live'] ) ) : ?>
								<span class="vefwp-badge vefwp-badge--warning"><?php esc_html_e( 'TEST', 'vms-elements-fastspring-woo-payment' ); ?></span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $email ); ?></td>
						<td><?php echo esc_html( $sub['product'] ?? '' ); ?></td>
						<td><span class="vefwp-status vefwp-status--<?php echo esc_attr( sanitize_key( (string) $state ) ); ?>"><?php echo esc_html( $state ); ?></span></td>
						<td><?php echo esc_html( $sub['nextChargeDateDisplay'] ?? ( $sub['nextChargeDate'] ?? '-' ) ); ?></td>
						<td class="vefwp-row-actions">
							<?php VMS_EFWP_Admin_Resource_Base::render_view_button( $sub ); ?>
							<?php self::render_row_actions( $sub_id, $state ); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $page > 1 || $has_next ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php
					$base_args = array(
						'page'       => 'vms-efwp-subscriptions',
						'tab'        => 'api_search',
						'account_id' => $account_id,
						'status'     => $status,
						'scope'      => $scope,
						'products'   => $products,
						'event'      => $event,
						'begin'      => $begin,
						'end'        => $end,
					);
					if ( $page > 1 ) {
						printf(
							'<a class="button" href="%s">&larr; %s</a> ',
							esc_url( add_query_arg( array_merge( $base_args, array( 'paged' => $page - 1 ) ), admin_url( 'admin.php' ) ) ),
							esc_html__( 'Previous', 'vms-elements-fastspring-woo-payment' )
						);
					}
					if ( $has_next ) {
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
	 * Lookup a single subscription and allow updates.
	 */
	private static function render_lookup() {
		$subscription_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'subscription_id' );
		VMS_EFWP_Admin_Resource_Base::render_lookup_form(
			'vms-efwp-subscriptions',
			'subscription_id',
			__( 'Subscription ID', 'vms-elements-fastspring-woo-payment' ),
			__( 'FastSpring subscription ID', 'vms-elements-fastspring-woo-payment' ),
			$subscription_id,
			array( 'tab' => 'lookup' )
		);

		if ( ! $subscription_id ) {
			return;
		}

		$api          = vms_efwp()->api;
		$subscription = $api->parse_subscription( $api->get_subscription( $subscription_id ) );
		if ( is_wp_error( $subscription ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $subscription );
			return;
		}

		VMS_EFWP_Admin_Resource_Base::render_api_detail_card( $subscription, __( 'Subscription details', 'vms-elements-fastspring-woo-payment' ) );

		$state = $subscription['state'] ?? '';
		?>
		<div class="vefwp-card vefwp-card--wide">
			<h2><?php esc_html_e( 'Update subscription', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_update_subscription' ); ?>
				<input type="hidden" name="wpfs_update_subscription" value="1" />
				<input type="hidden" name="subscription_id" value="<?php echo esc_attr( $subscription_id ); ?>" />
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Product path', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="product" class="regular-text" value="<?php echo esc_attr( $subscription['product'] ?? '' ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Quantity', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" min="0" name="quantity" class="regular-text" value="<?php echo esc_attr( (string) ( $subscription['quantity'] ?? 1 ) ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Next charge date (YYYY-MM-DD)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="date" name="next" class="regular-text" value="<?php echo esc_attr( $subscription['nextChargeDateDisplayISO8601'] ?? '' ); ?>" /></label></p>
					<?php if ( 'canceled' === $state ) : ?>
						<p><label><input type="checkbox" name="resume_canceled" value="1" /> <?php esc_html_e( 'Resume canceled subscription (clear deactivation)', 'vms-elements-fastspring-woo-payment' ); ?></label></p>
					<?php endif; ?>
				</div>
				<p><button class="button button-primary"><?php esc_html_e( 'Update subscription', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
			</form>
		</div>

		<p class="vefwp-row-actions">
			<button type="button" class="button vefwp-sync-sub" data-id="<?php echo esc_attr( $subscription_id ); ?>"><?php esc_html_e( 'Save to local database', 'vms-elements-fastspring-woo-payment' ); ?></button>
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-subscriptions', 'tab' => 'stored', 'entries' => $subscription_id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Charge history', 'vms-elements-fastspring-woo-payment' ); ?></a>
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-subscriptions', 'tab' => 'stored', 'history' => $subscription_id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Plan changes', 'vms-elements-fastspring-woo-payment' ); ?></a>
			<?php self::render_row_actions( $subscription_id, $state, false ); ?>
		</p>
		<?php
	}

	/**
	 * Render action buttons for a subscription row.
	 *
	 * @param string $subscription_id Subscription ID.
	 * @param string $status          Current status/state.
	 * @param bool   $include_nav     Include history/lookup links.
	 */
	private static function render_row_actions( $subscription_id, $status, $include_nav = true ) {
		$status = sanitize_key( (string) $status );
		?>
		<div class="vefwp-row-actions">
			<button type="button" class="button button-small vefwp-sync-sub" data-id="<?php echo esc_attr( $subscription_id ); ?>"><?php esc_html_e( 'Sync', 'vms-elements-fastspring-woo-payment' ); ?></button>
			<?php if ( $include_nav ) : ?>
				<a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-subscriptions', 'tab' => 'stored', 'entries' => $subscription_id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'History', 'vms-elements-fastspring-woo-payment' ); ?></a>
				<a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-subscriptions', 'tab' => 'lookup', 'subscription_id' => $subscription_id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Lookup', 'vms-elements-fastspring-woo-payment' ); ?></a>
			<?php endif; ?>
			<?php if ( in_array( $status, array( 'active', 'trial', 'overdue' ), true ) ) : ?>
				<button type="button" class="button button-small vefwp-pause-sub" data-id="<?php echo esc_attr( $subscription_id ); ?>"><?php esc_html_e( 'Pause', 'vms-elements-fastspring-woo-payment' ); ?></button>
				<button type="button" class="button button-small vefwp-charge-sub" data-id="<?php echo esc_attr( $subscription_id ); ?>"><?php esc_html_e( 'Charge', 'vms-elements-fastspring-woo-payment' ); ?></button>
				<button type="button" class="button button-small vefwp-cancel-sub" data-id="<?php echo esc_attr( $subscription_id ); ?>"><?php esc_html_e( 'Cancel', 'vms-elements-fastspring-woo-payment' ); ?></button>
				<button type="button" class="button button-small vefwp-cancel-sub" data-id="<?php echo esc_attr( $subscription_id ); ?>" data-immediate="1"><?php esc_html_e( 'Cancel now', 'vms-elements-fastspring-woo-payment' ); ?></button>
			<?php elseif ( 'paused' === $status ) : ?>
				<button type="button" class="button button-small vefwp-resume-sub" data-id="<?php echo esc_attr( $subscription_id ); ?>"><?php esc_html_e( 'Resume', 'vms-elements-fastspring-woo-payment' ); ?></button>
			<?php elseif ( 'canceled' === $status ) : ?>
				<button type="button" class="button button-small vefwp-uncancel-sub" data-id="<?php echo esc_attr( $subscription_id ); ?>"><?php esc_html_e( 'Uncancel', 'vms-elements-fastspring-woo-payment' ); ?></button>
			<?php elseif ( 'deactivated' === $status ) : ?>
				<button type="button" class="button button-small vefwp-convert-sub" data-id="<?php echo esc_attr( $subscription_id ); ?>"><?php esc_html_e( 'Convert trial', 'vms-elements-fastspring-woo-payment' ); ?></button>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Charge history panel.
	 */
	private static function render_subscription_entries() {
		$subscription_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'entries' );
		if ( ! $subscription_id || ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			return;
		}

		$api     = vms_efwp()->api;
		$entries = $api->parse_subscription_entries( $api->get_subscription_entries( $subscription_id ) );
		if ( is_wp_error( $entries ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $entries );
			return;
		}
		?>
		<div class="vefwp-card vefwp-card--wide">
			<div class="vefwp-card__head">
				<h2>
					<?php
					printf(
						/* translators: %s: subscription id */
						esc_html__( 'Charge history for %s', 'vms-elements-fastspring-woo-payment' ),
						esc_html( $subscription_id )
					);
					?>
				</h2>
				<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-subscriptions', 'tab' => 'stored' ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Close', 'vms-elements-fastspring-woo-payment' ); ?></a>
			</div>
			<table class="widefat striped vefwp-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Entry', 'vms-elements-fastspring-woo-payment' ); ?></th>
						<th><?php esc_html_e( 'Order', 'vms-elements-fastspring-woo-payment' ); ?></th>
						<th><?php esc_html_e( 'Period', 'vms-elements-fastspring-woo-payment' ); ?></th>
						<th><?php esc_html_e( 'Total', 'vms-elements-fastspring-woo-payment' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $entries ) ) : ?>
					<?php VMS_EFWP_Admin_Resource_Base::render_empty_row( __( 'No charge entries found.', 'vms-elements-fastspring-woo-payment' ), 5 ); ?>
				<?php else : ?>
					<?php foreach ( $entries as $entry ) : ?>
						<?php
						$order = isset( $entry['order'] ) && is_array( $entry['order'] ) ? $entry['order'] : array();
						?>
						<tr>
							<td><code><?php echo esc_html( $entry['id'] ?? '' ); ?></code></td>
							<td><code><?php echo esc_html( $order['reference'] ?? ( $order['id'] ?? ( $order['order'] ?? '' ) ) ); ?></code></td>
							<td><?php echo esc_html( trim( ( $entry['beginPeriodDate'] ?? '' ) . ' – ' . ( $entry['endPeriodDate'] ?? '' ), ' –' ) ); ?></td>
							<td><?php echo esc_html( ( $order['currency'] ?? '' ) . ' ' . number_format_i18n( (float) ( $order['total'] ?? 0 ), 2 ) ); ?></td>
							<td><?php VMS_EFWP_Admin_Resource_Base::render_view_button( $entry ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Plan change history panel.
	 */
	private static function render_subscription_history() {
		$subscription_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'history' );
		if ( ! $subscription_id || ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			return;
		}

		$result  = vms_efwp()->api->get_subscription_history( $subscription_id );
		$changes = array();
		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
			return;
		}
		$changes = isset( $result['changes'] ) && is_array( $result['changes'] ) ? $result['changes'] : array();
		?>
		<div class="vefwp-card vefwp-card--wide">
			<div class="vefwp-card__head">
				<h2>
					<?php
					printf(
						/* translators: %s: subscription id */
						esc_html__( 'Plan changes for %s', 'vms-elements-fastspring-woo-payment' ),
						esc_html( $subscription_id )
					);
					?>
				</h2>
				<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-subscriptions', 'tab' => 'stored' ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Close', 'vms-elements-fastspring-woo-payment' ); ?></a>
			</div>
			<table class="widefat striped vefwp-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Change', 'vms-elements-fastspring-woo-payment' ); ?></th>
						<th><?php esc_html_e( 'Product', 'vms-elements-fastspring-woo-payment' ); ?></th>
						<th><?php esc_html_e( 'Type', 'vms-elements-fastspring-woo-payment' ); ?></th>
						<th><?php esc_html_e( 'When', 'vms-elements-fastspring-woo-payment' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $changes ) ) : ?>
					<?php VMS_EFWP_Admin_Resource_Base::render_empty_row( __( 'No plan changes found.', 'vms-elements-fastspring-woo-payment' ), 5 ); ?>
				<?php else : ?>
					<?php foreach ( $changes as $change ) : ?>
						<tr>
							<td><code><?php echo esc_html( $change['changeId'] ?? '' ); ?></code></td>
							<td><?php echo esc_html( $change['itemProductPath'] ?? '' ); ?></td>
							<td><?php echo esc_html( $change['itemType'] ?? '' ); ?></td>
							<td><?php echo esc_html( isset( $change['insertTimestamp'] ) ? gmdate( 'Y-m-d H:i', (int) ( $change['insertTimestamp'] / 1000 ) ) : '' ); ?></td>
							<td><?php VMS_EFWP_Admin_Resource_Base::render_view_button( $change ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Subscription product catalog tab.
	 */
	private static function render_catalog() {
		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			return;
		}

		if ( VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_save_sub_product' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_save_sub_product' ) ) {
			$name = VMS_EFWP_Admin_Resource_Base::post_text( 'display_name' );
			$path = VMS_EFWP_Admin_Resource_Base::sanitize_product_path(
				VMS_EFWP_Admin_Resource_Base::post_text( 'product_path' ),
				$name
			);
			$cur     = strtoupper( substr( preg_replace( '/[^A-Za-z]/', '', VMS_EFWP_Admin_Resource_Base::post_text( 'currency', 'USD' ) ), 0, 3 ) );
			$price   = VMS_EFWP_Admin_Resource_Base::post_float( 'price' );
			$unit    = VMS_EFWP_Admin_Resource_Base::post_text( 'interval_unit', 'month' );
			$length  = max( 1, VMS_EFWP_Admin_Resource_Base::post_int( 'interval_length', 1 ) );
			$summary = VMS_EFWP_Admin_Resource_Base::post_textarea( 'summary' );
			$trial   = VMS_EFWP_Admin_Resource_Base::post_int( 'trial_days', 0 );

			$pricing = array(
				'price'          => array( $cur => $price ),
				'interval'       => $unit,
				'intervalLength' => $length,
			);
			if ( $trial > 0 ) {
				$pricing['trial']            = $trial;
				$pricing['paymentCollected'] = false;
				$pricing['paidTrial']        = false;
			}

			$payload = array(
				'product'     => $path,
				'display'     => array( 'en' => $name ),
				'description' => array( 'summary' => array( 'en' => $summary ) ),
				'sku'         => $path,
				'pricing'     => $pricing,
			);
			if ( ! $path ) {
				VMS_EFWP_Admin_Resource_Base::render_result_notice(
					new WP_Error( 'vms_efwp_invalid_product', __( 'A product path is required.', 'vms-elements-fastspring-woo-payment' ) )
				);
			} else {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( vms_efwp()->api->upsert_product( $payload ) );
			}
		}

		$delete_path = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'delete' );
		if ( $delete_path ) {
			check_admin_referer( 'wpfs_delete_sub_product' );
			VMS_EFWP_Admin_Resource_Base::render_result_notice( vms_efwp()->api->delete_product( $delete_path ) );
		}

		$page     = max( 1, VMS_EFWP_Admin_Resource_Base::get_filter_int( 'paged', 1 ) );
		$per_page = 50;
		$list     = vms_efwp()->api->list_products();
		$paths    = array();
		$has_next = false;

		if ( is_wp_error( $list ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $list );
		} else {
			$all_paths = vms_efwp()->api->extract_product_paths( $list );
			$paged     = VMS_EFWP_API::paginate_items( $all_paths, $page, $per_page );
			$paths     = $paged['items'];
			$has_next  = $paged['has_next'];
		}

		$products = array();
		if ( ! empty( $paths ) ) {
			$details = vms_efwp()->api->get_products( $paths );
			if ( is_wp_error( $details ) ) {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $details );
			} else {
				$products = array_values(
					array_filter(
						vms_efwp()->api->parse_products( $details ),
						static function ( $p ) {
							return VMS_EFWP_Admin_Products::is_subscription_product( $p );
						}
					)
				);
			}
		}
		?>

		<p>
			<button type="button" class="button button-primary" data-vefwp-open-form="save-sub-product">
				<?php esc_html_e( 'New subscription product', 'vms-elements-fastspring-woo-payment' ); ?>
			</button>
		</p>

		<div class="vefwp-card" data-vefwp-form="save-sub-product" hidden>
			<h2><?php esc_html_e( 'Create subscription product', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<form method="post" data-vefwp-slug-form>
				<?php wp_nonce_field( 'wpfs_save_sub_product' ); ?>
				<input type="hidden" name="wpfs_save_sub_product" value="1" />
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Display name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" required name="display_name" class="regular-text" data-vefwp-slug-source autocomplete="off" /></label></p>
					<p>
						<label><?php esc_html_e( 'Product path (slug)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" required name="product_path" class="regular-text" placeholder="pro-monthly" data-vefwp-slug-target autocomplete="off" /></label>
						<span class="description"><?php esc_html_e( 'Auto-generated from the display name. Edit if needed — it will be converted to a URL-safe slug.', 'vms-elements-fastspring-woo-payment' ); ?></span>
					</p>
					<p><label><?php esc_html_e( 'Currency', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="currency" maxlength="3" value="USD" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Price per interval', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" step="0.01" required name="price" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Interval unit', 'vms-elements-fastspring-woo-payment' ); ?><br />
						<select name="interval_unit">
							<option value="day"><?php esc_html_e( 'Day', 'vms-elements-fastspring-woo-payment' ); ?></option>
							<option value="week"><?php esc_html_e( 'Week', 'vms-elements-fastspring-woo-payment' ); ?></option>
							<option value="month" selected><?php esc_html_e( 'Month', 'vms-elements-fastspring-woo-payment' ); ?></option>
							<option value="year"><?php esc_html_e( 'Year', 'vms-elements-fastspring-woo-payment' ); ?></option>
						</select>
					</label></p>
					<p><label><?php esc_html_e( 'Interval length', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" min="1" name="interval_length" class="regular-text" value="1" /></label></p>
					<p><label><?php esc_html_e( 'Trial days (optional)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" min="0" name="trial_days" class="regular-text" value="0" /></label></p>
					<p class="vefwp-grid--full"><label><?php esc_html_e( 'Summary', 'vms-elements-fastspring-woo-payment' ); ?><br /><textarea name="summary" rows="2" class="regular-text"></textarea></label></p>
				</div>
				<p>
					<button class="button button-primary"><?php esc_html_e( 'Save subscription product', 'vms-elements-fastspring-woo-payment' ); ?></button>
					<button type="button" class="button" data-vefwp-close-form="save-sub-product"><?php esc_html_e( 'Cancel', 'vms-elements-fastspring-woo-payment' ); ?></button>
				</p>
			</form>
		</div>

		<table class="widefat striped vefwp-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Path', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Display', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Price', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Interval', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'vms-elements-fastspring-woo-payment' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $products ) ) : ?>
				<tr><td colspan="5"><em><?php esc_html_e( 'No subscription products in your FastSpring catalog yet.', 'vms-elements-fastspring-woo-payment' ); ?></em></td></tr>
			<?php else : ?>
				<?php foreach ( $products as $p ) :
					$path          = $p['product'] ?? '';
					$display       = $p['display'] ?? array();
					$display_first = is_array( $display ) ? reset( $display ) : (string) $display;
					$pricing       = $p['pricing'] ?? array();
					$prices        = $pricing['price'] ?? array();
					$interval      = ( isset( $pricing['intervalLength'] ) ? (int) $pricing['intervalLength'] : 1 ) . ' ' . ( $pricing['interval'] ?? $pricing['intervalUnit'] ?? 'month' );
					$delete_url    = wp_nonce_url(
						add_query_arg( array( 'page' => 'vms-efwp-subscriptions', 'tab' => 'catalog', 'delete' => $path ), admin_url( 'admin.php' ) ),
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
							<?php VMS_EFWP_Admin_Resource_Base::render_view_button( $p ); ?>
							<a class="button button-small" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Delete subscription product?', 'vms-elements-fastspring-woo-payment' ); ?>');"><?php esc_html_e( 'Delete', 'vms-elements-fastspring-woo-payment' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $page > 1 || $has_next ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php
					if ( $page > 1 ) {
						printf(
							'<a class="button" href="%s">&larr; %s</a> ',
							esc_url( add_query_arg( array( 'page' => 'vms-efwp-subscriptions', 'tab' => 'catalog', 'paged' => $page - 1 ), admin_url( 'admin.php' ) ) ),
							esc_html__( 'Previous', 'vms-elements-fastspring-woo-payment' )
						);
					}
					if ( $has_next ) {
						printf(
							'<a class="button" href="%s">%s &rarr;</a>',
							esc_url( add_query_arg( array( 'page' => 'vms-efwp-subscriptions', 'tab' => 'catalog', 'paged' => $page + 1 ), admin_url( 'admin.php' ) ) ),
							esc_html__( 'Next', 'vms-elements-fastspring-woo-payment' )
						);
					}
					?>
				</div>
			</div>
		<?php endif;
	}
}
