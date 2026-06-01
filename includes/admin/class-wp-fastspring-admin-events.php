<?php
/**
 * Events screen (historical event query against FastSpring API).
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Events.
 */
class WP_FastSpring_Admin_Events {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap wpfs-wrap">';
		WP_FastSpring_Admin_Resource_Base::render_header(
			__( 'Events', 'wp-fastspring' ),
			__( 'Query historical events from FastSpring.', 'wp-fastspring' )
		);

		if ( ! WP_FastSpring_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		// Handle "mark processed" action.
		if ( isset( $_GET['mark_processed'] ) && check_admin_referer( 'wpfs_mark_event' ) ) {
			$event_id = sanitize_text_field( wp_unslash( $_GET['mark_processed'] ) );
			$result   = wp_fastspring()->api->mark_event_processed( $event_id );
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		}

		$type   = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : 'unprocessed';
		$days   = isset( $_GET['days'] ) ? max( 1, min( 30, (int) $_GET['days'] ) ) : 7;
		$params = array( 'days' => $days, 'limit' => 100 );

		$result = wp_fastspring()->api->get_events( $type, $params );
		$events = array();
		if ( is_wp_error( $result ) ) {
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$events = isset( $result['events'] ) ? $result['events'] : ( isset( $result[0] ) ? $result : array() );
		}
		?>
		<form method="get" class="wpfs-filters">
			<input type="hidden" name="page" value="wp-fastspring-events" />
			<select name="type">
				<option value="unprocessed" <?php selected( $type, 'unprocessed' ); ?>><?php esc_html_e( 'Unprocessed', 'wp-fastspring' ); ?></option>
				<option value="processed" <?php selected( $type, 'processed' ); ?>><?php esc_html_e( 'Processed', 'wp-fastspring' ); ?></option>
			</select>
			<select name="days">
				<?php foreach ( array( 1, 3, 7, 14, 30 ) as $d ) : ?>
					<option value="<?php echo esc_attr( $d ); ?>" <?php selected( $days, $d ); ?>>
						<?php echo esc_html( sprintf( /* translators: %d days */ _n( 'Last %d day', 'Last %d days', $d, 'wp-fastspring' ), $d ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<button class="button"><?php esc_html_e( 'Refresh', 'wp-fastspring' ); ?></button>
		</form>

		<table class="widefat striped wpfs-table">
			<thead><tr>
				<th><?php esc_html_e( 'Event ID', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Type', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Live', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Created', 'wp-fastspring' ); ?></th>
				<th></th>
			</tr></thead>
			<tbody>
			<?php if ( empty( $events ) ) : ?>
				<?php WP_FastSpring_Admin_Resource_Base::render_empty_row( __( 'No events.', 'wp-fastspring' ), 5 ); ?>
			<?php else : ?>
				<?php foreach ( $events as $e ) : ?>
					<?php
					$event_id   = $e['id'] ?? '';
					$mark_url = wp_nonce_url(
						add_query_arg(
							array( 'page' => 'wp-fastspring-events', 'type' => $type, 'mark_processed' => $event_id ),
							admin_url( 'admin.php' )
						),
						'wpfs_mark_event'
					);
					?>
					<tr>
						<td><code><?php echo esc_html( $event_id ); ?></code></td>
						<td><?php echo esc_html( $e['type'] ?? '' ); ?></td>
						<td><?php echo ! empty( $e['live'] ) ? '<span class="wpfs-badge wpfs-badge--ok">LIVE</span>' : '<span class="wpfs-badge wpfs-badge--warning">TEST</span>'; ?></td>
						<td><?php echo esc_html( isset( $e['created'] ) ? gmdate( 'Y-m-d H:i', (int) ( $e['created'] / 1000 ) ) : '' ); ?></td>
						<td>
							<?php WP_FastSpring_Admin_Resource_Base::render_view_button( $e ); ?>
							<?php if ( 'unprocessed' === $type ) : ?>
								<a class="button button-small" href="<?php echo esc_url( $mark_url ); ?>"><?php esc_html_e( 'Mark processed', 'wp-fastspring' ); ?></a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>

		<?php
		WP_FastSpring_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}
}
