<?php
/**
 * Reports / Data screen.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Reports.
 */
class WP_FastSpring_Admin_Reports {

	const TRANSIENT_KEY = 'wp_fastspring_recent_reports';

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap wpfs-wrap">';
		WP_FastSpring_Admin_Resource_Base::render_header(
			__( 'Reports', 'wp-fastspring' ),
			__( 'Generate revenue, subscription, and order reports via the Data API.', 'wp-fastspring' )
		);

		if ( ! WP_FastSpring_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		$create_result = null;
		if ( ! empty( $_POST['wpfs_create_report'] ) && WP_FastSpring_Admin_Resource_Base::verify_post( 'wpfs_create_report' ) ) {
			$type   = sanitize_text_field( wp_unslash( $_POST['report_type'] ?? 'revenue' ) );
			$begin  = sanitize_text_field( wp_unslash( $_POST['begin'] ?? gmdate( 'Y-m-01' ) ) );
			$end    = sanitize_text_field( wp_unslash( $_POST['end'] ?? gmdate( 'Y-m-d' ) ) );
			$cur    = strtoupper( sanitize_text_field( wp_unslash( $_POST['currency'] ?? '' ) ) );
			$params = array( 'begin' => $begin, 'end' => $end );
			if ( $cur ) {
				$params['currency'] = $cur;
			}
			$create_result = wp_fastspring()->api->create_report( $type, $params );
			if ( ! is_wp_error( $create_result ) && ! empty( $create_result['requestId'] ) ) {
				self::remember_request( $create_result['requestId'], $type, $params );
			}
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $create_result );
		}

		// Handle download/refresh.
		$download = null;
		if ( isset( $_GET['download'] ) && check_admin_referer( 'wpfs_download_report' ) ) {
			$rid      = sanitize_text_field( wp_unslash( $_GET['download'] ) );
			$download = wp_fastspring()->api->download_report( $rid );
		}

		$recent = get_transient( self::TRANSIENT_KEY );
		if ( ! is_array( $recent ) ) {
			$recent = array();
		}
		?>
		<div class="wpfs-grid wpfs-grid--two">
			<div class="wpfs-card">
				<h2><?php esc_html_e( 'Generate report', 'wp-fastspring' ); ?></h2>
				<form method="post">
					<?php wp_nonce_field( 'wpfs_create_report' ); ?>
					<input type="hidden" name="wpfs_create_report" value="1" />
					<p><label><?php esc_html_e( 'Report type', 'wp-fastspring' ); ?><br />
						<select name="report_type">
							<option value="revenue"><?php esc_html_e( 'Revenue', 'wp-fastspring' ); ?></option>
							<option value="subscription"><?php esc_html_e( 'Subscription', 'wp-fastspring' ); ?></option>
							<option value="order"><?php esc_html_e( 'Order', 'wp-fastspring' ); ?></option>
							<option value="return"><?php esc_html_e( 'Return', 'wp-fastspring' ); ?></option>
						</select>
					</label></p>
					<p><label><?php esc_html_e( 'Start date (YYYY-MM-DD)', 'wp-fastspring' ); ?><br /><input type="date" name="begin" value="<?php echo esc_attr( gmdate( 'Y-m-01' ) ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'End date (YYYY-MM-DD)', 'wp-fastspring' ); ?><br /><input type="date" name="end" value="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>" /></label></p>
					<p><label><?php esc_html_e( 'Currency (optional)', 'wp-fastspring' ); ?><br /><input type="text" maxlength="3" name="currency" placeholder="USD" /></label></p>
					<p><button class="button button-primary"><?php esc_html_e( 'Generate', 'wp-fastspring' ); ?></button></p>
				</form>
			</div>

			<div class="wpfs-card">
				<h2><?php esc_html_e( 'Recent requests', 'wp-fastspring' ); ?></h2>
				<?php if ( empty( $recent ) ) : ?>
					<p class="description"><?php esc_html_e( 'No reports have been generated in this session.', 'wp-fastspring' ); ?></p>
				<?php else : ?>
					<table class="widefat striped wpfs-table">
						<thead><tr>
							<th><?php esc_html_e( 'Request ID', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Type', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Range', 'wp-fastspring' ); ?></th>
							<th></th>
						</tr></thead>
						<tbody>
						<?php foreach ( array_reverse( $recent ) as $req ) :
							$status = wp_fastspring()->api->get_report_status( $req['request_id'] );
							$ready  = ! is_wp_error( $status ) && isset( $status['status'] ) && 'completed' === $status['status'];
							$dl_url = wp_nonce_url(
								add_query_arg( array( 'page' => 'wp-fastspring-reports', 'download' => $req['request_id'] ), admin_url( 'admin.php' ) ),
								'wpfs_download_report'
							);
							?>
							<tr>
								<td><code><?php echo esc_html( $req['request_id'] ); ?></code></td>
								<td><?php echo esc_html( $req['type'] ); ?></td>
								<td><?php echo esc_html( ( $req['params']['begin'] ?? '' ) . ' → ' . ( $req['params']['end'] ?? '' ) ); ?></td>
								<td>
									<?php if ( $ready ) : ?>
										<a class="button button-small button-primary" href="<?php echo esc_url( $dl_url ); ?>"><?php esc_html_e( 'Download', 'wp-fastspring' ); ?></a>
									<?php else : ?>
										<span class="wpfs-status wpfs-status--pending"><?php echo esc_html( is_wp_error( $status ) ? __( 'error', 'wp-fastspring' ) : ( $status['status'] ?? __( 'pending', 'wp-fastspring' ) ) ); ?></span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $download ) : ?>
			<div class="wpfs-card">
				<h2><?php esc_html_e( 'Report payload', 'wp-fastspring' ); ?></h2>
				<?php if ( is_wp_error( $download ) ) : ?>
					<?php WP_FastSpring_Admin_Resource_Base::render_result_notice( $download ); ?>
				<?php else : ?>
					<pre class="wpfs-json"><?php echo esc_html( wp_json_encode( $download, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ); ?></pre>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php
		echo '</div>';
	}

	/**
	 * Remember a generated report request id (transient).
	 *
	 * @param string $request_id ID.
	 * @param string $type       Type.
	 * @param array  $params     Params.
	 */
	private static function remember_request( $request_id, $type, $params ) {
		$existing = get_transient( self::TRANSIENT_KEY );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}
		$existing[] = array(
			'request_id' => $request_id,
			'type'       => $type,
			'params'     => $params,
			'created'    => time(),
		);
		// Keep last 20.
		$existing = array_slice( $existing, -20 );
		set_transient( self::TRANSIENT_KEY, $existing, DAY_IN_SECONDS );
	}
}
