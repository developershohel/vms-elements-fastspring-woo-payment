<?php
/**
 * Shared helpers for admin resource screens.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Resource_Base.
 */
class VMS_EFWP_Admin_Resource_Base {

	/**
	 * Render the JSON detail modal markup once per page.
	 */
	public static function render_json_modal() {
		?>
		<div id="vms-efwp-json-modal" class="vms-efwp-modal" hidden>
			<div class="vms-efwp-modal__backdrop" data-vms-efwp-close></div>
			<div class="vms-efwp-modal__panel" role="dialog" aria-modal="true">
				<div class="vms-efwp-modal__head">
					<h2 id="vms-efwp-json-modal-title"><?php esc_html_e( 'Details', 'vms-elements-fastspring-woo-payment' ); ?></h2>
					<button type="button" class="vms-efwp-modal__close" data-vms-efwp-close aria-label="<?php esc_attr_e( 'Close', 'vms-elements-fastspring-woo-payment' ); ?>">&times;</button>
				</div>
				<pre id="vms-efwp-json-modal-body" class="vms-efwp-json"></pre>
				<div class="vms-efwp-modal__foot">
					<button type="button" class="button" data-vms-efwp-close><?php esc_html_e( 'Close', 'vms-elements-fastspring-woo-payment' ); ?></button>
					<button type="button" class="button button-primary" id="vms-efwp-json-copy"><?php esc_html_e( 'Copy JSON', 'vms-elements-fastspring-woo-payment' ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the overlay checkout link modal markup once per page.
	 */
	public static function render_checkout_link_modal() {
		?>
		<div id="vms-efwp-checkout-link-modal" class="vms-efwp-modal" hidden>
			<div class="vms-efwp-modal__backdrop" data-vms-efwp-close-checkout-link></div>
			<div class="vms-efwp-modal__panel vms-efwp-modal__panel--checkout-link" role="dialog" aria-modal="true" aria-labelledby="vms-efwp-checkout-link-modal-title">
				<div class="vms-efwp-modal__head">
					<h2 id="vms-efwp-checkout-link-modal-title"><?php esc_html_e( 'Overlay payment link', 'vms-elements-fastspring-woo-payment' ); ?></h2>
					<button type="button" class="vms-efwp-modal__close" data-vms-efwp-close-checkout-link aria-label="<?php esc_attr_e( 'Close', 'vms-elements-fastspring-woo-payment' ); ?>">&times;</button>
				</div>
				<div class="vms-efwp-checkout-link-body">
					<p class="vms-efwp-checkout-link-intro">
						<?php esc_html_e( 'Share the payment page link with customers. It opens your WordPress checkout page and launches the FastSpring popup overlay.', 'vms-elements-fastspring-woo-payment' ); ?>
					</p>
					<p class="vms-efwp-checkout-link-product">
						<strong><?php esc_html_e( 'Product', 'vms-elements-fastspring-woo-payment' ); ?>:</strong>
						<code id="vms-efwp-checkout-link-product"></code>
					</p>
					<div class="vms-efwp-copy-field">
						<label for="vms-efwp-checkout-payment-url"><?php esc_html_e( 'Payment page link', 'vms-elements-fastspring-woo-payment' ); ?></label>
						<div class="vms-efwp-copy-field__row">
							<input type="text" id="vms-efwp-checkout-payment-url" class="large-text" readonly />
							<button type="button" class="button vms-efwp-copy-checkout-field" data-target="#vms-efwp-checkout-payment-url"><?php esc_html_e( 'Copy', 'vms-elements-fastspring-woo-payment' ); ?></button>
							<a class="button" id="vms-efwp-checkout-open-preview" href="#" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open payment link', 'vms-elements-fastspring-woo-payment' ); ?></a>
						</div>
					</div>
					<div class="vms-efwp-copy-field">
						<label for="vms-efwp-checkout-overlay-url"><?php esc_html_e( 'FastSpring catalog URL (reference)', 'vms-elements-fastspring-woo-payment' ); ?></label>
						<div class="vms-efwp-copy-field__row">
							<input type="text" id="vms-efwp-checkout-overlay-url" class="large-text" readonly />
							<button type="button" class="button vms-efwp-copy-checkout-field" data-target="#vms-efwp-checkout-overlay-url"><?php esc_html_e( 'Copy', 'vms-elements-fastspring-woo-payment' ); ?></button>
						</div>
					</div>
					<input type="hidden" id="vms-efwp-checkout-preview-url" value="" />
					<div class="vms-efwp-copy-field" id="vms-efwp-checkout-session-wrap" hidden>
						<label for="vms-efwp-checkout-session-url"><?php esc_html_e( 'Checkout session URL', 'vms-elements-fastspring-woo-payment' ); ?></label>
						<div class="vms-efwp-copy-field__row">
							<input type="text" id="vms-efwp-checkout-session-url" class="large-text" readonly />
							<button type="button" class="button vms-efwp-copy-checkout-field" data-target="#vms-efwp-checkout-session-url"><?php esc_html_e( 'Copy', 'vms-elements-fastspring-woo-payment' ); ?></button>
						</div>
					</div>
					<div class="vms-efwp-copy-field">
						<label for="vms-efwp-checkout-embed-html"><?php esc_html_e( 'Embed HTML (Store Builder)', 'vms-elements-fastspring-woo-payment' ); ?></label>
						<div class="vms-efwp-copy-field__row vms-efwp-copy-field__row--stack">
							<textarea id="vms-efwp-checkout-embed-html" class="large-text code" rows="3" readonly></textarea>
							<button type="button" class="button vms-efwp-copy-checkout-field" data-target="#vms-efwp-checkout-embed-html"><?php esc_html_e( 'Copy', 'vms-elements-fastspring-woo-payment' ); ?></button>
						</div>
					</div>
					<p id="vms-efwp-checkout-link-status" class="vms-efwp-checkout-link-status" hidden></p>
				</div>
				<div class="vms-efwp-modal__foot">
					<button type="button" class="button" data-vms-efwp-close-checkout-link"><?php esc_html_e( 'Close', 'vms-elements-fastspring-woo-payment' ); ?></button>
					<button type="button" class="button" id="vms-efwp-checkout-generate-session"><?php esc_html_e( 'Generate session link', 'vms-elements-fastspring-woo-payment' ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render overlay checkout link actions for a catalog product path.
	 *
	 * @param string $product_path FastSpring product path.
	 */
	public static function render_checkout_link_buttons( $product_path ) {
		if ( ! class_exists( 'VMS_EFWP_Checkout_Links', false ) ) {
			return;
		}
		$product_path = sanitize_text_field( (string) $product_path );
		if ( '' === $product_path ) {
			return;
		}

		$has_popup = function_exists( 'vms_efwp' ) && vms_efwp()->settings && vms_efwp()->settings->has_popup_checkout();
		if ( ! $has_popup ) {
			printf(
				'<button type="button" class="button button-small" disabled title="%1$s">%2$s</button>',
				esc_attr__( 'Configure the popup checkout path in FastSpring Settings.', 'vms-elements-fastspring-woo-payment' ),
				esc_html__( 'Payment link', 'vms-elements-fastspring-woo-payment' )
			);
			return;
		}

		$payment_link = VMS_EFWP_Checkout_Links::build_payment_link_url( $product_path );

		printf(
			'<button type="button" class="button button-small vms-efwp-get-checkout-link" data-product-path="%1$s">%2$s</button>',
			esc_attr( $product_path ),
			esc_html__( 'Payment link', 'vms-elements-fastspring-woo-payment' )
		);

		if ( ! is_wp_error( $payment_link ) ) {
			printf(
				'<a class="button button-small" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
				esc_url( $payment_link ),
				esc_html__( 'Open payment link', 'vms-elements-fastspring-woo-payment' )
			);
		}
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

		$message = esc_html__( 'Operation completed successfully.', 'vms-elements-fastspring-woo-payment' );
		if ( is_string( $result ) && '' !== $result ) {
			$message = esc_html( $result );
		} elseif ( is_array( $result ) ) {
			if ( ! empty( $result['action'] ) && ! empty( $result['coupon'] ) ) {
				$message = sprintf(
					/* translators: 1: FastSpring action, 2: coupon path */
					esc_html__( '%1$s for %2$s completed successfully.', 'vms-elements-fastspring-woo-payment' ),
					esc_html( (string) $result['action'] ),
					esc_html( (string) $result['coupon'] )
				);
			} elseif ( ! empty( $result['result'] ) && 'success' === $result['result'] && ! empty( $result['coupon'] ) ) {
				$message = sprintf(
					/* translators: %s: coupon path */
					esc_html__( 'Coupon %s updated successfully.', 'vms-elements-fastspring-woo-payment' ),
					esc_html( (string) $result['coupon'] )
				);
			} elseif ( ! empty( $result['products'][0]['product'] ) && ! empty( $result['products'][0]['result'] ) && 'success' === $result['products'][0]['result'] ) {
				$message = sprintf(
					/* translators: 1: FastSpring action, 2: product path */
					esc_html__( '%1$s for %2$s completed successfully.', 'vms-elements-fastspring-woo-payment' ),
					esc_html( (string) ( $result['products'][0]['action'] ?? 'product' ) ),
					esc_html( (string) $result['products'][0]['product'] )
				);
			} elseif ( ! empty( $result['subscriptions'][0]['subscription'] ) && ! empty( $result['subscriptions'][0]['result'] ) && 'success' === $result['subscriptions'][0]['result'] ) {
				$message = sprintf(
					/* translators: 1: FastSpring action, 2: subscription id */
					esc_html__( '%1$s for %2$s completed successfully.', 'vms-elements-fastspring-woo-payment' ),
					esc_html( (string) ( $result['subscriptions'][0]['action'] ?? 'subscription' ) ),
					esc_html( (string) $result['subscriptions'][0]['subscription'] )
				);
			} elseif ( ! empty( $result['returns'][0]['return'] ) && ! empty( $result['returns'][0]['result'] ) && 'success' === $result['returns'][0]['result'] ) {
				$message = sprintf(
					/* translators: %s: return id */
					esc_html__( 'Return %s created successfully.', 'vms-elements-fastspring-woo-payment' ),
					esc_html( (string) $result['returns'][0]['return'] )
				);
			} elseif ( is_array( $result ) && isset( $result[0]['return'] ) && ! isset( $result['returns'] ) ) {
				$message = sprintf(
					/* translators: %d: number of returns */
					esc_html( _n( '%d return processed.', '%d returns processed.', count( $result ), 'vms-elements-fastspring-woo-payment' ) ),
					count( $result )
				);
			} elseif ( ! empty( $result['mode'] ) && 'async' === $result['mode'] && ! empty( $result['job']['id'] ) ) {
				$message = sprintf(
					/* translators: 1: job id, 2: status */
					esc_html__( 'Report job %1$s queued (%2$s).', 'vms-elements-fastspring-woo-payment' ),
					esc_html( (string) $result['job']['id'] ),
					esc_html( (string) ( $result['job']['status'] ?? '' ) )
				);
			} elseif ( ! empty( $result['mode'] ) && 'sync' === $result['mode'] && ! empty( $result['report'] ) ) {
				$message = sprintf(
					/* translators: %d: row count */
					esc_html( _n( 'Report generated with %d row.', 'Report generated with %d rows.', count( $result['report'] ), 'vms-elements-fastspring-woo-payment' ) ),
					count( $result['report'] )
				);
			} elseif ( isset( $result[0]['id'] ) && isset( $result[0]['status'] ) && isset( $result[0]['name'] ) && ! isset( $result[0]['return'] ) ) {
				$message = sprintf(
					/* translators: %d: job count */
					esc_html( _n( '%d job loaded.', '%d jobs loaded.', count( $result ), 'vms-elements-fastspring-woo-payment' ) ),
					count( $result )
				);
			} elseif ( ! empty( $result['id'] ) && isset( $result['status'] ) && ! empty( $result['name'] ) && ! isset( $result['processed'] ) && ! isset( $result['quoteUrl'] ) && ! isset( $result['checkoutUrls'] ) && ! isset( $result['expires'] ) && ! isset( $result['invoiceType'] ) ) {
				$message = sprintf(
					/* translators: 1: job id, 2: status */
					esc_html__( 'Report job %1$s (%2$s).', 'vms-elements-fastspring-woo-payment' ),
					esc_html( (string) $result['id'] ),
					esc_html( (string) $result['status'] )
				);
			} elseif ( ! empty( $result['id'] ) && isset( $result['processed'] ) && isset( $result['type'] ) && ! isset( $result['quoteUrl'] ) && ! isset( $result['invoiceType'] ) && ! isset( $result['checkoutUrls'] ) ) {
				$message = sprintf(
					/* translators: 1: event id, 2: processed state */
					esc_html__( 'Event %1$s updated (%2$s).', 'vms-elements-fastspring-woo-payment' ),
					esc_html( (string) $result['id'] ),
					! empty( $result['processed'] ) ? esc_html__( 'processed', 'vms-elements-fastspring-woo-payment' ) : esc_html__( 'unprocessed', 'vms-elements-fastspring-woo-payment' )
				);
			} elseif ( ! empty( $result['events'] ) && isset( $result['total'] ) ) {
				$message = sprintf(
					/* translators: %d: event count */
					esc_html( _n( '%d event loaded.', '%d events loaded.', (int) $result['total'], 'vms-elements-fastspring-woo-payment' ) ),
					(int) $result['total']
				);
			} elseif ( ! empty( $result['id'] ) && ( ! empty( $result['checkoutUrls']['webcheckoutUrl'] ) || isset( $result['checkoutStatus'] ) ) ) {
				$message = sprintf(
					/* translators: 1: session id, 2: status */
					esc_html__( 'Checkout session %1$s created (%2$s).', 'vms-elements-fastspring-woo-payment' ),
					esc_html( (string) $result['id'] ),
					esc_html( (string) ( $result['status'] ?? $result['checkoutStatus'] ?? '' ) )
				);
			} elseif ( ! empty( $result['id'] ) && isset( $result['expires'] ) && empty( $result['invoiceType'] ) && empty( $result['quoteUrl'] ) ) {
				$message = sprintf(
					/* translators: %s: session id */
					esc_html__( 'Session %s created successfully.', 'vms-elements-fastspring-woo-payment' ),
					esc_html( (string) $result['id'] )
				);
			} elseif ( ! empty( $result['id'] ) && isset( $result['quoteUrl'] ) ) {
				$message = sprintf(
					/* translators: 1: quote id, 2: status */
					esc_html__( 'Quote %1$s saved (%2$s).', 'vms-elements-fastspring-woo-payment' ),
					esc_html( (string) $result['id'] ),
					esc_html( (string) ( $result['status'] ?? '' ) )
				);
			} elseif ( ! empty( $result['id'] ) && ( isset( $result['status'] ) || isset( $result['invoiceType'] ) ) ) {
				$message = sprintf(
					/* translators: 1: invoice id, 2: status */
					esc_html__( 'Invoice %1$s created (%2$s).', 'vms-elements-fastspring-woo-payment' ),
					esc_html( (string) $result['id'] ),
					esc_html( (string) ( $result['status'] ?? '' ) )
				);
			}
		}

		echo '<div class="notice notice-success"><p>' . wp_kses_post( $message ) . '</p></div>';
	}

	/**
	 * Helper to render a "View JSON" button for a row.
	 *
	 * @param mixed $row Row data.
	 */
	public static function render_view_button( $row ) {
		$json = wp_json_encode( $row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
		printf(
			'<button type="button" class="button button-small vms-efwp-view-json" data-json="%s">%s</button>',
			esc_attr( $json ),
			esc_html__( 'View', 'vms-elements-fastspring-woo-payment' )
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
		$settings = vms_efwp()->settings;
		if ( ! $settings->has_credentials() ) {
			printf(
				'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
				esc_html__( 'FastSpring API credentials are not configured for the active mode.', 'vms-elements-fastspring-woo-payment' ),
				esc_url( admin_url( 'admin.php?page=vms-efwp-settings' ) ),
				esc_html__( 'Open settings', 'vms-elements-fastspring-woo-payment' )
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
		$mode = vms_efwp()->settings->get_mode();
		?>
		<div class="vms-efwp-header">
			<div class="vms-efwp-header__title">
				<h1><?php echo esc_html( $title ); ?></h1>
				<span class="vms-efwp-mode-pill vms-efwp-mode-pill--<?php echo esc_attr( $mode ); ?>">
					<?php echo 'live' === $mode ? esc_html__( 'LIVE', 'vms-elements-fastspring-woo-payment' ) : esc_html__( 'SANDBOX', 'vms-elements-fastspring-woo-payment' ); ?>
				</span>
				<?php if ( $subtitle ) : ?>
					<span class="vms-efwp-subtitle"><?php echo esc_html( $subtitle ); ?></span>
				<?php endif; ?>
			</div>
			<div class="vms-efwp-header__actions">
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

		check_admin_referer( $nonce_action );
		return true;
	}

	/**
	 * Whether a POST submit button/flag was used.
	 *
	 * @param string $field Submit field name.
	 * @return bool
	 */
	public static function is_post_submit( $field ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Paired with verify_post().
		return ! empty( $_POST[ $field ] );
	}

	/**
	 * Read a sanitized GET filter value (read-only admin UI).
	 *
	 * @param string $key     Query arg.
	 * @param string $default Default value.
	 * @return string
	 */
	public static function get_filter_text( $key, $default = '' ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list filter; requires manage_options.
		if ( ! isset( $_GET[ $key ] ) ) {
			return $default;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized via sanitize_text_field().
		$raw = wp_unslash( $_GET[ $key ] );

		return sanitize_text_field( $raw );
	}

	/**
	 * Read a sanitized GET integer filter value.
	 *
	 * @param string $key     Query arg.
	 * @param int    $default Default value.
	 * @return int
	 */
	public static function get_filter_int( $key, $default = 0 ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin list filter; requires manage_options.
		if ( ! isset( $_GET[ $key ] ) ) {
			return $default;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized via absint().
		$raw = wp_unslash( $_GET[ $key ] );

		return absint( $raw );
	}

	/**
	 * Read a sanitized GET key filter value.
	 *
	 * @param string $key     Query arg.
	 * @param string $default Default value.
	 * @return string
	 */
	public static function get_filter_key( $key, $default = '' ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin tab/filter; requires manage_options.
		if ( ! isset( $_GET[ $key ] ) ) {
			return $default;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized via sanitize_key().
		$raw = wp_unslash( $_GET[ $key ] );

		return sanitize_key( $raw );
	}

	/**
	 * Read a sanitized GET value after verifying an admin nonce.
	 *
	 * @param string $key          Query arg.
	 * @param string $nonce_action Nonce action.
	 * @param string $default      Default value.
	 * @return string
	 */
	public static function verified_get_text( $key, $nonce_action, $default = '' ) {
		check_admin_referer( $nonce_action );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above; value sanitized below.
		if ( ! isset( $_GET[ $key ] ) ) {
			return $default;
		}

		return sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
	}

	/**
	 * Read sanitized POST text (call verify_post() first).
	 *
	 * @param string $key     Field name.
	 * @param string $default Default value.
	 * @return string
	 */
	public static function post_text( $key, $default = '' ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verify_post() must run first; value sanitized below.
		return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : $default;
	}

	/**
	 * Normalize a FastSpring product path (slug).
	 *
	 * Converts human-readable text such as "VMS Fastspring Plugin" to "vms-fastspring-plugin".
	 * Falls back to the display name when the path field is empty.
	 *
	 * @param string $path          Raw path from the form.
	 * @param string $fallback_name Display name used when path is empty.
	 * @return string
	 */
	public static function sanitize_product_path( $path, $fallback_name = '' ) {
		$path = sanitize_title( (string) $path );
		if ( '' === $path && '' !== $fallback_name ) {
			$path = sanitize_title( $fallback_name );
		}
		return $path;
	}

	/**
	 * Read sanitized POST email (call verify_post() first).
	 *
	 * @param string $key     Field name.
	 * @param string $default Default value.
	 * @return string
	 */
	public static function post_email( $key, $default = '' ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verify_post() must run first; value sanitized below.
		return isset( $_POST[ $key ] ) ? sanitize_email( wp_unslash( $_POST[ $key ] ) ) : $default;
	}

	/**
	 * Read sanitized POST textarea (call verify_post() first).
	 *
	 * @param string $key     Field name.
	 * @param string $default Default value.
	 * @return string
	 */
	public static function post_textarea( $key, $default = '' ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verify_post() must run first; value sanitized below.
		return isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : $default;
	}

	/**
	 * Read sanitized POST float (call verify_post() first).
	 *
	 * @param string $key     Field name.
	 * @param float  $default Default value.
	 * @return float
	 */
	public static function post_float( $key, $default = 0.0 ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verify_post() must run first.
		if ( ! isset( $_POST[ $key ] ) ) {
			return $default;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verify_post() must run first; sanitized below.
		$raw = wp_unslash( $_POST[ $key ] );

		return (float) sanitize_text_field( $raw );
	}

	/**
	 * Read sanitized POST integer (call verify_post() first).
	 *
	 * @param string $key     Field name.
	 * @param int    $default Default value.
	 * @return int
	 */
	public static function post_int( $key, $default = 0 ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verify_post() must run first.
		if ( ! isset( $_POST[ $key ] ) ) {
			return $default;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verify_post() must run first; sanitized below.
		$raw = wp_unslash( $_POST[ $key ] );

		return absint( $raw );
	}

	/**
	 * Read sanitized POST boolean checkbox (call verify_post() first).
	 *
	 * @param string $key     Field name.
	 * @param bool   $default Default when the field is absent.
	 * @return bool
	 */
	public static function post_bool( $key, $default = false ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verify_post() must run first.
		if ( ! isset( $_POST[ $key ] ) ) {
			return $default;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verify_post() must run first; sanitized below.
		$raw = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );

		return in_array( strtolower( $raw ), array( '1', 'true', 'on', 'yes' ), true );
	}

	/**
	 * Read unslashed POST array (call verify_post() first).
	 *
	 * @param string $key     Field name.
	 * @param array  $default Default value.
	 * @return array
	 */
	public static function post_array( $key, $default = array() ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- verify_post() must run first; nested values sanitized by caller.
		$value = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : $default;
		return is_array( $value ) ? $value : $default;
	}

	/**
	 * Render horizontal admin nav tabs.
	 *
	 * @param array  $tabs    Tab key => label.
	 * @param string $current Active tab key.
	 * @param string $base_url Base admin URL without tab arg.
	 */
	public static function render_nav_tabs( $tabs, $current, $base_url ) {
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $key => $label ) {
			printf(
				'<a href="%s" class="nav-tab %s">%s</a>',
				esc_url( add_query_arg( 'tab', $key, $base_url ) ),
				$key === $current ? 'nav-tab-active' : '',
				esc_html( $label )
			);
		}
		echo '</h2>';
	}

	/**
	 * Render a lookup-by-ID form.
	 *
	 * @param string $page        Admin page slug.
	 * @param string $field       Query arg for the ID.
	 * @param string $label       Field label.
	 * @param string $placeholder Input placeholder.
	 * @param string $value       Current value.
	 * @param array  $preserve    Extra query args to preserve.
	 */
	public static function render_lookup_form( $page, $field, $label, $placeholder = '', $value = '', $preserve = array() ) {
		?>
		<form method="get" class="vms-efwp-filters vms-efwp-filters--lookup">
			<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
			<?php foreach ( $preserve as $key => $preserve_value ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $preserve_value ); ?>" />
			<?php endforeach; ?>
			<label>
				<?php echo esc_html( $label ); ?>
				<input type="text" name="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" class="regular-text" />
			</label>
			<button class="button button-primary"><?php esc_html_e( 'Lookup', 'vms-elements-fastspring-woo-payment' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Render an API detail card with JSON view.
	 *
	 * @param mixed  $result API result.
	 * @param string $title  Card title.
	 */
	public static function render_api_detail_card( $result, $title = '' ) {
		if ( null === $result ) {
			return;
		}

		if ( is_wp_error( $result ) ) {
			self::render_result_notice( $result );
			return;
		}

		if ( '' === $title ) {
			$title = __( 'API response', 'vms-elements-fastspring-woo-payment' );
		}
		?>
		<div class="vms-efwp-card vms-efwp-card--wide vms-efwp-api-detail">
			<div class="vms-efwp-card__head">
				<h2><?php echo esc_html( $title ); ?></h2>
				<?php self::render_view_button( $result ); ?>
			</div>
			<pre class="vms-efwp-json vms-efwp-json--inline"><?php echo esc_html( wp_json_encode( $result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) ); ?></pre>
		</div>
		<?php
	}
}
