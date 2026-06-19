<?php
/**
 * Reports / Data screen.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Reports.
 */
class VMS_EFWP_Admin_Reports {

	const TRANSIENT_KEY = 'vms_efwp_recent_reports';

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab  = VMS_EFWP_Admin_Resource_Base::get_filter_key( 'tab', 'generate' );
		$tabs = array(
			'generate' => __( 'Generate', 'vms-elements-fastspring-woo-payment' ),
			'jobs'     => __( 'Jobs', 'vms-elements-fastspring-woo-payment' ),
			'lookup'   => __( 'Lookup', 'vms-elements-fastspring-woo-payment' ),
			'tools'    => __( 'Tools', 'vms-elements-fastspring-woo-payment' ),
		);
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'generate';
		}
		$base = admin_url( 'admin.php?page=vms-efwp-reports' );

		echo '<div class="wrap vefwp-wrap">';
		VMS_EFWP_Admin_Resource_Base::render_header(
			__( 'Reports', 'vms-elements-fastspring-woo-payment' ),
			__( 'Generate and download FastSpring revenue and subscription reports.', 'vms-elements-fastspring-woo-payment' )
		);

		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		$sync_report = null;
		$download    = self::handle_download();

		if ( 'generate' === $tab && VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_create_report' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_create_report' ) ) {
			$sync_report = self::handle_generate();
		}

		if ( 'tools' === $tab && VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_reset_data_cache' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_reset_data_cache' ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( vms_efwp()->api->reset_data_cache() );
		}

		VMS_EFWP_Admin_Resource_Base::render_nav_tabs( $tabs, $tab, $base );

		if ( 'jobs' === $tab ) {
			self::render_jobs_tab();
		} elseif ( 'lookup' === $tab ) {
			self::render_lookup_tab();
		} elseif ( 'tools' === $tab ) {
			self::render_tools_tab();
		} else {
			self::render_generate_tab( $sync_report );
		}

		if ( $download ) {
			self::render_download_card( $download );
		}

		VMS_EFWP_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}

	/**
	 * Handle report download action.
	 *
	 * @return string|WP_Error|null
	 */
	private static function handle_download() {
		$job_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'download' );
		if ( ! $job_id ) {
			return null;
		}

		check_admin_referer( 'wpfs_download_report' );
		return vms_efwp()->api->download_report( $job_id );
	}

	/**
	 * Handle generate report form.
	 *
	 * @return array|null Sync report payload when generated inline.
	 */
	private static function handle_generate() {
		$type = VMS_EFWP_Admin_Resource_Base::post_text( 'report_type', 'revenue' );
		$body = self::build_report_request_from_post();

		if ( 'subscription' === $type ) {
			$result = vms_efwp()->api->generate_subscription_report( $body );
		} else {
			$result = vms_efwp()->api->generate_revenue_report( $body );
		}

		if ( ! is_wp_error( $result ) && 'async' === ( $result['mode'] ?? '' ) ) {
			$job_id = vms_efwp()->api->extract_data_job_id( $result );
			if ( $job_id ) {
				self::remember_job(
					$job_id,
					$type,
					$body['filter'] ?? array(),
					$result['job']['status'] ?? '',
					$result['job']['name'] ?? ''
				);
			}
		}

		VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );

		if ( ! is_wp_error( $result ) && 'sync' === ( $result['mode'] ?? '' ) ) {
			return $result;
		}

		return null;
	}

	/**
	 * Build report request body from POST fields.
	 *
	 * @return array
	 */
	private static function build_report_request_from_post() {
		$params = array(
			'begin' => VMS_EFWP_Admin_Resource_Base::post_text( 'begin', gmdate( 'Y-m-01' ) ),
			'end'   => VMS_EFWP_Admin_Resource_Base::post_text( 'end', gmdate( 'Y-m-d' ) ),
			'async' => ! empty( $_POST['async'] ), // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by caller.
		);

		$sync_date = VMS_EFWP_Admin_Resource_Base::post_text( 'sync_date' );
		if ( $sync_date ) {
			$params['syncDate'] = $sync_date;
		}

		$country_iso = self::lines_to_array( VMS_EFWP_Admin_Resource_Base::post_text( 'country_iso' ) );
		if ( $country_iso ) {
			$params['countryISO'] = $country_iso;
		}

		$product_paths = self::lines_to_array( VMS_EFWP_Admin_Resource_Base::post_text( 'product_paths' ) );
		if ( $product_paths ) {
			$params['productPaths'] = $product_paths;
		}

		$product_names = self::lines_to_array( VMS_EFWP_Admin_Resource_Base::post_text( 'product_names' ) );
		if ( $product_names ) {
			$params['productNames'] = $product_names;
		}

		$segments = self::lines_to_array( VMS_EFWP_Admin_Resource_Base::post_text( 'segments' ) );
		if ( $segments ) {
			$params['segments'] = $segments;
		}

		$report_columns = self::lines_to_array( VMS_EFWP_Admin_Resource_Base::post_textarea( 'report_columns' ) );
		if ( $report_columns ) {
			$params['reportColumns'] = $report_columns;
		}

		$group_by = self::lines_to_array( VMS_EFWP_Admin_Resource_Base::post_textarea( 'group_by' ) );
		if ( $group_by ) {
			$params['groupBy'] = $group_by;
		}

		$page_count = VMS_EFWP_Admin_Resource_Base::post_int( 'page_count' );
		if ( $page_count > 0 ) {
			$params['pageCount'] = min( 1000, $page_count );
		}

		$page_number = VMS_EFWP_Admin_Resource_Base::post_int( 'page_number' );
		if ( $page_number > 0 ) {
			$params['pageNumber'] = $page_number;
		}

		$emails = self::lines_to_array( VMS_EFWP_Admin_Resource_Base::post_textarea( 'notification_emails' ) );
		if ( $emails ) {
			$params['notificationEmails'] = array_map( 'sanitize_email', $emails );
		}

		return vms_efwp()->api->build_data_report_request( $params );
	}

	/**
	 * Split comma/newline separated input into a list.
	 *
	 * @param string $text Raw input.
	 * @return array
	 */
	private static function lines_to_array( $text ) {
		if ( '' === trim( (string) $text ) ) {
			return array();
		}

		$parts = preg_split( '/[\r\n,]+/', (string) $text );
		$parts = array_map( 'trim', (array) $parts );

		return array_values( array_filter( $parts ) );
	}

	/**
	 * Render generate tab.
	 *
	 * @param array|null $sync_report Inline sync report payload.
	 */
	private static function render_generate_tab( $sync_report = null ) {
		$recent = self::get_recent_jobs();
		?>
		<div class="vefwp-grid vefwp-grid--two">
			<div class="vefwp-card">
				<h2><?php esc_html_e( 'Generate report', 'vms-elements-fastspring-woo-payment' ); ?></h2>
				<form method="post">
					<?php wp_nonce_field( 'wpfs_create_report' ); ?>
					<input type="hidden" name="wpfs_create_report" value="1" />
					<p><label><?php esc_html_e( 'Report type', 'vms-elements-fastspring-woo-payment' ); ?><br />
						<select name="report_type">
							<option value="revenue"><?php esc_html_e( 'Revenue', 'vms-elements-fastspring-woo-payment' ); ?></option>
							<option value="subscription"><?php esc_html_e( 'Subscription', 'vms-elements-fastspring-woo-payment' ); ?></option>
						</select>
					</label></p>
					<p><label><?php esc_html_e( 'Start date', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="date" name="begin" value="<?php echo esc_attr( gmdate( 'Y-m-01' ) ); ?>" required /></label></p>
					<p><label><?php esc_html_e( 'End date', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="date" name="end" value="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>" required /></label></p>
					<p><label><?php esc_html_e( 'Sync date (optional)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="date" name="sync_date" /></label></p>
					<p><label><?php esc_html_e( 'Country ISO codes (optional)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="country_iso" placeholder="US, CO" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Product paths (optional)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="product_paths" placeholder="my-product" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Product names (optional)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="product_names" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Segments (optional)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="segments" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Report columns (optional)', 'vms-elements-fastspring-woo-payment' ); ?><br /><textarea name="report_columns" rows="3" class="large-text" placeholder="income, order_id, product_path"></textarea></label></p>
					<p><label><?php esc_html_e( 'Group by (optional)', 'vms-elements-fastspring-woo-payment' ); ?><br /><textarea name="group_by" rows="2" class="large-text" placeholder="product_path, country_iso"></textarea></label></p>
					<p>
						<label><?php esc_html_e( 'Page count', 'vms-elements-fastspring-woo-payment' ); ?>
							<input type="number" min="1" max="1000" name="page_count" value="30" class="small-text" />
						</label>
						<label><?php esc_html_e( 'Page number', 'vms-elements-fastspring-woo-payment' ); ?>
							<input type="number" min="1" name="page_number" value="1" class="small-text" />
						</label>
					</p>
					<p><label><input type="checkbox" name="async" value="1" /> <?php esc_html_e( 'Generate asynchronously (returns a job id)', 'vms-elements-fastspring-woo-payment' ); ?></label></p>
					<p><label><?php esc_html_e( 'Notification emails (optional)', 'vms-elements-fastspring-woo-payment' ); ?><br /><textarea name="notification_emails" rows="2" class="large-text" placeholder="admin@example.com"></textarea></label></p>
					<p><button class="button button-primary"><?php esc_html_e( 'Generate', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
				</form>
			</div>

			<div class="vefwp-card">
				<h2><?php esc_html_e( 'Recent jobs', 'vms-elements-fastspring-woo-payment' ); ?></h2>
				<?php self::render_jobs_table( $recent, true ); ?>
			</div>
		</div>

		<?php if ( $sync_report && ! empty( $sync_report['report'] ) ) : ?>
			<div class="vefwp-card">
				<h2><?php esc_html_e( 'Sync report result', 'vms-elements-fastspring-woo-payment' ); ?></h2>
				<?php VMS_EFWP_Admin_Resource_Base::render_view_button( $sync_report['report'] ); ?>
				<pre class="vefwp-json"><?php echo esc_html( wp_json_encode( $sync_report['report'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ); ?></pre>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render jobs tab.
	 */
	private static function render_jobs_tab() {
		$jobs = vms_efwp()->api->list_data_jobs();
		if ( is_wp_error( $jobs ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $jobs );
			$jobs = array();
		}
		?>
		<p>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=vms-efwp-reports&tab=jobs' ) ); ?>"><?php esc_html_e( 'Refresh', 'vms-elements-fastspring-woo-payment' ); ?></a>
		</p>
		<?php self::render_jobs_table( $jobs, false ); ?>
		<?php
	}

	/**
	 * Render lookup tab.
	 */
	private static function render_lookup_tab() {
		$job_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'job_id' );
		VMS_EFWP_Admin_Resource_Base::render_lookup_form(
			'vms-efwp-reports',
			'job_id',
			__( 'Job ID', 'vms-elements-fastspring-woo-payment' ),
			'JOBABCDEFGHIJKLMNOPQRS1TUVWX',
			$job_id,
			array( 'tab' => 'lookup' )
		);

		if ( ! $job_id ) {
			return;
		}

		$job = vms_efwp()->api->get_data_job( $job_id );
		if ( is_wp_error( $job ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $job );
			return;
		}

		VMS_EFWP_Admin_Resource_Base::render_api_detail_card(
			$job,
			sprintf(
				/* translators: %s: job id */
				__( 'Job %s', 'vms-elements-fastspring-woo-payment' ),
				$job_id
			)
		);

		if ( vms_efwp()->api->is_data_job_ready( $job ) ) {
			$dl_url = self::download_url( $job_id );
			printf(
				'<p><a class="button button-primary" href="%s">%s</a></p>',
				esc_url( $dl_url ),
				esc_html__( 'Download report', 'vms-elements-fastspring-woo-payment' )
			);
		}
	}

	/**
	 * Render tools tab.
	 */
	private static function render_tools_tab() {
		?>
		<div class="vefwp-card">
			<h2><?php esc_html_e( 'Reset Data API cache', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Clears cached data used by FastSpring report endpoints.', 'vms-elements-fastspring-woo-payment' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_reset_data_cache' ); ?>
				<input type="hidden" name="wpfs_reset_data_cache" value="1" />
				<p><button class="button"><?php esc_html_e( 'Reset cache', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render jobs table.
	 *
	 * @param array $jobs         Job rows.
	 * @param bool  $recent_only  Whether rows come from local recent list.
	 */
	private static function render_jobs_table( $jobs, $recent_only = false ) {
		if ( empty( $jobs ) ) {
			echo '<p class="description">' . esc_html__( 'No report jobs found.', 'vms-elements-fastspring-woo-payment' ) . '</p>';
			return;
		}
		?>
		<table class="widefat striped vefwp-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Job ID', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Name', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Type', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Range', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Status', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Created', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'vms-elements-fastspring-woo-payment' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( array_reverse( $jobs ) as $job ) : ?>
				<?php
				$job_id = $job['job_id'] ?? $job['request_id'] ?? $job['id'] ?? '';
				if ( $recent_only && $job_id ) {
					$live = vms_efwp()->api->get_data_job( $job_id );
					if ( ! is_wp_error( $live ) ) {
						$job = array_merge( $job, $live );
					}
				}
				$ready   = vms_efwp()->api->is_data_job_ready( $job );
				$filter  = $job['params'] ?? $job['request']['filter'] ?? array();
				$range   = ( $filter['startDate'] ?? $filter['begin'] ?? '' ) . ' → ' . ( $filter['endDate'] ?? $filter['end'] ?? '' );
				$type    = $job['type'] ?? '';
				$name    = $job['name'] ?? '';
				$status  = $job['status'] ?? '';
				$created = $job['created'] ?? '';
				?>
				<tr>
					<td><code><?php echo esc_html( $job_id ); ?></code></td>
					<td><?php echo esc_html( $name ); ?></td>
					<td><?php echo esc_html( $type ); ?></td>
					<td><?php echo esc_html( trim( $range, ' →' ) ? $range : '—' ); ?></td>
					<td><span class="vefwp-status vefwp-status--<?php echo $ready ? 'ok' : 'pending'; ?>"><?php echo esc_html( $status ?: __( 'unknown', 'vms-elements-fastspring-woo-payment' ) ); ?></span></td>
					<td><?php echo esc_html( $created ); ?></td>
					<td class="vefwp-row-actions">
						<?php if ( $job_id ) : ?>
							<?php VMS_EFWP_Admin_Resource_Base::render_view_button( $job ); ?>
							<a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-reports', 'tab' => 'lookup', 'job_id' => $job_id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Lookup', 'vms-elements-fastspring-woo-payment' ); ?></a>
							<?php if ( $ready ) : ?>
								<a class="button button-small button-primary" href="<?php echo esc_url( self::download_url( $job_id ) ); ?>"><?php esc_html_e( 'Download', 'vms-elements-fastspring-woo-payment' ); ?></a>
							<?php endif; ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render downloaded report payload.
	 *
	 * @param string|WP_Error $download Download result.
	 */
	private static function render_download_card( $download ) {
		?>
		<div class="vefwp-card">
			<h2><?php esc_html_e( 'Report download', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<?php if ( is_wp_error( $download ) ) : ?>
				<?php VMS_EFWP_Admin_Resource_Base::render_result_notice( $download ); ?>
			<?php else : ?>
				<pre class="vefwp-json"><?php echo esc_html( (string) $download ); ?></pre>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Build a nonce-protected download URL.
	 *
	 * @param string $job_id Job id.
	 * @return string
	 */
	private static function download_url( $job_id ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'page'     => 'vms-efwp-reports',
					'download' => $job_id,
				),
				admin_url( 'admin.php' )
			),
			'wpfs_download_report'
		);
	}

	/**
	 * Get remembered recent jobs.
	 *
	 * @return array
	 */
	private static function get_recent_jobs() {
		$recent = get_transient( self::TRANSIENT_KEY );
		return is_array( $recent ) ? $recent : array();
	}

	/**
	 * Remember a generated report job id (transient).
	 *
	 * @param string $job_id Job id.
	 * @param string $type   Report type.
	 * @param array  $filter Filter payload.
	 * @param string $status Job status.
	 * @param string $name   Job name.
	 */
	private static function remember_job( $job_id, $type, $filter, $status = '', $name = '' ) {
		$existing = self::get_recent_jobs();
		$existing[] = array(
			'job_id'  => $job_id,
			'type'    => $type,
			'params'  => $filter,
			'status'  => $status,
			'name'    => $name,
			'created' => gmdate( 'Y-m-d H:i:s' ) . ' UTC',
		);
		set_transient( self::TRANSIENT_KEY, array_slice( $existing, -20 ), DAY_IN_SECONDS );
	}
}
