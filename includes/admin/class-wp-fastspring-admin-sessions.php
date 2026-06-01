<?php
/**
 * Sessions screen (V1 + V2).
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Sessions.
 */
class WP_FastSpring_Admin_Sessions {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="wrap wpfs-wrap">';
		WP_FastSpring_Admin_Resource_Base::render_header(
			__( 'Sessions', 'wp-fastspring' ),
			__( 'Create checkout sessions and inspect existing ones.', 'wp-fastspring' )
		);

		if ( ! WP_FastSpring_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'create';
		$base = admin_url( 'admin.php?page=wp-fastspring-sessions' );
		?>
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'create', $base ) ); ?>" class="nav-tab <?php echo 'create' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Create session', 'wp-fastspring' ); ?></a>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'lookup', $base ) ); ?>" class="nav-tab <?php echo 'lookup' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Lookup', 'wp-fastspring' ); ?></a>
		</h2>

		<?php if ( 'create' === $tab ) : ?>
			<?php self::render_create_tab(); ?>
		<?php else : ?>
			<?php self::render_lookup_tab(); ?>
		<?php endif; ?>

		<?php
		WP_FastSpring_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}

	/**
	 * Create session tab.
	 */
	private static function render_create_tab() {
		$result = null;
		if ( ! empty( $_POST['wpfs_create_session'] ) && WP_FastSpring_Admin_Resource_Base::verify_post( 'wpfs_create_session' ) ) {
			$version  = sanitize_text_field( wp_unslash( $_POST['version'] ?? 'v1' ) );
			$products = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $_POST['products'] ?? '' ) ) ) ) );
			$items    = array();
			foreach ( $products as $p ) {
				$items[] = array( 'product' => $p, 'quantity' => 1 );
			}
			$payload = array(
				'items'    => $items,
				'currency' => strtoupper( sanitize_text_field( wp_unslash( $_POST['currency'] ?? 'USD' ) ) ),
				'language' => sanitize_text_field( wp_unslash( $_POST['language'] ?? 'en' ) ),
			);
			$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
			if ( $email ) {
				$payload['account'] = array( 'contact' => array( 'email' => $email ) );
			}
			$result = 'v2' === $version
				? wp_fastspring()->api->create_session_v2( $payload )
				: wp_fastspring()->api->create_session( $payload );

			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		}
		?>
		<div class="wpfs-card">
			<h2><?php esc_html_e( 'Create checkout session', 'wp-fastspring' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_create_session' ); ?>
				<input type="hidden" name="wpfs_create_session" value="1" />
				<div class="wpfs-grid wpfs-grid--two">
					<p><label><?php esc_html_e( 'API version', 'wp-fastspring' ); ?><br />
						<select name="version">
							<option value="v1"><?php esc_html_e( 'Sessions (v1)', 'wp-fastspring' ); ?></option>
							<option value="v2"><?php esc_html_e( 'Sessions V2', 'wp-fastspring' ); ?></option>
						</select>
					</label></p>
					<p><label><?php esc_html_e( 'Email (optional)', 'wp-fastspring' ); ?><br /><input type="email" name="email" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Products (comma separated paths)', 'wp-fastspring' ); ?><br /><input type="text" required name="products" class="regular-text" placeholder="my-app, my-app-pro" /></label></p>
					<p><label><?php esc_html_e( 'Currency', 'wp-fastspring' ); ?><br /><input type="text" name="currency" maxlength="3" value="USD" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Language', 'wp-fastspring' ); ?><br /><input type="text" name="language" maxlength="5" value="en" class="regular-text" /></label></p>
				</div>
				<p><button class="button button-primary"><?php esc_html_e( 'Create session', 'wp-fastspring' ); ?></button></p>
			</form>

			<?php if ( $result && ! is_wp_error( $result ) && isset( $result['id'] ) ) : ?>
				<?php
				$storefront = wp_fastspring()->settings->storefront();
				$url = $storefront ? sprintf( 'https://%s/session/%s', $storefront, rawurlencode( $result['id'] ) ) : '';
				?>
				<div class="notice notice-info"><p>
					<?php esc_html_e( 'Session created.', 'wp-fastspring' ); ?>
					<code><?php echo esc_html( $result['id'] ); ?></code>
					<?php if ( $url ) : ?>
						&nbsp;<a class="button button-small" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open checkout', 'wp-fastspring' ); ?></a>
					<?php endif; ?>
				</p></div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Lookup session tab.
	 */
	private static function render_lookup_tab() {
		$id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
		$version = isset( $_GET['v'] ) ? sanitize_text_field( wp_unslash( $_GET['v'] ) ) : 'v1';
		$session = null;
		if ( $id ) {
			$session = 'v2' === $version
				? wp_fastspring()->api->get_session_v2( $id )
				: wp_fastspring()->api->get_session( $id );
		}
		?>
		<form method="get" class="wpfs-filters">
			<input type="hidden" name="page" value="wp-fastspring-sessions" />
			<input type="hidden" name="tab" value="lookup" />
			<select name="v">
				<option value="v1" <?php selected( $version, 'v1' ); ?>><?php esc_html_e( 'V1', 'wp-fastspring' ); ?></option>
				<option value="v2" <?php selected( $version, 'v2' ); ?>><?php esc_html_e( 'V2', 'wp-fastspring' ); ?></option>
			</select>
			<input type="text" name="id" value="<?php echo esc_attr( $id ); ?>" placeholder="<?php esc_attr_e( 'Session id', 'wp-fastspring' ); ?>" />
			<button class="button"><?php esc_html_e( 'Lookup', 'wp-fastspring' ); ?></button>
		</form>
		<?php
		if ( $session ) {
			if ( is_wp_error( $session ) ) {
				WP_FastSpring_Admin_Resource_Base::render_result_notice( $session );
			} else {
				printf(
					'<pre class="wpfs-json">%s</pre>',
					esc_html( wp_json_encode( $session, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) )
				);
			}
		}
	}
}
