<?php
/**
 * Shared helpers for admin resource screens.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Resource_Base.
 */
class WP_FastSpring_Admin_Resource_Base {

	/**
	 * Render the JSON detail modal markup once per page.
	 */
	public static function render_json_modal() {
		?>
		<div id="wpfs-json-modal" class="wpfs-modal" hidden>
			<div class="wpfs-modal__backdrop" data-wpfs-close></div>
			<div class="wpfs-modal__panel" role="dialog" aria-modal="true">
				<div class="wpfs-modal__head">
					<h2 id="wpfs-json-modal-title"><?php esc_html_e( 'Details', 'wp-fastspring' ); ?></h2>
					<button type="button" class="wpfs-modal__close" data-wpfs-close aria-label="<?php esc_attr_e( 'Close', 'wp-fastspring' ); ?>">&times;</button>
				</div>
				<pre id="wpfs-json-modal-body" class="wpfs-json"></pre>
				<div class="wpfs-modal__foot">
					<button type="button" class="button" data-wpfs-close><?php esc_html_e( 'Close', 'wp-fastspring' ); ?></button>
					<button type="button" class="button button-primary" id="wpfs-json-copy"><?php esc_html_e( 'Copy JSON', 'wp-fastspring' ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a notice/error block for results.
	 *
	 * @param mixed $result API result (array or WP_Error).
	 * @return void
	 */
	public static function render_result_notice( $result ) {
		if ( null === $result ) {
			return;
		}
		if ( is_wp_error( $result ) ) {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html( $result->get_error_message() )
			);
			return;
		}
		printf(
			'<div class="notice notice-success"><p>%s</p></div>',
			esc_html__( 'Operation completed successfully.', 'wp-fastspring' )
		);
	}

	/**
	 * Helper to render a "View JSON" button for a row.
	 *
	 * @param mixed $row Row data.
	 */
	public static function render_view_button( $row ) {
		$json = wp_json_encode( $row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
		printf(
			'<button type="button" class="button button-small wpfs-view-json" data-json="%s">%s</button>',
			esc_attr( $json ),
			esc_html__( 'View', 'wp-fastspring' )
		);
	}

	/**
	 * Render an empty state.
	 *
	 * @param string $message Empty message.
	 * @param int    $colspan Colspan for cell.
	 */
	public static function render_empty_row( $message, $colspan = 4 ) {
		printf( '<tr><td colspan="%d"><em>%s</em></td></tr>', (int) $colspan, esc_html( $message ) );
	}

	/**
	 * Render a credentials warning if API isn't ready.
	 *
	 * @return bool True if credentials exist (can render screen), false otherwise.
	 */
	public static function require_credentials() {
		$settings = wp_fastspring()->settings;
		if ( ! $settings->has_credentials() ) {
			printf(
				'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
				esc_html__( 'FastSpring API credentials are not configured for the active mode.', 'wp-fastspring' ),
				esc_url( admin_url( 'admin.php?page=wp-fastspring-settings' ) ),
				esc_html__( 'Open settings', 'wp-fastspring' )
			);
			return false;
		}
		return true;
	}

	/**
	 * Render a header for the resource page.
	 *
	 * @param string $title    Title.
	 * @param string $subtitle Subtitle.
	 * @param array  $actions  HTML action buttons.
	 */
	public static function render_header( $title, $subtitle = '', $actions = array() ) {
		$mode = wp_fastspring()->settings->get_mode();
		?>
		<div class="wpfs-header">
			<div class="wpfs-header__title">
				<h1><?php echo esc_html( $title ); ?></h1>
				<span class="wpfs-mode-pill wpfs-mode-pill--<?php echo esc_attr( $mode ); ?>">
					<?php echo 'live' === $mode ? esc_html__( 'LIVE', 'wp-fastspring' ) : esc_html__( 'SANDBOX', 'wp-fastspring' ); ?>
				</span>
				<?php if ( $subtitle ) : ?>
					<span class="wpfs-subtitle"><?php echo esc_html( $subtitle ); ?></span>
				<?php endif; ?>
			</div>
			<div class="wpfs-header__actions">
				<?php foreach ( $actions as $action ) : ?>
					<?php echo wp_kses_post( $action ); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Verify nonce and capability for a POST submission.
	 *
	 * @param string $nonce_action Action name.
	 * @return bool
	 */
	public static function verify_post( $nonce_action ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		return (bool) wp_verify_nonce( $nonce, $nonce_action );
	}
}
