<?php
/**
 * Tools screen (logs, manual sync).
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Tools.
 */
class WP_FastSpring_Admin_Tools {

	/**
	 * Render tools.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		global $wpdb;

		$logs = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}fastspring_log ORDER BY id DESC LIMIT 100",
			ARRAY_A
		);

		$events = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}fastspring_events ORDER BY id DESC LIMIT 50",
			ARRAY_A
		);
		?>
		<div class="wrap wpfs-wrap">
			<h1><?php esc_html_e( 'FastSpring Tools', 'wp-fastspring' ); ?></h1>

			<div class="wpfs-card">
				<h2><?php esc_html_e( 'Recent webhook events', 'wp-fastspring' ); ?></h2>
				<table class="widefat striped wpfs-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Event ID', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Type', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Live', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Processed', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Created', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Error', 'wp-fastspring' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php if ( empty( $events ) ) : ?>
						<tr><td colspan="6"><?php esc_html_e( 'No events received yet.', 'wp-fastspring' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $events as $e ) : ?>
							<tr>
								<td><code><?php echo esc_html( $e['event_id'] ); ?></code></td>
								<td><?php echo esc_html( $e['event_type'] ); ?></td>
								<td><?php echo (int) $e['live'] ? '<span class="wpfs-badge wpfs-badge--ok">LIVE</span>' : '<span class="wpfs-badge wpfs-badge--warning">TEST</span>'; ?></td>
								<td><?php echo (int) $e['processed'] ? esc_html__( 'Yes', 'wp-fastspring' ) : esc_html__( 'No', 'wp-fastspring' ); ?></td>
								<td><?php echo esc_html( $e['created_at'] ); ?></td>
								<td><?php echo esc_html( $e['error_message'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					</tbody>
				</table>
			</div>

			<div class="wpfs-card">
				<h2><?php esc_html_e( 'Recent log entries', 'wp-fastspring' ); ?></h2>
				<table class="widefat striped wpfs-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Level', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Channel', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Message', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'When', 'wp-fastspring' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php if ( empty( $logs ) ) : ?>
						<tr><td colspan="4"><?php esc_html_e( 'No log entries yet.', 'wp-fastspring' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $logs as $log ) : ?>
							<tr>
								<td><span class="wpfs-status wpfs-status--<?php echo esc_attr( $log['level'] ); ?>"><?php echo esc_html( $log['level'] ); ?></span></td>
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
