<?php
/**
 * Webhooks screen (HMAC management).
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Webhooks.
 */
class VMS_EFWP_Admin_Webhooks {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap vefwp-wrap">';
		VMS_EFWP_Admin_Resource_Base::render_header(
			__( 'Webhooks', 'vms-elements-fastspring-woo-payment' ),
			__( 'Configured webhook endpoints, event permissions, and HMAC secret rotation.', 'vms-elements-fastspring-woo-payment' )
		);

		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		$settings    = vms_efwp()->settings;
		$permissions = new VMS_EFWP_Webhook_Permissions( vms_efwp()->api, $settings );

		self::handle_actions( $settings, $permissions );

		$result = vms_efwp()->api->get_webhooks();
		$hooks  = array();
		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$parsed = vms_efwp()->api->parse_webhooks_list( $result );
			$hooks  = is_wp_error( $parsed ) ? array() : $parsed;
			if ( is_wp_error( $parsed ) ) {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $parsed );
			}
		}

		self::render_receiver_card( $settings );
		self::render_permissions_card( $permissions, $settings );
		self::render_hooks_table( $hooks, $settings );
		self::render_json_modal();
		echo '</div>';
	}

	/**
	 * Handle rotate/view HMAC and permission refresh actions.
	 *
	 * @param VMS_EFWP_Settings            $settings    Settings.
	 * @param VMS_EFWP_Webhook_Permissions $permissions Permissions helper.
	 */
	private static function handle_actions( VMS_EFWP_Settings $settings, VMS_EFWP_Webhook_Permissions $permissions ) {
		$refresh = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'refresh_permissions' );
		if ( $refresh ) {
			check_admin_referer( 'vms_efwp_refresh_webhook_permissions' );
			$synced = $permissions->refresh();
			if ( is_wp_error( $synced ) ) {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $synced );
			} else {
				$count = in_array( '*', $synced, true ) ? __( 'all events', 'vms-elements-fastspring-woo-payment' ) : (string) count( $synced );
				printf(
					'<div class="notice notice-success"><p>%s</p></div>',
					esc_html(
						sprintf(
							/* translators: %s: number of enabled events or "all events" */
							__( 'Webhook permissions synced from FastSpring (%s enabled for this receiver URL).', 'vms-elements-fastspring-woo-payment' ),
							$count
						)
					)
				);
			}
		}

		$rotate_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'rotate' );
		$view_hmac = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'view_hmac' );
		if ( $view_hmac ) {
			check_admin_referer( 'wpfs_view_hmac' );
			$hmac_result = vms_efwp()->api->get_webhook_hmac( $view_hmac );
			if ( is_wp_error( $hmac_result ) ) {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $hmac_result );
			} else {
				$secret = $hmac_result['hmacSecret'] ?? ( $hmac_result['secret'] ?? ( $hmac_result['hmac']['secret'] ?? '' ) );
				if ( $secret ) {
					printf(
						'<div class="notice notice-info"><p>%s <code>%s</code></p></div>',
						esc_html__( 'Current HMAC secret:', 'vms-elements-fastspring-woo-payment' ),
						esc_html( $secret )
					);
				} else {
					VMS_EFWP_Admin_Resource_Base::render_api_detail_card( $hmac_result, __( 'Webhook HMAC', 'vms-elements-fastspring-woo-payment' ) );
				}
			}
		}
		if ( $rotate_id ) {
			check_admin_referer( 'wpfs_rotate_hmac' );
			$rotation_result = vms_efwp()->api->rotate_webhook_hmac( $rotate_id );
			if ( ! is_wp_error( $rotation_result ) ) {
				$secret = $rotation_result['hmacSecret'] ?? ( $rotation_result['secret'] ?? ( $rotation_result['hmac']['secret'] ?? '' ) );
				if ( $secret ) {
					printf( '<div class="notice notice-success"><p>%s <code>%s</code></p></div>', esc_html__( 'HMAC secret rotated and saved to plugin settings.', 'vms-elements-fastspring-woo-payment' ), esc_html( $secret ) );
					if ( $settings->is_sandbox() ) {
						$settings->set( 'webhook_secret_sandbox', $secret );
					} else {
						$settings->set( 'webhook_secret_live', $secret );
					}
					$settings->refresh();
				} else {
					VMS_EFWP_Admin_Resource_Base::render_result_notice( $rotation_result );
				}
			} else {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $rotation_result );
			}
		}
	}

	/**
	 * Render receiver URL card.
	 *
	 * @param VMS_EFWP_Settings $settings Settings.
	 */
	private static function render_receiver_card( VMS_EFWP_Settings $settings ) {
		?>
		<div class="vefwp-card">
			<h2><?php esc_html_e( 'Receiver endpoint', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Configure the URL below in FastSpring App > Integrations > Webhooks. Use HMAC SHA256 signing and copy the secret into Settings.', 'vms-elements-fastspring-woo-payment' ); ?>
			</p>
			<input type="text" readonly value="<?php echo esc_attr( $settings->webhook_url() ); ?>" class="large-text vefwp-readonly" onclick="this.select()" />
		</div>
		<?php
	}

	/**
	 * Render event permissions matrix.
	 *
	 * @param VMS_EFWP_Webhook_Permissions $permissions Permissions helper.
	 * @param VMS_EFWP_Settings            $settings    Settings.
	 */
	private static function render_permissions_card( VMS_EFWP_Webhook_Permissions $permissions, VMS_EFWP_Settings $settings ) {
		if ( ! $permissions->has_synced_permissions() && $settings->has_credentials() ) {
			$permissions->refresh();
		}

		$statuses  = $permissions->get_handler_statuses();
		$synced_at = (int) $settings->get( ( $settings->is_sandbox() ? 'webhook_enabled_events_sandbox' : 'webhook_enabled_events_live' ) . '_synced_at', 0 );
		$refresh_url = wp_nonce_url(
			add_query_arg(
				array(
					'page'                 => 'vms-efwp-webhooks',
					'refresh_permissions'  => '1',
				),
				admin_url( 'admin.php' )
			),
			'vms_efwp_refresh_webhook_permissions'
		);
		?>
		<div class="vefwp-card">
			<div class="vefwp-card__header-row">
				<h2><?php esc_html_e( 'Event permissions', 'vms-elements-fastspring-woo-payment' ); ?></h2>
				<a class="button" href="<?php echo esc_url( $refresh_url ); ?>"><?php esc_html_e( 'Refresh from FastSpring', 'vms-elements-fastspring-woo-payment' ); ?></a>
			</div>
			<p class="description">
				<?php esc_html_e( 'The plugin reads which events are enabled on your FastSpring webhook URL and only applies handlers for those event types. For example, subscription.trial.reminder is processed only when you tick it in FastSpring.', 'vms-elements-fastspring-woo-payment' ); ?>
			</p>
			<?php if ( $synced_at ) : ?>
				<p class="description">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: localized datetime */
							__( 'Last synced: %s', 'vms-elements-fastspring-woo-payment' ),
							wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $synced_at )
						)
					);
					?>
				</p>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'Permissions not synced yet — click Refresh from FastSpring after configuring your webhook URL.', 'vms-elements-fastspring-woo-payment' ); ?></p>
			<?php endif; ?>

			<table class="widefat striped vefwp-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Event', 'vms-elements-fastspring-woo-payment' ); ?></th>
						<th><?php esc_html_e( 'Category', 'vms-elements-fastspring-woo-payment' ); ?></th>
						<th><?php esc_html_e( 'Plugin handler', 'vms-elements-fastspring-woo-payment' ); ?></th>
						<th><?php esc_html_e( 'FastSpring', 'vms-elements-fastspring-woo-payment' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $statuses as $type => $status ) : ?>
					<tr>
						<td><code><?php echo esc_html( $type ); ?></code></td>
						<td><?php echo esc_html( $status['category'] ); ?></td>
						<td>
							<?php echo esc_html( $status['description'] ); ?>
							<?php if ( ! empty( $status['required'] ) ) : ?>
								<span class="vefwp-badge vefwp-badge--required"><?php esc_html_e( 'Required', 'vms-elements-fastspring-woo-payment' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<?php
							if ( null === $status['enabled'] ) {
								esc_html_e( 'Unknown', 'vms-elements-fastspring-woo-payment' );
							} elseif ( $status['enabled'] ) {
								echo '<span class="vefwp-badge vefwp-badge--success">' . esc_html__( 'Enabled', 'vms-elements-fastspring-woo-payment' ) . '</span>';
							} else {
								echo '<span class="vefwp-badge vefwp-badge--muted">' . esc_html__( 'Disabled', 'vms-elements-fastspring-woo-payment' ) . '</span>';
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render configured webhooks table.
	 *
	 * @param array             $hooks    Webhook rows.
	 * @param VMS_EFWP_Settings $settings Settings.
	 */
	private static function render_hooks_table( $hooks, VMS_EFWP_Settings $settings ) {
		?>
		<div class="vefwp-card">
			<h2><?php esc_html_e( 'Configured webhooks', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<table class="widefat striped vefwp-table">
				<thead><tr>
					<th><?php esc_html_e( 'Webhook ID', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'URL', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Events', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th></th>
				</tr></thead>
				<tbody>
				<?php if ( empty( $hooks ) ) : ?>
					<?php VMS_EFWP_Admin_Resource_Base::render_empty_row( __( 'No webhooks configured in FastSpring yet.', 'vms-elements-fastspring-woo-payment' ), 4 ); ?>
				<?php else : ?>
					<?php foreach ( $hooks as $h ) : ?>
						<?php
						$wid = $h['id'] ?? '';
						$url = $h['url'] ?? '';
						if ( empty( $url ) && ! empty( $h['endpoints'][0]['url'] ) ) {
							$url = $h['endpoints'][0]['url'];
						}
						$events = $h['events'] ?? ( $h['endpoints'][0]['events'] ?? array() );
						$events_display = vms_efwp()->api->parse_webhook_events( $events );
						if ( in_array( '*', $events_display, true ) ) {
							$events_display = array( __( 'All events', 'vms-elements-fastspring-woo-payment' ) );
						}
						$is_receiver = $url && vms_efwp()->api->webhook_urls_match( $url, $settings->webhook_url() );
						$rotate_url = wp_nonce_url(
							add_query_arg( array( 'page' => 'vms-efwp-webhooks', 'rotate' => $wid ), admin_url( 'admin.php' ) ),
							'wpfs_rotate_hmac'
						);
						$view_url = wp_nonce_url(
							add_query_arg( array( 'page' => 'vms-efwp-webhooks', 'view_hmac' => $wid ), admin_url( 'admin.php' ) ),
							'wpfs_view_hmac'
						);
						?>
						<tr<?php echo $is_receiver ? ' class="is-active"' : ''; ?>>
							<td><code><?php echo esc_html( $wid ); ?></code></td>
							<td>
								<?php echo esc_html( $url ); ?>
								<?php if ( $is_receiver ) : ?>
									<span class="vefwp-badge vefwp-badge--success"><?php esc_html_e( 'This site', 'vms-elements-fastspring-woo-payment' ); ?></span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( implode( ', ', $events_display ) ); ?></td>
							<td>
								<?php VMS_EFWP_Admin_Resource_Base::render_view_button( $h ); ?>
								<a class="button button-small" href="<?php echo esc_url( $view_url ); ?>"><?php esc_html_e( 'View HMAC', 'vms-elements-fastspring-woo-payment' ); ?></a>
								<a class="button button-small" href="<?php echo esc_url( $rotate_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Rotate HMAC secret? Existing signatures will fail until you update the receiver.', 'vms-elements-fastspring-woo-payment' ); ?>');"><?php esc_html_e( 'Rotate HMAC', 'vms-elements-fastspring-woo-payment' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render JSON modal once per page.
	 */
	private static function render_json_modal() {
		VMS_EFWP_Admin_Resource_Base::render_json_modal();
	}
}
