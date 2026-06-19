<?php
/**
 * Coupons screen.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Coupons.
 */
class VMS_EFWP_Admin_Coupons {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="wrap vefwp-wrap">';
		VMS_EFWP_Admin_Resource_Base::render_header(
			__( 'Coupons', 'vms-elements-fastspring-woo-payment' ),
			__( 'Discount codes and promotional offers.', 'vms-elements-fastspring-woo-payment' ),
			array( '<button type="button" class="button button-primary" data-vefwp-open-form="create-coupon">' . esc_html__( 'New coupon', 'vms-elements-fastspring-woo-payment' ) . '</button>' )
		);

		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		$api = vms_efwp()->api;

		self::handle_get_actions( $api );
		self::handle_post_actions( $api );

		$view_codes = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'codes' );
		$codes_view = null;
		if ( $view_codes ) {
			$codes_view = $api->get_coupon_codes( $view_codes );
		}

		$result  = $api->get_coupons();
		$coupons = array();
		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
		} else {
			$coupons = $api->hydrate_coupons( $api->extract_coupon_paths( $result ) );
		}

		self::render_create_form();
		self::render_add_codes_form();
		self::render_codes_panel( $view_codes, $codes_view );
		self::render_table( $coupons );

		VMS_EFWP_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}

	/**
	 * Handle GET actions (delete coupon / delete all codes).
	 *
	 * @param VMS_EFWP_API $api API client.
	 */
	private static function handle_get_actions( $api ) {
		$delete_id = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'delete' );
		if ( $delete_id ) {
			check_admin_referer( 'vms_efwp_delete_coupon' );
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $api->delete_coupon( $delete_id ) );
		}

		$delete_codes = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'delete_codes' );
		if ( $delete_codes ) {
			check_admin_referer( 'vms_efwp_delete_coupon_codes' );
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $api->delete_coupon_codes( $delete_codes ) );
		}
	}

	/**
	 * Handle POST actions (create / add codes).
	 *
	 * @param VMS_EFWP_API $api API client.
	 */
	private static function handle_post_actions( $api ) {
		if ( VMS_EFWP_Admin_Resource_Base::is_post_submit( 'vms_efwp_create_coupon' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'vms_efwp_create_coupon' ) ) {
			$payload = self::build_coupon_payload_from_post();
			if ( is_wp_error( $payload ) ) {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $payload );
			} else {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $api->create_coupon( $payload ) );
			}
		}

		if ( VMS_EFWP_Admin_Resource_Base::is_post_submit( 'vms_efwp_add_coupon_codes' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'vms_efwp_add_coupon_codes' ) ) {
			$coupon_id = self::sanitize_coupon_path( VMS_EFWP_Admin_Resource_Base::post_text( 'coupon_id' ) );
			$codes     = self::parse_codes_input( VMS_EFWP_Admin_Resource_Base::post_text( 'codes' ) );
			if ( ! $coupon_id || empty( $codes ) ) {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					esc_html__( 'Coupon path and at least one code are required.', 'vms-elements-fastspring-woo-payment' )
				);
			} else {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $api->add_coupon_codes( $coupon_id, $codes ) );
			}
		}
	}

	/**
	 * Build a FastSpring coupon upsert payload from the create form.
	 *
	 * @return array|WP_Error
	 */
	private static function build_coupon_payload_from_post() {
		$coupon_path   = self::sanitize_coupon_path( VMS_EFWP_Admin_Resource_Base::post_text( 'coupon' ) );
		$codes         = self::parse_codes_input( VMS_EFWP_Admin_Resource_Base::post_text( 'code' ) );
		$name          = VMS_EFWP_Admin_Resource_Base::post_text( 'name' );
		$discount_type = VMS_EFWP_Admin_Resource_Base::post_text( 'discount_type', 'percent' );
		$discount_val  = VMS_EFWP_Admin_Resource_Base::post_float( 'discount_value' );
		$currency      = strtoupper( VMS_EFWP_Admin_Resource_Base::post_text( 'currency', 'USD' ) );

		if ( ! $coupon_path || ! preg_match( '/^[a-zA-Z0-9_-]+$/', $coupon_path ) ) {
			return new WP_Error( 'vms_efwp_invalid_coupon_path', __( 'Coupon path must use only letters, numbers, hyphens, and underscores.', 'vms-elements-fastspring-woo-payment' ) );
		}
		if ( empty( $codes ) || ! $name ) {
			return new WP_Error( 'vms_efwp_missing_coupon_fields', __( 'Checkout code and display name are required.', 'vms-elements-fastspring-woo-payment' ) );
		}
		if ( $discount_val <= 0 ) {
			return new WP_Error( 'vms_efwp_invalid_discount', __( 'Discount value must be greater than zero.', 'vms-elements-fastspring-woo-payment' ) );
		}

		$payload = array(
			'coupon' => $coupon_path,
			'reason' => array( 'en' => $name ),
			'codes'  => $codes,
		);

		if ( 'flat' === $discount_type ) {
			$payload['discount'] = array(
				'type'   => 'flat',
				'amount' => array( $currency => $discount_val ),
			);
		} else {
			$payload['discount'] = array(
				'type'    => 'percent',
				'percent' => $discount_val,
			);
		}

		$products = array_filter( array_map( 'trim', explode( ',', VMS_EFWP_Admin_Resource_Base::post_text( 'products' ) ) ) );
		if ( ! empty( $products ) ) {
			$payload['products'] = array_values( $products );
		}

		$period = VMS_EFWP_Admin_Resource_Base::post_int( 'discount_period_count', 0 );
		if ( $period > 0 ) {
			$payload['discountPeriodCount'] = $period;
		}

		if ( VMS_EFWP_Admin_Resource_Base::post_text( 'apply_discount_immediately' ) ) {
			$payload['applyDiscountImmediately'] = true;
		}

		$limit = VMS_EFWP_Admin_Resource_Base::post_int( 'limit', 0 );
		if ( $limit > 0 ) {
			$payload['limit'] = $limit;
		}

		$start = VMS_EFWP_Admin_Resource_Base::post_text( 'starts' );
		$end   = VMS_EFWP_Admin_Resource_Base::post_text( 'expires' );
		if ( $start || $end ) {
			$payload['available'] = array();
			if ( $start ) {
				$payload['available']['start'] = $start;
			}
			if ( $end ) {
				$payload['available']['end'] = $end;
			}
		}

		if ( VMS_EFWP_Admin_Resource_Base::post_text( 'order_level_discount' ) ) {
			$payload['orderLevelDiscount'] = true;
			$payload['discount']           = array(
				'type'   => 'flat',
				'amount' => array( $currency => $discount_val ),
			);
		}

		return $payload;
	}

	/**
	 * Render create / upsert form.
	 */
	private static function render_create_form() {
		?>
		<div class="vefwp-card" data-vefwp-form="create-coupon" hidden>
			<h2><?php esc_html_e( 'Create coupon', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Creates a new coupon or updates an existing one when the path already exists. Re-using a path updates that coupon and replaces codes if you include the codes field.', 'vms-elements-fastspring-woo-payment' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'vms_efwp_create_coupon' ); ?>
				<input type="hidden" name="vms_efwp_create_coupon" value="1" />
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Coupon path', 'vms-elements-fastspring-woo-payment' ); ?> <span class="required">*</span><br /><input type="text" required name="coupon" class="regular-text" placeholder="summer-sale-2026" pattern="[a-zA-Z0-9_-]+" /></label></p>
					<p><label><?php esc_html_e( 'Checkout code(s)', 'vms-elements-fastspring-woo-payment' ); ?> <span class="required">*</span><br /><input type="text" required name="code" class="regular-text" placeholder="SUMMER10, SUMMER20" /></label></p>
					<p><label><?php esc_html_e( 'Display name', 'vms-elements-fastspring-woo-payment' ); ?> <span class="required">*</span><br /><input type="text" required name="name" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Discount type', 'vms-elements-fastspring-woo-payment' ); ?><br />
						<select name="discount_type" id="vefwp-coupon-discount-type">
							<option value="percent"><?php esc_html_e( 'Percent (%)', 'vms-elements-fastspring-woo-payment' ); ?></option>
							<option value="flat"><?php esc_html_e( 'Fixed amount (flat)', 'vms-elements-fastspring-woo-payment' ); ?></option>
						</select>
					</label></p>
					<p><label><?php esc_html_e( 'Discount value', 'vms-elements-fastspring-woo-payment' ); ?> <span class="required">*</span><br /><input type="number" step="0.01" min="0.01" name="discount_value" class="regular-text" required /></label></p>
					<p data-vefwp-flat-only><label><?php esc_html_e( 'Currency (flat discounts)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="currency" maxlength="3" class="regular-text" value="USD" /></label></p>
					<p><label><?php esc_html_e( 'Product paths (comma separated, optional)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="products" class="regular-text" placeholder="pro-plan-monthly, pro-plan-annual" /></label></p>
					<p><label><?php esc_html_e( 'Discount periods (subscriptions)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" min="0" max="365" name="discount_period_count" class="small-text" placeholder="3" /></label></p>
					<p><label><?php esc_html_e( 'Usage limit (0 = unlimited)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" min="0" name="limit" class="small-text" value="0" /></label></p>
					<p><label><?php esc_html_e( 'Start date (YYYY-MM-DD)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="date" name="starts" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'End date (YYYY-MM-DD)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="date" name="expires" class="regular-text" /></label></p>
					<p><label><input type="checkbox" name="apply_discount_immediately" value="1" /> <?php esc_html_e( 'Apply discount immediately (subscriptions)', 'vms-elements-fastspring-woo-payment' ); ?></label></p>
					<p><label><input type="checkbox" name="order_level_discount" value="1" id="vefwp-order-level-discount" /> <?php esc_html_e( 'Order-level flat discount (FastSpring beta)', 'vms-elements-fastspring-woo-payment' ); ?></label></p>
				</div>
				<p><button class="button button-primary"><?php esc_html_e( 'Save coupon', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render add-codes form.
	 */
	private static function render_add_codes_form() {
		$prefill = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'add_codes' );
		?>
		<div class="vefwp-card" data-vefwp-form="add-coupon-codes" <?php echo $prefill ? '' : 'hidden'; ?>>
			<h2><?php esc_html_e( 'Add coupon codes', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Adds new checkout codes to an existing coupon. Existing codes are preserved.', 'vms-elements-fastspring-woo-payment' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'vms_efwp_add_coupon_codes' ); ?>
				<input type="hidden" name="vms_efwp_add_coupon_codes" value="1" />
				<p><label><?php esc_html_e( 'Coupon path', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" required name="coupon_id" class="regular-text" value="<?php echo esc_attr( $prefill ); ?>" <?php echo $prefill ? 'readonly' : ''; ?> /></label></p>
				<p><label><?php esc_html_e( 'New codes (comma or line separated)', 'vms-elements-fastspring-woo-payment' ); ?><br /><textarea name="codes" rows="3" class="large-text" required placeholder="SUMMER30, SUMMER40"></textarea></label></p>
				<p><button class="button button-primary"><?php esc_html_e( 'Add codes', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render stored codes for a coupon.
	 *
	 * @param string            $coupon_id Coupon path.
	 * @param array|WP_Error|null $codes_view API response.
	 */
	private static function render_codes_panel( $coupon_id, $codes_view ) {
		if ( ! $coupon_id ) {
			return;
		}
		?>
		<div class="vefwp-card">
			<h2><?php echo esc_html( sprintf( __( 'Codes for %s', 'vms-elements-fastspring-woo-payment' ), $coupon_id ) ); ?></h2>
			<?php if ( is_wp_error( $codes_view ) ) : ?>
				<?php VMS_EFWP_Admin_Resource_Base::render_result_notice( $codes_view ); ?>
			<?php elseif ( empty( $codes_view['codes'] ) ) : ?>
				<p><em><?php esc_html_e( 'No codes stored for this coupon.', 'vms-elements-fastspring-woo-payment' ); ?></em></p>
			<?php else : ?>
				<p><code><?php echo esc_html( implode( ', ', (array) $codes_view['codes'] ) ); ?></code></p>
			<?php endif; ?>
			<p>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=vms-efwp-coupons' ) ); ?>"><?php esc_html_e( 'Back to coupons', 'vms-elements-fastspring-woo-payment' ); ?></a>
				<a class="button button-primary" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-coupons', 'add_codes' => $coupon_id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Add more codes', 'vms-elements-fastspring-woo-payment' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Render coupons table.
	 *
	 * @param array $coupons Coupon rows.
	 */
	private static function render_table( $coupons ) {
		?>
		<table class="widefat striped vefwp-table">
			<thead><tr>
				<th><?php esc_html_e( 'Coupon', 'vms-elements-fastspring-woo-payment' ); ?></th>
				<th><?php esc_html_e( 'Codes', 'vms-elements-fastspring-woo-payment' ); ?></th>
				<th><?php esc_html_e( 'Discount', 'vms-elements-fastspring-woo-payment' ); ?></th>
				<th><?php esc_html_e( 'Products', 'vms-elements-fastspring-woo-payment' ); ?></th>
				<th><?php esc_html_e( 'Expires', 'vms-elements-fastspring-woo-payment' ); ?></th>
				<th></th>
			</tr></thead>
			<tbody>
			<?php if ( empty( $coupons ) ) : ?>
				<?php VMS_EFWP_Admin_Resource_Base::render_empty_row( __( 'No coupons configured.', 'vms-elements-fastspring-woo-payment' ), 6 ); ?>
			<?php else : ?>
				<?php foreach ( $coupons as $c ) : ?>
					<?php
					$cid         = $c['coupon'] ?? ( $c['id'] ?? '' );
					$discount    = isset( $c['discount'] ) ? $c['discount'] : array();
					$reason      = isset( $c['reason']['en'] ) ? $c['reason']['en'] : ( is_array( $c['reason'] ?? null ) ? (string) reset( $c['reason'] ) : '' );
					$codes       = is_array( $c['codes'] ?? null ) ? $c['codes'] : array();
					$codes_label = $codes ? implode( ', ', array_slice( $codes, 0, 5 ) ) . ( count( $codes ) > 5 ? '…' : '' ) : '';
					$products    = is_array( $c['products'] ?? null ) ? implode( ', ', $c['products'] ) : '';
					$delete_url  = wp_nonce_url(
						add_query_arg( array( 'page' => 'vms-efwp-coupons', 'delete' => $cid ), admin_url( 'admin.php' ) ),
						'vms_efwp_delete_coupon'
					);
					$codes_url   = add_query_arg( array( 'page' => 'vms-efwp-coupons', 'codes' => $cid ), admin_url( 'admin.php' ) );
					$add_url     = add_query_arg( array( 'page' => 'vms-efwp-coupons', 'add_codes' => $cid ), admin_url( 'admin.php' ) );
					$clear_url   = wp_nonce_url(
						add_query_arg( array( 'page' => 'vms-efwp-coupons', 'delete_codes' => $cid ), admin_url( 'admin.php' ) ),
						'vms_efwp_delete_coupon_codes'
					);
					?>
					<tr>
						<td><strong><?php echo esc_html( $cid ); ?></strong><br /><span class="description"><?php echo esc_html( $reason ); ?></span></td>
						<td><?php echo esc_html( $codes_label ); ?></td>
						<td><?php echo esc_html( self::format_discount( $discount ) ); ?></td>
						<td><?php echo esc_html( $products ? $products : '—' ); ?></td>
						<td><?php echo esc_html( $c['available']['end'] ?? '—' ); ?></td>
						<td class="vefwp-row-actions">
							<?php VMS_EFWP_Admin_Resource_Base::render_view_button( $c ); ?>
							<a class="button button-small" href="<?php echo esc_url( $codes_url ); ?>"><?php esc_html_e( 'Codes', 'vms-elements-fastspring-woo-payment' ); ?></a>
							<a class="button button-small" href="<?php echo esc_url( $add_url ); ?>"><?php esc_html_e( 'Add codes', 'vms-elements-fastspring-woo-payment' ); ?></a>
							<a class="button button-small" href="<?php echo esc_url( $clear_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Delete all codes for this coupon?', 'vms-elements-fastspring-woo-payment' ); ?>');"><?php esc_html_e( 'Clear codes', 'vms-elements-fastspring-woo-payment' ); ?></a>
							<a class="button button-small" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Delete this coupon?', 'vms-elements-fastspring-woo-payment' ); ?>');"><?php esc_html_e( 'Delete', 'vms-elements-fastspring-woo-payment' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Sanitize a coupon path per FastSpring pattern.
	 *
	 * @param string $path Raw path.
	 * @return string
	 */
	private static function sanitize_coupon_path( $path ) {
		return preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $path );
	}

	/**
	 * Parse one or more coupon codes from user input.
	 *
	 * @param string $raw Raw input.
	 * @return string[]
	 */
	private static function parse_codes_input( $raw ) {
		$parts = preg_split( '/[\s,]+/', (string) $raw, -1, PREG_SPLIT_NO_EMPTY );
		$codes = array();
		foreach ( (array) $parts as $part ) {
			$code = preg_replace( '/[^a-zA-Z0-9_-]/', '', $part );
			if ( '' !== $code ) {
				$codes[] = $code;
			}
		}
		return array_values( array_unique( $codes ) );
	}

	/**
	 * Format a coupon discount for display.
	 *
	 * @param array $discount Discount payload.
	 * @return string
	 */
	private static function format_discount( $discount ) {
		if ( ! is_array( $discount ) ) {
			return '';
		}

		if ( ! empty( $discount['hasMultipleDiscounts'] ) && ! empty( $discount['discounts'] ) ) {
			return __( 'Multi-tier', 'vms-elements-fastspring-woo-payment' );
		}

		if ( 'percent' === ( $discount['type'] ?? '' ) ) {
			return ( $discount['percent'] ?? 0 ) . '%';
		}

		if ( 'flat' === ( $discount['type'] ?? '' ) && ! empty( $discount['amount'] ) && is_array( $discount['amount'] ) ) {
			$parts = array();
			foreach ( $discount['amount'] as $cur => $val ) {
				$parts[] = $cur . ' ' . number_format_i18n( (float) $val, 2 );
			}
			return implode( ', ', $parts );
		}

		return '';
	}
}
