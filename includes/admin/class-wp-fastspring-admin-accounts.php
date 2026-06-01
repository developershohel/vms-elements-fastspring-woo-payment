<?php
/**
 * Accounts (customers) screen.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Accounts.
 */
class WP_FastSpring_Admin_Accounts {

	/**
	 * Render the screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap wpfs-wrap">';
		WP_FastSpring_Admin_Resource_Base::render_header(
			__( 'Accounts', 'wp-fastspring' ),
			__( 'Search FastSpring customer accounts.', 'wp-fastspring' ),
			array( '<button type="button" class="button button-primary" data-wpfs-open-form="create-account">' . esc_html__( 'New account', 'wp-fastspring' ) . '</button>' )
		);

		if ( ! WP_FastSpring_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		$result   = null;
		$accounts = array();

		// Handle creation.
		$create_result = null;
		if ( ! empty( $_POST['wpfs_create_account'] ) && WP_FastSpring_Admin_Resource_Base::verify_post( 'wpfs_create_account' ) ) {
			$payload = array(
				'contact' => array(
					'email'   => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
					'first'   => sanitize_text_field( wp_unslash( $_POST['first'] ?? '' ) ),
					'last'    => sanitize_text_field( wp_unslash( $_POST['last'] ?? '' ) ),
					'company' => sanitize_text_field( wp_unslash( $_POST['company'] ?? '' ) ),
				),
				'language' => sanitize_text_field( wp_unslash( $_POST['language'] ?? 'en' ) ),
				'country'  => sanitize_text_field( wp_unslash( $_POST['country'] ?? '' ) ),
			);
			$create_result = wp_fastspring()->api->create_account( $payload );
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $create_result );
		}

		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$page   = isset( $_GET['paged'] ) ? max( 0, (int) $_GET['paged'] - 1 ) : 0;
		$params = array( 'limit' => 50, 'page' => $page );
		if ( $search ) {
			$params['email'] = $search;
		}

		$result = wp_fastspring()->api->get_accounts( $params );

		if ( is_wp_error( $result ) ) {
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$accounts = isset( $result['accounts'] ) ? $result['accounts'] : ( isset( $result[0] ) ? $result : array() );
		}
		?>

		<form method="get" class="wpfs-filters">
			<input type="hidden" name="page" value="wp-fastspring-accounts" />
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by email...', 'wp-fastspring' ); ?>" />
			<button class="button"><?php esc_html_e( 'Search', 'wp-fastspring' ); ?></button>
		</form>

		<div class="wpfs-card" data-wpfs-form="create-account" hidden>
			<h2><?php esc_html_e( 'Create new account', 'wp-fastspring' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_create_account' ); ?>
				<input type="hidden" name="wpfs_create_account" value="1" />
				<div class="wpfs-grid wpfs-grid--two">
					<p><label><?php esc_html_e( 'Email', 'wp-fastspring' ); ?><br /><input type="email" required name="email" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Company', 'wp-fastspring' ); ?><br /><input type="text" name="company" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'First name', 'wp-fastspring' ); ?><br /><input type="text" name="first" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Last name', 'wp-fastspring' ); ?><br /><input type="text" name="last" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Country (ISO 2)', 'wp-fastspring' ); ?><br /><input type="text" name="country" maxlength="2" class="regular-text" placeholder="US" /></label></p>
					<p><label><?php esc_html_e( 'Language', 'wp-fastspring' ); ?><br /><input type="text" name="language" maxlength="5" class="regular-text" value="en" /></label></p>
				</div>
				<p><button class="button button-primary"><?php esc_html_e( 'Create account', 'wp-fastspring' ); ?></button></p>
			</form>
		</div>

		<table class="widefat striped wpfs-table">
			<thead><tr>
				<th><?php esc_html_e( 'Account ID', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Email', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Name', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Country', 'wp-fastspring' ); ?></th>
				<th><?php esc_html_e( 'Created', 'wp-fastspring' ); ?></th>
				<th></th>
			</tr></thead>
			<tbody>
			<?php if ( empty( $accounts ) ) : ?>
				<?php WP_FastSpring_Admin_Resource_Base::render_empty_row( __( 'No accounts found.', 'wp-fastspring' ), 6 ); ?>
			<?php else : ?>
				<?php foreach ( $accounts as $a ) : ?>
					<?php $contact = isset( $a['contact'] ) ? $a['contact'] : array(); ?>
					<tr>
						<td><code><?php echo esc_html( $a['id'] ?? '' ); ?></code></td>
						<td><?php echo esc_html( $contact['email'] ?? '' ); ?></td>
						<td><?php echo esc_html( trim( ( $contact['first'] ?? '' ) . ' ' . ( $contact['last'] ?? '' ) ) ); ?></td>
						<td><?php echo esc_html( $a['country'] ?? '' ); ?></td>
						<td><?php echo isset( $a['created'] ) ? esc_html( gmdate( 'Y-m-d', (int) ( $a['created'] / 1000 ) ) ) : '&mdash;'; ?></td>
						<td><?php WP_FastSpring_Admin_Resource_Base::render_view_button( $a ); ?></td>
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
