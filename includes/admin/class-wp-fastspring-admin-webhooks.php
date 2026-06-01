<?php
/**
 * Webhooks screen (HMAC management).
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Webhooks.
 */
class WP_FastSpring_Admin_Webhooks {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap wpfs-wrap">';
		WP_FastSpring_Admin_Resource_Base::render_header(
			__( 'Webhooks', 'wp-fastspring' ),
			__( 'Configured webhook endpoints and HMAC secret rotation.', 'wp-fastspring' )
		);

		if ( ! WP_FastSpring_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		// Handle rotation.
		$rotation_result = null;
		if ( isset( $_GET['rotate'] ) && check_admin_referer( 'wpfs_rotate_hmac' ) ) {
			$wid             = sanitize_text_field( wp_unslash( $_GET['rotate'] ) );
			$rotation_result = wp_fastspring()->api->rotate_webhook_hmac( $wid );
			if ( ! is_wp_error( $rotation_result ) ) {
				printf( '<div class="notice notice-success"><p>%s</p></div>', esc_html__( 'HMAC secret rotated. Update your webhook secret below.', 'wp-fastspring' ) );
				if ( ! empty( $rotation_result['hmacSecret'] ) ) {
					$settings = wp_fastspring()->settings;
					if ( $settings->is_sandbox() ) {
						$settings->set( 'webhook_secret_sandbox', $rotation_result['hmacSecret'] );
					} else {
						$settings->set( 'webhook_secret_live', $rotation_result['hmacSecret'] );
					}
					$settings->refresh();
				}
			} else {
				WP_FastSpring_Admin_Resource_Base::render_result_notice( $rotation_result );
			}
		}

		$result = wp_fastspring()->api->get_webhooks();
		$hooks  = array();
		if ( is_wp_error( $result ) ) {
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$hooks = isset( $result['webhooks'] ) ? $result['webhooks'] : ( isset( $result[0] ) ? $result : array() );
		}

		$settings = wp_fastspring()->settings;
		?>

		<div class="wpfs-card">
			<h2><?php esc_html_e( 'Receiver endpoint', 'wp-fastspring' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Configure the URL below in FastSpring App > Integrations > Webhooks. Use HMAC SHA256 signing and copy the secret into Settings.', 'wp-fastspring' ); ?>
			</p>
			<input type="text" readonly value="<?php echo esc_attr( $settings->webhook_url() ); ?>" class="large-text wpfs-readonly" onclick="this.select()" />
		</div>

		<div class="wpfs-card">
			<h2><?php esc_html_e( 'Configured webhooks', 'wp-fastspring' ); ?></h2>
			<table class="widefat striped wpfs-table">
				<thead><tr>
					<th><?php esc_html_e( 'Webhook ID', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'URL', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Events', 'wp-fastspring' ); ?></th>
					<th></th>
				</tr></thead>
				<tbody>
				<?php if ( empty( $hooks ) ) : ?>
					<?php WP_FastSpring_Admin_Resource_Base::render_empty_row( __( 'No webhooks configured in FastSpring yet.', 'wp-fastspring' ), 4 ); ?>
				<?php else : ?>
					<?php foreach ( $hooks as $h ) : ?>
						<?php
						$wid = $h['id'] ?? '';
						$rotate_url = wp_nonce_url(
							add_query_arg( array( 'page' => 'wp-fastspring-webhooks', 'rotate' => $wid ), admin_url( 'admin.php' ) ),
							'wpfs_rotate_hmac'
						);
						?>
						<tr>
							<td><code><?php echo esc_html( $wid ); ?></code></td>
							<td><?php echo esc_html( $h['url'] ?? '' ); ?></td>
							<td><?php echo esc_html( is_array( $h['events'] ?? null ) ? implode( ', ', $h['events'] ) : ( $h['events'] ?? '' ) ); ?></td>
							<td>
								<?php WP_FastSpring_Admin_Resource_Base::render_view_button( $h ); ?>
								<a class="button button-small" href="<?php echo esc_url( $rotate_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Rotate HMAC secret? Existing signatures will fail until you update the receiver.', 'wp-fastspring' ); ?>');"><?php esc_html_e( 'Rotate HMAC', 'wp-fastspring' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>

		<?php
		WP_FastSpring_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}
}
