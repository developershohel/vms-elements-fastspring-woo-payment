<?php
/**
 * Invoices screen.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Invoices.
 */
class WP_FastSpring_Admin_Invoices {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap wpfs-wrap">';
		WP_FastSpring_Admin_Resource_Base::render_header(
			__( 'Invoices', 'wp-fastspring' ),
			__( 'Customer invoices and proforma documents.', 'wp-fastspring' ),
			array( '<button type="button" class="button button-primary" data-wpfs-open-form="create-proforma">' . esc_html__( 'New proforma', 'wp-fastspring' ) . '</button>' )
		);

		if ( ! WP_FastSpring_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		$proforma_result = null;
		if ( ! empty( $_POST['wpfs_create_proforma'] ) && WP_FastSpring_Admin_Resource_Base::verify_post( 'wpfs_create_proforma' ) ) {
			$account_id = sanitize_text_field( wp_unslash( $_POST['account_id'] ?? '' ) );
			$products   = array_filter(
				array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $_POST['products'] ?? '' ) ) ) )
			);
			$items = array();
			foreach ( $products as $p ) {
				$items[] = array( 'product' => $p, 'quantity' => 1 );
			}
			$payload = array(
				'account' => $account_id,
				'items'   => $items,
				'currency' => strtoupper( sanitize_text_field( wp_unslash( $_POST['currency'] ?? 'USD' ) ) ),
			);
			$proforma_result = wp_fastspring()->api->create_proforma_invoice( $payload );
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $proforma_result );
		}

		// Build search query.
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$params = array();
		if ( $search ) {
			$params['search'] = $search;
		}

		$result   = wp_fastspring()->api->search_invoices( $params );
		$invoices = array();
		if ( is_wp_error( $result ) ) {
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$invoices = isset( $result['invoices'] ) ? $result['invoices'] : ( isset( $result[0] ) ? $result : array() );
		}
		?>

		<form method="get" class="wpfs-filters">
			<input type="hidden" name="page" value="wp-fastspring-invoices" />
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search invoices...', 'wp-fastspring' ); ?>" />
			<button class="button"><?php esc_html_e( 'Search', 'wp-fastspring' ); ?></button>
		</form>

		<div class="wpfs-card" data-wpfs-form="create-proforma" hidden>
			<h2><?php esc_html_e( 'Generate proforma invoice', 'wp-fastspring' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_create_proforma' ); ?>
				<input type="hidden" name="wpfs_create_proforma" value="1" />
				<div class="wpfs-grid wpfs-grid--two">
					<p><label><?php esc_html_e( 'Account ID', 'wp-fastspring' ); ?><br /><input type="text" name="account_id" required class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Products (comma separated paths)', 'wp-fastspring' ); ?><br /><input type="text" name="products" required class="regular-text" placeholder="my-app, my-app-pro" /></label></p>
					<p><label><?php esc_html_e( 'Currency', 'wp-fastspring' ); ?><br /><input type="text" name="currency" maxlength="3" value="USD" class="regular-text" /></label></p>
				</div>
				<p><button class="button button-primary"><?php esc_html_e( 'Generate', 'wp-fastspring' ); ?></button></p>
			</form>
		</div>

		<table class="widefat striped wpfs-table">
			<thead><tr>
				<th><?php esc_html_e( 'Invoice', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Customer', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Total', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Status', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Created', 'wp-fastspring' ); ?></th>
				<th></th>
			</tr></thead>
			<tbody>
			<?php if ( empty( $invoices ) ) : ?>
				<?php WP_FastSpring_Admin_Resource_Base::render_empty_row( __( 'No invoices.', 'wp-fastspring' ), 6 ); ?>
			<?php else : ?>
				<?php foreach ( $invoices as $inv ) : ?>
					<tr>
						<td><code><?php echo esc_html( $inv['id'] ?? '' ); ?></code></td>
						<td><?php echo esc_html( ( $inv['customer']['email'] ?? $inv['account'] ) ?? '' ); ?></td>
						<td><?php echo esc_html( ( $inv['currency'] ?? '' ) . ' ' . number_format_i18n( (float) ( $inv['total'] ?? 0 ), 2 ) ); ?></td>
						<td><span class="wpfs-status wpfs-status--<?php echo esc_attr( $inv['status'] ?? 'info' ); ?>"><?php echo esc_html( $inv['status'] ?? '' ); ?></span></td>
						<td><?php echo esc_html( $inv['created'] ?? '' ); ?></td>
						<td><?php WP_FastSpring_Admin_Resource_Base::render_view_button( $inv ); ?></td>
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
