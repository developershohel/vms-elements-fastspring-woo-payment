<?php
/**
 * Tools screen (logs, manual sync).
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Tools.
 */
class VMS_EFWP_Admin_Tools {

	/**
	 * Handle bulk WooCommerce → FastSpring product sync.
	 */
	public static function handle_sync_all_products() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) );
		}
		check_admin_referer( 'vms_efwp_sync_all_products' );

		$summary = function_exists( 'vms_efwp' ) && vms_efwp()->product_sync
			? vms_efwp()->product_sync->sync_all_products()
			: array( 'synced' => 0, 'failed' => 0, 'skipped' => 0, 'errors' => array( __( 'Product sync is unavailable.', 'vms-elements-fastspring-woo-payment' ) ) );

		$message = sprintf(
			/* translators: 1: synced count 2: failed count 3: skipped count */
			__( 'Bulk sync finished. Synced: %1$d, failed: %2$d, skipped: %3$d.', 'vms-elements-fastspring-woo-payment' ),
			(int) $summary['synced'],
			(int) $summary['failed'],
			(int) $summary['skipped']
		);
		if ( ! empty( $summary['errors'] ) ) {
			$message .= ' ' . implode( ' | ', array_slice( (array) $summary['errors'], 0, 3 ) );
		}

		set_transient( 'vms_efwp_tools_notice', $message, 60 );
		wp_safe_redirect( admin_url( 'admin.php?page=vms-efwp-tools' ) );
		exit;
	}

	/**
	 * Render tools.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		global $wpdb;

		$notice = get_transient( 'vms_efwp_tools_notice' );
		if ( $notice ) {
			delete_transient( 'vms_efwp_tools_notice' );
			printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $notice ) );
		}

		$log_table    = VMS_EFWP_Install::table_name( 'log' );
		$events_table = VMS_EFWP_Install::table_name( 'events' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$logs = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT * FROM {$log_table} ORDER BY id DESC LIMIT 100",
			ARRAY_A
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$events = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT * FROM {$events_table} ORDER BY id DESC LIMIT 50",
			ARRAY_A
		);
		?>
		<div class="wrap vefwp-wrap">
			<?php
			VMS_EFWP_Admin_Resource_Base::render_header(
				__( 'Tools', 'vms-elements-fastspring-woo-payment' ),
				__( 'Diagnostics, bulk sync, and local webhook/log data.', 'vms-elements-fastspring-woo-payment' )
			);
			?>

			<div class="vefwp-card vefwp-card--wide">
				<h2><?php esc_html_e( 'Maintenance actions', 'vms-elements-fastspring-woo-payment' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Run one-off tasks against FastSpring and WooCommerce.', 'vms-elements-fastspring-woo-payment' ); ?></p>
				<p>
					<button type="button" class="button" id="vefwp-test-connection"><?php esc_html_e( 'Test API connection', 'vms-elements-fastspring-woo-payment' ); ?></button>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;">
						<?php wp_nonce_field( 'vms_efwp_sync_all_products' ); ?>
						<input type="hidden" name="action" value="vms_efwp_sync_all_products" />
						<button type="submit" class="button button-primary" onclick="return confirm('<?php esc_attr_e( 'Sync all published WooCommerce products to FastSpring?', 'vms-elements-fastspring-woo-payment' ); ?>');"><?php esc_html_e( 'Bulk sync WooCommerce products', 'vms-elements-fastspring-woo-payment' ); ?></button>
					</form>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=vms-efwp-events' ) ); ?>"><?php esc_html_e( 'View all webhook events', 'vms-elements-fastspring-woo-payment' ); ?></a>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=vms-efwp-settings' ) ); ?>"><?php esc_html_e( 'Open settings', 'vms-elements-fastspring-woo-payment' ); ?></a>
				</p>
			</div>

			<div class="vefwp-card vefwp-card--wide">
				<h2><?php esc_html_e( 'Recent webhook events', 'vms-elements-fastspring-woo-payment' ); ?></h2>
				<table class="widefat striped vefwp-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Event ID', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Type', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Live', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Processed', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Created', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Error', 'vms-elements-fastspring-woo-payment' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php if ( empty( $events ) ) : ?>
						<tr><td colspan="6"><?php esc_html_e( 'No events received yet.', 'vms-elements-fastspring-woo-payment' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $events as $e ) : ?>
							<tr>
								<td><code><?php echo esc_html( $e['event_id'] ); ?></code></td>
								<td><?php echo esc_html( $e['event_type'] ); ?></td>
								<td><?php echo (int) $e['live'] ? '<span class="vefwp-badge vefwp-badge--ok">LIVE</span>' : '<span class="vefwp-badge vefwp-badge--warning">TEST</span>'; ?></td>
								<td><?php echo (int) $e['processed'] ? esc_html__( 'Yes', 'vms-elements-fastspring-woo-payment' ) : esc_html__( 'No', 'vms-elements-fastspring-woo-payment' ); ?></td>
								<td><?php echo esc_html( $e['created_at'] ); ?></td>
								<td><?php echo esc_html( $e['error_message'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					</tbody>
				</table>
			</div>

			<div class="vefwp-card vefwp-card--wide">
				<h2><?php esc_html_e( 'Recent log entries', 'vms-elements-fastspring-woo-payment' ); ?></h2>
				<table class="widefat striped vefwp-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Level', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Channel', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Message', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'When', 'vms-elements-fastspring-woo-payment' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php if ( empty( $logs ) ) : ?>
						<tr><td colspan="4"><?php esc_html_e( 'No log entries yet.', 'vms-elements-fastspring-woo-payment' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $logs as $log ) : ?>
							<tr>
								<td><span class="vefwp-status vefwp-status--<?php echo esc_attr( $log['level'] ); ?>"><?php echo esc_html( $log['level'] ); ?></span></td>
								<td><?php echo esc_html( $log['channel'] ); ?></td>
								<td><?php echo esc_html( $log['message'] ); ?></td>
								<td><?php echo esc_html( $log['created_at'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
}
