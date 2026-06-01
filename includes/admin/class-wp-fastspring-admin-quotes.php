<?php
/**
 * Quotes screen.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Quotes.
 */
class WP_FastSpring_Admin_Quotes {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap wpfs-wrap">';
		WP_FastSpring_Admin_Resource_Base::render_header(
			__( 'Quotes', 'wp-fastspring' ),
			__( 'B2B price quotes and custom sales cycles.', 'wp-fastspring' ),
			array( '<button type="button" class="button button-primary" data-wpfs-open-form="create-quote">' . esc_html__( 'New quote', 'wp-fastspring' ) . '</button>' )
		);

		if ( ! WP_FastSpring_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		if ( ! empty( $_POST['wpfs_create_quote'] ) && WP_FastSpring_Admin_Resource_Base::verify_post( 'wpfs_create_quote' ) ) {
			$products = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $_POST['products'] ?? '' ) ) ) ) );
			$items    = array();
			foreach ( $products as $p ) {
				$items[] = array( 'product' => $p, 'quantity' => 1 );
			}
			$payload = array(
				'customer' => array(
					'email'   => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
					'first'   => sanitize_text_field( wp_unslash( $_POST['first'] ?? '' ) ),
					'last'    => sanitize_text_field( wp_unslash( $_POST['last'] ?? '' ) ),
					'company' => sanitize_text_field( wp_unslash( $_POST['company'] ?? '' ) ),
				),
				'items'    => $items,
				'currency' => strtoupper( sanitize_text_field( wp_unslash( $_POST['currency'] ?? 'USD' ) ) ),
				'notes'    => sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) ),
			);
			$result = wp_fastspring()->api->create_quote( $payload );
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		}

		$result = wp_fastspring()->api->get_quotes();
		$quotes = array();
		if ( is_wp_error( $result ) ) {
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$quotes = isset( $result['quotes'] ) ? $result['quotes'] : ( isset( $result[0] ) ? $result : array() );
		}
		?>

		<div class="wpfs-card" data-wpfs-form="create-quote" hidden>
			<h2><?php esc_html_e( 'Create quote', 'wp-fastspring' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_create_quote' ); ?>
				<input type="hidden" name="wpfs_create_quote" value="1" />
				<div class="wpfs-grid wpfs-grid--two">
					<p><label><?php esc_html_e( 'Email', 'wp-fastspring' ); ?><br /><input type="email" required name="email" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Company', 'wp-fastspring' ); ?><br /><input type="text" name="company" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'First name', 'wp-fastspring' ); ?><br /><input type="text" name="first" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Last name', 'wp-fastspring' ); ?><br /><input type="text" name="last" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Products (comma separated paths)', 'wp-fastspring' ); ?><br /><input type="text" required name="products" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Currency', 'wp-fastspring' ); ?><br /><input type="text" name="currency" maxlength="3" value="USD" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Notes', 'wp-fastspring' ); ?><br /><textarea name="notes" rows="2" class="regular-text"></textarea></label></p>
				</div>
				<p><button class="button button-primary"><?php esc_html_e( 'Create quote', 'wp-fastspring' ); ?></button></p>
			</form>
		</div>

		<table class="widefat striped wpfs-table">
			<thead><tr>
				<th><?php esc_html_e( 'Quote', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Customer', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Total', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Status', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Expires', 'wp-fastspring' ); ?></th>
				<th></th>
			</tr></thead>
			<tbody>
			<?php if ( empty( $quotes ) ) : ?>
				<?php WP_FastSpring_Admin_Resource_Base::render_empty_row( __( 'No quotes.', 'wp-fastspring' ), 6 ); ?>
			<?php else : ?>
				<?php foreach ( $quotes as $q ) : ?>
					<tr>
						<td><code><?php echo esc_html( $q['id'] ?? '' ); ?></code></td>
						<td><?php echo esc_html( $q['customer']['email'] ?? '' ); ?></td>
						<td><?php echo esc_html( ( $q['currency'] ?? '' ) . ' ' . number_format_i18n( (float) ( $q['total'] ?? 0 ), 2 ) ); ?></td>
						<td><span class="wpfs-status wpfs-status--<?php echo esc_attr( $q['status'] ?? 'info' ); ?>"><?php echo esc_html( $q['status'] ?? '' ); ?></span></td>
						<td><?php echo esc_html( $q['expirationDate'] ?? '&mdash;' ); ?></td>
						<td><?php WP_FastSpring_Admin_Resource_Base::render_view_button( $q ); ?></td>
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
