<?php
/**
 * Returns / refunds screen.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Returns.
 */
class WP_FastSpring_Admin_Returns {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="wrap wpfs-wrap">';
		WP_FastSpring_Admin_Resource_Base::render_header(
			__( 'Returns', 'wp-fastspring' ),
			__( 'Process refunds and manage returned access.', 'wp-fastspring' ),
			array( '<button type="button" class="button button-primary" data-wpfs-open-form="create-return">' . esc_html__( 'Issue refund', 'wp-fastspring' ) . '</button>' )
		);

		if ( ! WP_FastSpring_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		if ( ! empty( $_POST['wpfs_create_return'] ) && WP_FastSpring_Admin_Resource_Base::verify_post( 'wpfs_create_return' ) ) {
			$payload = array(
				'order'  => sanitize_text_field( wp_unslash( $_POST['order_id'] ?? '' ) ),
				'reason' => sanitize_text_field( wp_unslash( $_POST['reason'] ?? 'requested_by_customer' ) ),
				'note'   => sanitize_textarea_field( wp_unslash( $_POST['note'] ?? '' ) ),
			);
			$amount = $_POST['amount'] ?? '';
			if ( '' !== $amount ) {
				$payload['amount'] = (float) $amount;
			}
			$result = wp_fastspring()->api->create_return( $payload );
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		}

		$result = wp_fastspring()->api->search_returns();
		$rows   = array();
		if ( is_wp_error( $result ) ) {
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$rows = isset( $result['returns'] ) ? $result['returns'] : ( isset( $result[0] ) ? $result : array() );
		}
		?>

		<div class="wpfs-card" data-wpfs-form="create-return" hidden>
			<h2><?php esc_html_e( 'Issue refund', 'wp-fastspring' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_create_return' ); ?>
				<input type="hidden" name="wpfs_create_return" value="1" />
				<div class="wpfs-grid wpfs-grid--two">
					<p><label><?php esc_html_e( 'Order ID or reference', 'wp-fastspring' ); ?><br /><input type="text" required name="order_id" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Amount (blank for full refund)', 'wp-fastspring' ); ?><br /><input type="number" step="0.01" name="amount" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Reason', 'wp-fastspring' ); ?><br />
						<select name="reason">
							<option value="requested_by_customer"><?php esc_html_e( 'Requested by customer', 'wp-fastspring' ); ?></option>
							<option value="duplicate"><?php esc_html_e( 'Duplicate', 'wp-fastspring' ); ?></option>
							<option value="fraudulent"><?php esc_html_e( 'Fraudulent', 'wp-fastspring' ); ?></option>
							<option value="other"><?php esc_html_e( 'Other', 'wp-fastspring' ); ?></option>
						</select>
					</label></p>
					<p><label><?php esc_html_e( 'Internal note', 'wp-fastspring' ); ?><br /><textarea name="note" rows="2" class="regular-text"></textarea></label></p>
				</div>
				<p><button class="button button-primary"><?php esc_html_e( 'Submit refund', 'wp-fastspring' ); ?></button></p>
			</form>
		</div>

		<table class="widefat striped wpfs-table">
			<thead><tr>
				<th><?php esc_html_e( 'Return ID', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Original order', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Amount', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Reason', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Date', 'wp-fastspring' ); ?></th>
				<th></th>
			</tr></thead>
			<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<?php WP_FastSpring_Admin_Resource_Base::render_empty_row( __( 'No returns yet.', 'wp-fastspring' ), 6 ); ?>
			<?php else : ?>
				<?php foreach ( $rows as $r ) : ?>
					<tr>
						<td><code><?php echo esc_html( $r['id'] ?? '' ); ?></code></td>
						<td><code><?php echo esc_html( $r['original']['id'] ?? ( $r['order'] ?? '' ) ); ?></code></td>
						<td><?php echo esc_html( ( $r['currency'] ?? '' ) . ' ' . number_format_i18n( (float) ( $r['totalReturn'] ?? $r['amount'] ?? 0 ), 2 ) ); ?></td>
						<td><?php echo esc_html( $r['reason'] ?? '' ); ?></td>
						<td><?php echo esc_html( $r['returnDate'] ?? ( $r['created'] ?? '' ) ); ?></td>
						<td><?php WP_FastSpring_Admin_Resource_Base::render_view_button( $r ); ?></td>
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
