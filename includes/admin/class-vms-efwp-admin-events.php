<?php
/**
 * Events screen (historical event query against FastSpring API).
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Events.
 */
class VMS_EFWP_Admin_Events {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab  = VMS_EFWP_Admin_Resource_Base::get_filter_key( 'tab', 'unprocessed' );
		$tabs = array(
			'unprocessed' => __( 'Unprocessed', 'vms-elements-fastspring-woo-payment' ),
			'processed'   => __( 'Processed', 'vms-elements-fastspring-woo-payment' ),
		);
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'unprocessed';
		}
		$base = admin_url( 'admin.php?page=vms-efwp-events' );

		echo '<div class="wrap vefwp-wrap">';
		VMS_EFWP_Admin_Resource_Base::render_header(
			__( 'Events', 'vms-elements-fastspring-woo-payment' ),
			__( 'Query and update FastSpring webhook events.', 'vms-elements-fastspring-woo-payment' )
		);

		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		self::handle_event_actions( $tab );

		VMS_EFWP_Admin_Resource_Base::render_nav_tabs( $tabs, $tab, $base );
		self::render_list( $tab );

		VMS_EFWP_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}

	/**
	 * Handle mark processed/unprocessed actions.
	 *
	 * @param string $tab Active tab.
	 */
	private static function handle_event_actions( $tab ) {
		$event_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'event_id' );
		$action   = VMS_EFWP_Admin_Resource_Base::get_filter_key( 'event_action' );

		if ( ! $event_id || ! $action ) {
			return;
		}

		check_admin_referer( 'wpfs_event_action_' . $event_id );

		if ( 'mark_processed' === $action ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( vms_efwp()->api->mark_event_processed( $event_id ) );
		} elseif ( 'mark_unprocessed' === $action ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( vms_efwp()->api->mark_event_unprocessed( $event_id ) );
		}
	}

	/**
	 * Render event list for a tab.
	 *
	 * @param string $type processed|unprocessed.
	 */
	private static function render_list( $type ) {
		$days  = max( 1, min( 30, VMS_EFWP_Admin_Resource_Base::get_filter_int( 'days', 7 ) ) );
		$begin = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'begin' );
		$end   = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'end' );

		$params = array(
			'days' => $days,
		);
		if ( $begin ) {
			$params['begin'] = $begin;
		}
		if ( $end ) {
			$params['end'] = $end;
		}

		$result = vms_efwp()->api->list_events( $type, $params );
		$events = array();
		$meta   = array(
			'total'    => 0,
			'page'     => 1,
			'nextPage' => null,
		);

		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$events = $result['events'] ?? array();
			$meta   = array(
				'total'    => (int) ( $result['total'] ?? count( $events ) ),
				'page'     => (int) ( $result['page'] ?? 1 ),
				'nextPage' => $result['nextPage'] ?? null,
			);
		}
		?>
		<form method="get" class="vefwp-filters">
			<input type="hidden" name="page" value="vms-efwp-events" />
			<input type="hidden" name="tab" value="<?php echo esc_attr( $type ); ?>" />
			<select name="days">
				<?php foreach ( array( 1, 3, 7, 14, 30 ) as $d ) : ?>
					<option value="<?php echo esc_attr( $d ); ?>" <?php selected( $days, $d ); ?>>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: number of days */
								_n( 'Last %d day', 'Last %d days', $d, 'vms-elements-fastspring-woo-payment' ),
								$d
							)
						);
						?>
					</option>
				<?php endforeach; ?>
			</select>
			<input type="date" name="begin" value="<?php echo esc_attr( $begin ); ?>" placeholder="<?php esc_attr_e( 'Begin', 'vms-elements-fastspring-woo-payment' ); ?>" />
			<input type="date" name="end" value="<?php echo esc_attr( $end ); ?>" placeholder="<?php esc_attr_e( 'End', 'vms-elements-fastspring-woo-payment' ); ?>" />
			<button class="button"><?php esc_html_e( 'Refresh', 'vms-elements-fastspring-woo-payment' ); ?></button>
		</form>

		<?php if ( $meta['total'] > 0 ) : ?>
			<p class="description">
				<?php
				printf(
					/* translators: %d: number of events returned */
					esc_html__( '%d events returned (FastSpring returns up to 25 per request — narrow the date range to see more).', 'vms-elements-fastspring-woo-payment' ),
					(int) $meta['total']
				);
				?>
			</p>
		<?php endif; ?>

		<table class="widefat striped vefwp-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Event ID', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Type', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Resource', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Account', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Live', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Created', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'vms-elements-fastspring-woo-payment' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $events ) ) : ?>
				<?php VMS_EFWP_Admin_Resource_Base::render_empty_row( __( 'No events found for this range.', 'vms-elements-fastspring-woo-payment' ), 7 ); ?>
			<?php else : ?>
				<?php foreach ( $events as $event ) : ?>
					<?php
					$event_id = $event['id'] ?? '';
					$data     = is_array( $event['data'] ?? null ) ? $event['data'] : array();
					$resource = $data['id'] ?? '';
					$account  = $data['account'] ?? '';
					$email    = $data['contact']['email'] ?? '';
					?>
					<tr>
						<td><code><?php echo esc_html( $event_id ); ?></code></td>
						<td><?php echo esc_html( $event['type'] ?? '' ); ?></td>
						<td>
							<?php if ( $resource ) : ?>
								<code><?php echo esc_html( $resource ); ?></code>
							<?php else : ?>
								&mdash;
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $account ) : ?>
								<code><?php echo esc_html( $account ); ?></code>
							<?php elseif ( $email ) : ?>
								<?php echo esc_html( $email ); ?>
							<?php else : ?>
								&mdash;
							<?php endif; ?>
						</td>
						<td><?php echo ! empty( $event['live'] ) ? '<span class="vefwp-badge vefwp-badge--ok">LIVE</span>' : '<span class="vefwp-badge vefwp-badge--warning">TEST</span>'; ?></td>
						<td><?php echo esc_html( self::format_event_created( $event['created'] ?? null ) ); ?></td>
						<td class="vefwp-row-actions">
							<?php VMS_EFWP_Admin_Resource_Base::render_view_button( $event ); ?>
							<?php if ( $event_id ) : ?>
								<?php if ( 'unprocessed' === $type ) : ?>
									<a class="button button-small" href="<?php echo esc_url( self::event_action_url( $type, $event_id, 'mark_processed', $days, $begin, $end ) ); ?>"><?php esc_html_e( 'Mark processed', 'vms-elements-fastspring-woo-payment' ); ?></a>
								<?php else : ?>
									<a class="button button-small" href="<?php echo esc_url( self::event_action_url( $type, $event_id, 'mark_unprocessed', $days, $begin, $end ) ); ?>"><?php esc_html_e( 'Mark unprocessed', 'vms-elements-fastspring-woo-payment' ); ?></a>
								<?php endif; ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Build an event action URL.
	 *
	 * @param string $tab        Active tab.
	 * @param string $event_id   Event ID.
	 * @param string $action     Action key.
	 * @param int    $days       Days filter.
	 * @param string $begin      Begin date.
	 * @param string $end        End date.
	 * @return string
	 */
	private static function event_action_url( $tab, $event_id, $action, $days, $begin, $end ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'page'         => 'vms-efwp-events',
					'tab'          => $tab,
					'days'         => $days,
					'begin'        => $begin,
					'end'          => $end,
					'event_id'     => $event_id,
					'event_action' => $action,
				),
				admin_url( 'admin.php' )
			),
			'wpfs_event_action_' . $event_id
		);
	}

	/**
	 * Format event created timestamp.
	 *
	 * @param mixed $created Milliseconds since epoch.
	 * @return string
	 */
	private static function format_event_created( $created ) {
		if ( null === $created || '' === $created ) {
			return '';
		}

		$timestamp = (int) $created;
		if ( $timestamp > 9999999999 ) {
			$timestamp = (int) floor( $timestamp / 1000 );
		}

		return gmdate( 'Y-m-d H:i:s', $timestamp ) . ' UTC';
	}
}
