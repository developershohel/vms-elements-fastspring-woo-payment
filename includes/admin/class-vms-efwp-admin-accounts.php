<?php
/**
 * Accounts (customers) screen.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Accounts.
 */
class VMS_EFWP_Admin_Accounts {

	/**
	 * Render the screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap vefwp-wrap">';
		VMS_EFWP_Admin_Resource_Base::render_header(
			__( 'Accounts', 'vms-elements-fastspring-woo-payment' ),
			__( 'Search FastSpring customer accounts.', 'vms-elements-fastspring-woo-payment' ),
			array( '<button type="button" class="button button-primary" data-vefwp-open-form="create-account">' . esc_html__( 'New account', 'vms-elements-fastspring-woo-payment' ) . '</button>' )
		);

		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		$api = vms_efwp()->api;

		if ( VMS_EFWP_Admin_Resource_Base::is_post_submit( 'vms_efwp_account_portal' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'vms_efwp_account_portal' ) ) {
			$account_id = VMS_EFWP_Admin_Resource_Base::post_text( 'account_id' );
			$portal_tab = VMS_EFWP_Admin_Resource_Base::post_text( 'portal_tab', 'orders' );
			$auth       = $api->get_account_management_url( $account_id );
			$url        = $api->parse_account_management_url( $auth );
			if ( is_wp_error( $url ) ) {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $url );
			} else {
				if ( 'subscriptions' === $portal_tab ) {
					$url .= '#/subscriptions';
				}
				wp_safe_redirect( $url );
				exit;
			}
		}

		if ( VMS_EFWP_Admin_Resource_Base::is_post_submit( 'vms_efwp_update_account' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'vms_efwp_update_account' ) ) {
			$account_id = VMS_EFWP_Admin_Resource_Base::post_text( 'account_id' );
			$contact    = array_filter(
				array(
					'email'   => VMS_EFWP_Admin_Resource_Base::post_email( 'email' ),
					'first'   => VMS_EFWP_Admin_Resource_Base::post_text( 'first' ),
					'last'    => VMS_EFWP_Admin_Resource_Base::post_text( 'last' ),
					'company' => VMS_EFWP_Admin_Resource_Base::post_text( 'company' ),
					'phone'   => VMS_EFWP_Admin_Resource_Base::post_text( 'phone' ),
				),
				static function ( $value ) {
					return is_string( $value ) && '' !== $value;
				}
			);
			$payload = array( 'contact' => $contact );
			$country   = VMS_EFWP_Admin_Resource_Base::post_text( 'country' );
			$language  = VMS_EFWP_Admin_Resource_Base::post_text( 'language' );
			$subscribed = VMS_EFWP_Admin_Resource_Base::post_text( 'subscribed' );
			if ( $country ) {
				$payload['country'] = strtoupper( $country );
			}
			if ( $language ) {
				$payload['language'] = $language;
			}
			if ( in_array( $subscribed, array( 'true', 'false' ), true ) ) {
				$payload['subscribed'] = 'true' === $subscribed;
			}
			$update_result = $api->update_account( $account_id, $payload );
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $update_result );
		}

		if ( VMS_EFWP_Admin_Resource_Base::is_post_submit( 'vms_efwp_create_account' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'vms_efwp_create_account' ) ) {
			$contact = array_filter(
				array(
					'email'   => VMS_EFWP_Admin_Resource_Base::post_email( 'email' ),
					'first'   => VMS_EFWP_Admin_Resource_Base::post_text( 'first' ),
					'last'    => VMS_EFWP_Admin_Resource_Base::post_text( 'last' ),
					'company' => VMS_EFWP_Admin_Resource_Base::post_text( 'company' ),
					'phone'   => VMS_EFWP_Admin_Resource_Base::post_text( 'phone' ),
				),
				static function ( $value ) {
					return is_string( $value ) && '' !== $value;
				}
			);

			$payload = array( 'contact' => $contact );

			$country = VMS_EFWP_Admin_Resource_Base::post_text( 'country' );
			if ( $country ) {
				$payload['country'] = strtoupper( $country );
			}

			$language = VMS_EFWP_Admin_Resource_Base::post_text( 'language', 'en' );
			if ( $language ) {
				$payload['language'] = $language;
			}

			$custom_key = VMS_EFWP_Admin_Resource_Base::post_text( 'lookup_custom' );
			if ( strlen( $custom_key ) >= 4 ) {
				$payload['lookup'] = array( 'custom' => $custom_key );
			}

			if ( empty( $contact['email'] ) || empty( $contact['first'] ) || empty( $contact['last'] ) ) {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					esc_html__( 'Email, first name, and last name are required to create a FastSpring account.', 'vms-elements-fastspring-woo-payment' )
				);
			} else {
				$create_result = $api->create_account( $payload );
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $create_result );
			}
		}

		$search    = VMS_EFWP_Admin_Resource_Base::get_filter_text( 's' );
		$edit_id   = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'edit' );
		$page      = max( 1, VMS_EFWP_Admin_Resource_Base::get_filter_int( 'paged', 1 ) );
		$params = array(
			'limit' => 50,
			'page'  => $page,
		);
		if ( $search ) {
			$params['email'] = $search;
		}

		$result   = $api->get_accounts( $params );
		$accounts = array();
		$total    = 0;
		$has_next = false;

		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$total    = isset( $result['total'] ) ? (int) $result['total'] : 0;
			$has_next = ! empty( $result['nextPage'] );
			$accounts = $api->hydrate_accounts( $api->extract_account_ids( $result ) );
		}
		?>

		<form method="get" class="vefwp-filters">
			<input type="hidden" name="page" value="vms-efwp-accounts" />
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by email...', 'vms-elements-fastspring-woo-payment' ); ?>" />
			<button class="button"><?php esc_html_e( 'Search', 'vms-elements-fastspring-woo-payment' ); ?></button>
		</form>

		<?php if ( $edit_id ) : ?>
			<?php
			$edit_account = $api->get_account( $edit_id );
			if ( is_wp_error( $edit_account ) ) {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $edit_account );
			} else {
				$edit_contact = isset( $edit_account['contact'] ) && is_array( $edit_account['contact'] ) ? $edit_account['contact'] : array();
				?>
				<div class="vefwp-card vefwp-card--wide">
					<h2><?php esc_html_e( 'Edit account', 'vms-elements-fastspring-woo-payment' ); ?></h2>
					<form method="post">
						<?php wp_nonce_field( 'vms_efwp_update_account' ); ?>
						<input type="hidden" name="vms_efwp_update_account" value="1" />
						<input type="hidden" name="account_id" value="<?php echo esc_attr( $edit_id ); ?>" />
						<div class="vefwp-grid vefwp-grid--two">
							<p><label><?php esc_html_e( 'Email', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="email" name="email" class="regular-text" value="<?php echo esc_attr( $edit_contact['email'] ?? '' ); ?>" /></label></p>
							<p><label><?php esc_html_e( 'Phone', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="phone" class="regular-text" value="<?php echo esc_attr( $edit_contact['phone'] ?? '' ); ?>" /></label></p>
							<p><label><?php esc_html_e( 'First name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="first" class="regular-text" value="<?php echo esc_attr( $edit_contact['first'] ?? '' ); ?>" /></label></p>
							<p><label><?php esc_html_e( 'Last name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="last" class="regular-text" value="<?php echo esc_attr( $edit_contact['last'] ?? '' ); ?>" /></label></p>
							<p><label><?php esc_html_e( 'Company', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="company" class="regular-text" value="<?php echo esc_attr( $edit_contact['company'] ?? '' ); ?>" /></label></p>
							<p><label><?php esc_html_e( 'Country', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="country" maxlength="2" class="regular-text" value="<?php echo esc_attr( $edit_account['country'] ?? '' ); ?>" /></label></p>
							<p><label><?php esc_html_e( 'Language', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="language" maxlength="5" class="regular-text" value="<?php echo esc_attr( $edit_account['language'] ?? 'en' ); ?>" /></label></p>
							<p><label><?php esc_html_e( 'Marketing subscribed', 'vms-elements-fastspring-woo-payment' ); ?><br />
								<select name="subscribed">
									<option value=""><?php esc_html_e( 'No change', 'vms-elements-fastspring-woo-payment' ); ?></option>
									<option value="true" <?php selected( ! empty( $edit_account['subscribed'] ) ); ?>><?php esc_html_e( 'Subscribed', 'vms-elements-fastspring-woo-payment' ); ?></option>
									<option value="false" <?php selected( empty( $edit_account['subscribed'] ) ); ?>><?php esc_html_e( 'Unsubscribed', 'vms-elements-fastspring-woo-payment' ); ?></option>
								</select>
							</label></p>
						</div>
						<p>
							<button class="button button-primary"><?php esc_html_e( 'Update account', 'vms-elements-fastspring-woo-payment' ); ?></button>
							<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=vms-efwp-accounts' ) ); ?>"><?php esc_html_e( 'Cancel', 'vms-elements-fastspring-woo-payment' ); ?></a>
						</p>
					</form>
				</div>
				<?php
			}
			?>
		<?php endif; ?>

		<div class="vefwp-card" data-vefwp-form="create-account" hidden>
			<h2><?php esc_html_e( 'Create new account', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<p class="description"><?php esc_html_e( 'FastSpring recommends including the customer country; some payment methods require it.', 'vms-elements-fastspring-woo-payment' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'vms_efwp_create_account' ); ?>
				<input type="hidden" name="vms_efwp_create_account" value="1" />
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Email', 'vms-elements-fastspring-woo-payment' ); ?> <span class="required">*</span><br /><input type="email" required name="email" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Phone', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="phone" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'First name', 'vms-elements-fastspring-woo-payment' ); ?> <span class="required">*</span><br /><input type="text" required name="first" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Last name', 'vms-elements-fastspring-woo-payment' ); ?> <span class="required">*</span><br /><input type="text" required name="last" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Company', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="company" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Country (ISO 3166-1 alpha-2)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="country" maxlength="2" class="regular-text" placeholder="US" /></label></p>
					<p><label><?php esc_html_e( 'Language (ISO 639)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="language" maxlength="5" class="regular-text" value="en" /></label></p>
					<p><label><?php esc_html_e( 'Custom lookup key (4+ chars)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="lookup_custom" minlength="4" class="regular-text" /></label></p>
				</div>
				<p><button class="button button-primary"><?php esc_html_e( 'Create account', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
			</form>
		</div>

		<table class="widefat striped vefwp-table">
			<thead><tr>
				<th><?php esc_html_e( 'Account ID', 'vms-elements-fastspring-woo-payment' ); ?></th>
				<th><?php esc_html_e( 'Email', 'vms-elements-fastspring-woo-payment' ); ?></th>
				<th><?php esc_html_e( 'Name', 'vms-elements-fastspring-woo-payment' ); ?></th>
				<th><?php esc_html_e( 'Country', 'vms-elements-fastspring-woo-payment' ); ?></th>
				<th><?php esc_html_e( 'Subscribed', 'vms-elements-fastspring-woo-payment' ); ?></th>
				<th></th>
			</tr></thead>
			<tbody>
			<?php if ( empty( $accounts ) ) : ?>
				<?php VMS_EFWP_Admin_Resource_Base::render_empty_row( __( 'No accounts found.', 'vms-elements-fastspring-woo-payment' ), 6 ); ?>
			<?php else : ?>
				<?php foreach ( $accounts as $a ) : ?>
					<?php
					$account_id = $a['id'] ?? $a['account'] ?? '';
					$contact    = isset( $a['contact'] ) && is_array( $a['contact'] ) ? $a['contact'] : array();
					?>
					<tr>
						<td><code><?php echo esc_html( $account_id ); ?></code></td>
						<td><?php echo esc_html( $contact['email'] ?? '' ); ?></td>
						<td><?php echo esc_html( trim( ( $contact['first'] ?? '' ) . ' ' . ( $contact['last'] ?? '' ) ) ); ?></td>
						<td><?php echo esc_html( $a['country'] ?? '' ); ?></td>
						<td>
							<?php if ( ! empty( $a['subscribed'] ) ) : ?>
								<span class="vefwp-status vefwp-status--active"><?php esc_html_e( 'Yes', 'vms-elements-fastspring-woo-payment' ); ?></span>
							<?php else : ?>
								<span class="vefwp-status"><?php esc_html_e( 'No', 'vms-elements-fastspring-woo-payment' ); ?></span>
							<?php endif; ?>
						</td>
						<td class="vefwp-row-actions">
							<?php VMS_EFWP_Admin_Resource_Base::render_view_button( $a ); ?>
							<?php if ( $account_id ) : ?>
								<a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-accounts', 'edit' => $account_id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Edit', 'vms-elements-fastspring-woo-payment' ); ?></a>
								<form method="post" style="display:inline;">
									<?php wp_nonce_field( 'vms_efwp_account_portal' ); ?>
									<input type="hidden" name="vms_efwp_account_portal" value="1" />
									<input type="hidden" name="account_id" value="<?php echo esc_attr( $account_id ); ?>" />
									<input type="hidden" name="portal_tab" value="orders" />
									<button type="submit" class="button button-small"><?php esc_html_e( 'Portal', 'vms-elements-fastspring-woo-payment' ); ?></button>
								</form>
								<form method="post" style="display:inline;">
									<?php wp_nonce_field( 'vms_efwp_account_portal' ); ?>
									<input type="hidden" name="vms_efwp_account_portal" value="1" />
									<input type="hidden" name="account_id" value="<?php echo esc_attr( $account_id ); ?>" />
									<input type="hidden" name="portal_tab" value="subscriptions" />
									<button type="submit" class="button button-small"><?php esc_html_e( 'Subscriptions', 'vms-elements-fastspring-woo-payment' ); ?></button>
								</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>

		<?php if ( $page > 1 || $has_next ) : ?>
			<div class="tablenav bottom">
				<div class="tablenav-pages">
					<?php if ( $page > 1 ) : ?>
						<a class="button" href="<?php echo esc_url( add_query_arg( array( 'paged' => $page - 1, 's' => $search ), admin_url( 'admin.php?page=vms-efwp-accounts' ) ) ); ?>">&larr; <?php esc_html_e( 'Previous', 'vms-elements-fastspring-woo-payment' ); ?></a>
					<?php endif; ?>
					<span class="displaying-num">
						<?php
						if ( $total ) {
							printf(
								/* translators: 1: current page number, 2: total account count */
								esc_html__( 'Page %1$d (%2$s accounts)', 'vms-elements-fastspring-woo-payment' ),
								(int) $page,
								esc_html( number_format_i18n( $total ) )
							);
						} else {
							printf(
								/* translators: %d: current page number */
								esc_html__( 'Page %d', 'vms-elements-fastspring-woo-payment' ),
								(int) $page
							);
						}
						?>
					</span>
					<?php if ( $has_next ) : ?>
						<a class="button" href="<?php echo esc_url( add_query_arg( array( 'paged' => $page + 1, 's' => $search ), admin_url( 'admin.php?page=vms-efwp-accounts' ) ) ); ?>"><?php esc_html_e( 'Next', 'vms-elements-fastspring-woo-payment' ); ?> &rarr;</a>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php
		VMS_EFWP_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}
}
