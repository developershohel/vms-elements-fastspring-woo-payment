<?php
/**
 * Coupons screen.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Coupons.
 */
class WP_FastSpring_Admin_Coupons {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap wpfs-wrap">';
		WP_FastSpring_Admin_Resource_Base::render_header(
			__( 'Coupons', 'wp-fastspring' ),
			__( 'Discount codes and promotional offers.', 'wp-fastspring' ),
			array( '<button type="button" class="button button-primary" data-wpfs-open-form="create-coupon">' . esc_html__( 'New coupon', 'wp-fastspring' ) . '</button>' )
		);

		if ( ! WP_FastSpring_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		// Handle delete.
		if ( isset( $_GET['delete'] ) && check_admin_referer( 'wpfs_delete_coupon' ) ) {
			$coupon_id = sanitize_text_field( wp_unslash( $_GET['delete'] ) );
			$result    = wp_fastspring()->api->delete_coupon( $coupon_id );
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		}

		// Handle creation.
		if ( ! empty( $_POST['wpfs_create_coupon'] ) && WP_FastSpring_Admin_Resource_Base::verify_post( 'wpfs_create_coupon' ) ) {
			$payload = array(
				'parent'      => sanitize_text_field( wp_unslash( $_POST['parent'] ?? '' ) ),
				'name'        => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
				'description' => sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) ),
				'discount'    => array(
					'type'  => sanitize_text_field( wp_unslash( $_POST['discount_type'] ?? 'percent' ) ),
					'value' => (float) ( $_POST['discount_value'] ?? 0 ),
				),
			);
			$expires = sanitize_text_field( wp_unslash( $_POST['expires'] ?? '' ) );
			if ( $expires ) {
				$payload['expirationDate'] = $expires;
			}
			$result = wp_fastspring()->api->create_coupon( $payload );
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		}

		$result  = wp_fastspring()->api->get_coupons();
		$coupons = array();
		if ( is_wp_error( $result ) ) {
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$coupons = isset( $result['coupons'] ) ? $result['coupons'] : ( isset( $result[0] ) ? $result : array() );
		}
		?>

		<div class="wpfs-card" data-wpfs-form="create-coupon" hidden>
			<h2><?php esc_html_e( 'Create coupon', 'wp-fastspring' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_create_coupon' ); ?>
				<input type="hidden" name="wpfs_create_coupon" value="1" />
				<div class="wpfs-grid wpfs-grid--two">
					<p><label><?php esc_html_e( 'Coupon ID (parent)', 'wp-fastspring' ); ?><br /><input type="text" required name="parent" class="regular-text" placeholder="SUMMER25" /></label></p>
					<p><label><?php esc_html_e( 'Name', 'wp-fastspring' ); ?><br /><input type="text" required name="name" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Discount type', 'wp-fastspring' ); ?><br />
						<select name="discount_type">
							<option value="percent"><?php esc_html_e( 'Percent (%)', 'wp-fastspring' ); ?></option>
							<option value="amount"><?php esc_html_e( 'Fixed amount', 'wp-fastspring' ); ?></option>
						</select>
					</label></p>
					<p><label><?php esc_html_e( 'Discount value', 'wp-fastspring' ); ?><br /><input type="number" step="0.01" name="discount_value" class="regular-text" required /></label></p>
					<p><label><?php esc_html_e( 'Expiration date (YYYY-MM-DD)', 'wp-fastspring' ); ?><br /><input type="text" name="expires" class="regular-text" placeholder="2026-12-31" /></label></p>
					<p><label><?php esc_html_e( 'Description', 'wp-fastspring' ); ?><br /><textarea name="description" class="regular-text" rows="2"></textarea></label></p>
				</div>
				<p><button class="button button-primary"><?php esc_html_e( 'Create coupon', 'wp-fastspring' ); ?></button></p>
			</form>
		</div>

		<table class="widefat striped wpfs-table">
			<thead><tr>
				<th><?php esc_html_e( 'Coupon', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Discount', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Description', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Expires', 'wp-fastspring' ); ?></th>
				<th></th>
			</tr></thead>
			<tbody>
			<?php if ( empty( $coupons ) ) : ?>
				<?php WP_FastSpring_Admin_Resource_Base::render_empty_row( __( 'No coupons configured.', 'wp-fastspring' ), 5 ); ?>
			<?php else : ?>
				<?php foreach ( $coupons as $c ) : ?>
					<?php
					$cid       = $c['parent'] ?? ( $c['id'] ?? '' );
					$discount  = isset( $c['discount'] ) ? $c['discount'] : array();
					$delete_url = wp_nonce_url(
						add_query_arg( array( 'page' => 'wp-fastspring-coupons', 'delete' => $cid ), admin_url( 'admin.php' ) ),
						'wpfs_delete_coupon'
					);
					?>
					<tr>
						<td><strong><?php echo esc_html( $cid ); ?></strong><br /><span class="description"><?php echo esc_html( $c['name'] ?? '' ); ?></span></td>
						<td>
							<?php
							if ( 'percent' === ( $discount['type'] ?? '' ) ) {
								echo esc_html( ( $discount['value'] ?? 0 ) . '%' );
							} else {
								echo esc_html( number_format_i18n( (float) ( $discount['value'] ?? 0 ), 2 ) );
							}
							?>
						</td>
						<td><?php echo esc_html( $c['description'] ?? '' ); ?></td>
						<td><?php echo esc_html( $c['expirationDate'] ?? '&mdash;' ); ?></td>
						<td>
							<?php WP_FastSpring_Admin_Resource_Base::render_view_button( $c ); ?>
							<a class="button button-small" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Delete coupon?', 'wp-fastspring' ); ?>');"><?php esc_html_e( 'Delete', 'wp-fastspring' ); ?></a>
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
