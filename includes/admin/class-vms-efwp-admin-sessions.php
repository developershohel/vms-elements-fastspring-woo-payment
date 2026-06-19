<?php
/**
 * Sessions screen (V2 primary, V1 legacy).
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Sessions.
 */
class VMS_EFWP_Admin_Sessions {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab  = VMS_EFWP_Admin_Resource_Base::get_filter_key( 'tab', 'create' );
		$tabs = array(
			'create' => __( 'Create Session', 'vms-elements-fastspring-woo-payment' ),
			'lookup' => __( 'Lookup & Manage', 'vms-elements-fastspring-woo-payment' ),
			'legacy' => __( 'Legacy v1', 'vms-elements-fastspring-woo-payment' ),
		);
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'create';
		}
		$base = admin_url( 'admin.php?page=vms-efwp-sessions' );

		echo '<div class="wrap vefwp-wrap">';
		VMS_EFWP_Admin_Resource_Base::render_header(
			__( 'Sessions', 'vms-elements-fastspring-woo-payment' ),
			__( 'Create and manage FastSpring checkout sessions.', 'vms-elements-fastspring-woo-payment' )
		);

		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		if ( 'create' === $tab && VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_create_checkout_session' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_create_checkout_session' ) ) {
			self::handle_create_v2();
		}

		if ( 'lookup' === $tab ) {
			self::handle_lookup_actions();
		}

		if ( 'legacy' === $tab && VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_create_session_v1' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_create_session_v1' ) ) {
			self::handle_create_v1();
		}

		VMS_EFWP_Admin_Resource_Base::render_nav_tabs( $tabs, $tab, $base );

		if ( 'lookup' === $tab ) {
			self::render_lookup_tab();
		} elseif ( 'legacy' === $tab ) {
			self::render_legacy_tab();
		} else {
			self::render_create_tab();
		}

		VMS_EFWP_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}

	/**
	 * Default checkout path from settings.
	 *
	 * @return string
	 */
	private static function default_checkout_path() {
		return vms_efwp()->settings->checkout_path();
	}

	/**
	 * Read checkout path from GET/POST with fallback.
	 *
	 * @return string
	 */
	private static function get_checkout_path_input() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Admin POST paired with verify_post(); sanitized below.
		if ( ! empty( $_POST['checkout_path'] ) ) {
			return sanitize_text_field( wp_unslash( $_POST['checkout_path'] ) );
		}

		$path = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'checkout_path' );
		return $path ? $path : self::default_checkout_path();
	}

	/**
	 * Read session ID from GET/POST.
	 *
	 * @return string
	 */
	private static function get_session_id_input() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Admin POST paired with verify_post(); sanitized below.
		if ( ! empty( $_POST['session_id'] ) ) {
			return sanitize_text_field( wp_unslash( $_POST['session_id'] ) );
		}

		return VMS_EFWP_Admin_Resource_Base::get_filter_text( 'session_id' );
	}

	/**
	 * Build V2 line items from POST.
	 *
	 * @return array
	 */
	private static function build_v2_line_items_from_post() {
		$items        = array();
		$product_path = VMS_EFWP_Admin_Resource_Base::post_text( 'product_path' );
		$quantity     = max( 1, VMS_EFWP_Admin_Resource_Base::post_int( 'quantity', 1 ) );
		$unit_price   = VMS_EFWP_Admin_Resource_Base::post_float( 'unit_price' );
		$currency     = strtoupper( VMS_EFWP_Admin_Resource_Base::post_text( 'currency', 'USD' ) );

		if ( $product_path ) {
			$item = array(
				'productPath' => $product_path,
				'quantity'    => $quantity,
			);
			if ( $unit_price > 0 ) {
				$item['customPrice'] = array(
					'unitPrice' => array(
						$currency => $unit_price,
					),
				);
			}
			$items[] = $item;
		}

		$extra = VMS_EFWP_Admin_Resource_Base::post_text( 'additional_products' );
		if ( $extra ) {
			foreach ( array_filter( array_map( 'trim', explode( ',', $extra ) ) ) as $pair ) {
				$parts = array_map( 'trim', explode( ':', $pair ) );
				$path  = $parts[0] ?? '';
				$qty   = isset( $parts[1] ) ? max( 1, (int) $parts[1] ) : 1;
				$price = isset( $parts[2] ) ? (float) $parts[2] : 0;
				if ( ! $path ) {
					continue;
				}
				$item = array(
					'productPath' => $path,
					'quantity'    => $qty,
				);
				if ( $price > 0 ) {
					$item['customPrice'] = array(
						'unitPrice' => array(
							$currency => $price,
						),
					);
				}
				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Build V2 create/update payload from POST.
	 *
	 * @return array
	 */
	private static function build_v2_payload_from_post() {
		$payload = array_filter(
			array(
				'locale'  => VMS_EFWP_Admin_Resource_Base::post_text( 'locale', 'en' ),
				'country' => strtoupper( VMS_EFWP_Admin_Resource_Base::post_text( 'country' ) ),
				'buyerIp' => VMS_EFWP_Admin_Resource_Base::post_text( 'buyer_ip' ),
			)
		);

		$email = VMS_EFWP_Admin_Resource_Base::post_email( 'email' );
		$first = VMS_EFWP_Admin_Resource_Base::post_text( 'first_name' );
		$last  = VMS_EFWP_Admin_Resource_Base::post_text( 'last_name' );
		$account_id = VMS_EFWP_Admin_Resource_Base::post_text( 'account_id' );

		$customer = array();
		if ( $account_id ) {
			$customer['accountId'] = $account_id;
		}
		if ( $email || $first || $last ) {
			$customer['billToContact'] = array_filter(
				array(
					'email'       => $email,
					'firstName'   => $first,
					'lastName'    => $last,
					'company'     => VMS_EFWP_Admin_Resource_Base::post_text( 'company' ),
					'phoneNumber' => VMS_EFWP_Admin_Resource_Base::post_text( 'phone' ),
				)
			);
		}
		if ( ! empty( $customer ) ) {
			$payload['customer'] = $customer;
		}

		$line_items = self::build_v2_line_items_from_post();
		$cart       = array();
		$coupon     = VMS_EFWP_Admin_Resource_Base::post_text( 'coupon_code' );
		if ( $coupon ) {
			$cart['couponCode'] = $coupon;
		}
		if ( ! empty( $line_items ) ) {
			$cart['lineItems'] = $line_items;
		}
		if ( ! empty( $cart ) ) {
			$payload['cart'] = $cart;
		}

		$tag_key   = VMS_EFWP_Admin_Resource_Base::post_text( 'tag_key' );
		$tag_value = VMS_EFWP_Admin_Resource_Base::post_text( 'tag_value' );
		if ( $tag_key && $tag_value ) {
			$payload['orderTags'] = array( $tag_key => $tag_value );
		}

		return $payload;
	}

	/**
	 * Handle V2 session create.
	 */
	private static function handle_create_v2() {
		$checkout_path = VMS_EFWP_Admin_Resource_Base::post_text( 'checkout_path', self::default_checkout_path() );
		$payload       = self::build_v2_payload_from_post();
		if ( empty( $payload['cart']['lineItems'] ) ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html__( 'At least one cart line item is required.', 'vms-elements-fastspring-woo-payment' ) );
			return;
		}

		$result = vms_efwp()->api->create_checkout_session( $checkout_path, $payload );
		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
			return;
		}

		VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
		self::render_session_links( $result, $checkout_path );
	}

	/**
	 * Handle V1 legacy session create.
	 */
	private static function handle_create_v1() {
		$products = array_filter( array_map( 'trim', explode( ',', VMS_EFWP_Admin_Resource_Base::post_text( 'products' ) ) ) );
		$items    = array();
		foreach ( $products as $p ) {
			$items[] = array( 'product' => $p, 'quantity' => 1 );
		}

		$payload = array(
			'items' => $items,
		);

		$account = VMS_EFWP_Admin_Resource_Base::post_text( 'account_id' );
		if ( $account ) {
			$payload['account'] = $account;
		}

		$first = VMS_EFWP_Admin_Resource_Base::post_text( 'first' );
		$last  = VMS_EFWP_Admin_Resource_Base::post_text( 'last' );
		$email = VMS_EFWP_Admin_Resource_Base::post_email( 'email' );
		if ( $email || $first || $last ) {
			$payload['contact'] = array_filter(
				array(
					'email'    => $email,
					'first'    => $first,
					'last'     => $last,
					'firstName' => $first,
					'lastName'  => $last,
					'company'  => VMS_EFWP_Admin_Resource_Base::post_text( 'company' ),
					'phone'    => VMS_EFWP_Admin_Resource_Base::post_text( 'phone' ),
					'country'  => strtoupper( VMS_EFWP_Admin_Resource_Base::post_text( 'country', 'US' ) ),
					'language' => VMS_EFWP_Admin_Resource_Base::post_text( 'language', 'en' ),
				)
			);
		}

		$coupon = VMS_EFWP_Admin_Resource_Base::post_text( 'coupon' );
		if ( $coupon ) {
			$payload['coupon'] = $coupon;
		}

		$expiration = VMS_EFWP_Admin_Resource_Base::post_int( 'expiration_days' );
		if ( $expiration > 0 ) {
			$payload['expiration'] = min( 7, $expiration );
		}

		$result = vms_efwp()->api->create_session( $payload );
		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
			return;
		}

		VMS_EFWP_Admin_Resource_Base::render_result_notice( $result );
		self::render_session_links( $result );
	}

	/**
	 * Handle lookup tab POST actions.
	 */
	private static function handle_lookup_actions() {
		$checkout_path = VMS_EFWP_Admin_Resource_Base::post_text( 'checkout_path', self::default_checkout_path() );
		$session_id    = VMS_EFWP_Admin_Resource_Base::post_text( 'session_id' );

		if ( VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_update_checkout_session' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_update_checkout_session' ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice(
				vms_efwp()->api->update_checkout_session( $checkout_path, $session_id, self::build_v2_payload_from_post() )
			);
		}

		if ( VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_add_session_item' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_add_session_item' ) ) {
			$item = array_filter(
				array(
					'productPath' => VMS_EFWP_Admin_Resource_Base::post_text( 'item_product_path' ),
					'quantity'    => max( 1, VMS_EFWP_Admin_Resource_Base::post_int( 'item_quantity', 1 ) ),
				)
			);
			VMS_EFWP_Admin_Resource_Base::render_result_notice(
				vms_efwp()->api->add_checkout_session_item( $checkout_path, $session_id, $item )
			);
		}

		if ( VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_update_session_item' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_update_session_item' ) ) {
			$product_path = VMS_EFWP_Admin_Resource_Base::post_text( 'item_product_path' );
			$item         = array(
				'quantity' => max( 1, VMS_EFWP_Admin_Resource_Base::post_int( 'item_quantity', 1 ) ),
			);
			VMS_EFWP_Admin_Resource_Base::render_result_notice(
				vms_efwp()->api->update_checkout_session_item( $checkout_path, $session_id, $product_path, $item )
			);
		}

		if ( VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_remove_session_item' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_remove_session_item' ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice(
				vms_efwp()->api->remove_checkout_session_item(
					$checkout_path,
					$session_id,
					VMS_EFWP_Admin_Resource_Base::post_text( 'item_product_path' )
				)
			);
		}

		if ( VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_update_session_customer' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_update_session_customer' ) ) {
			$customer = array(
				'billToContact' => array_filter(
					array(
						'email'       => VMS_EFWP_Admin_Resource_Base::post_email( 'email' ),
						'firstName'   => VMS_EFWP_Admin_Resource_Base::post_text( 'first_name' ),
						'lastName'    => VMS_EFWP_Admin_Resource_Base::post_text( 'last_name' ),
						'company'     => VMS_EFWP_Admin_Resource_Base::post_text( 'company' ),
						'phoneNumber' => VMS_EFWP_Admin_Resource_Base::post_text( 'phone' ),
					)
				),
			);
			VMS_EFWP_Admin_Resource_Base::render_result_notice(
				vms_efwp()->api->update_checkout_session_customer( $checkout_path, $session_id, $customer )
			);
		}
	}

	/**
	 * Render V2 create tab.
	 */
	private static function render_create_tab() {
		$checkout_path = self::default_checkout_path();
		?>
		<div class="vefwp-card">
			<h2><?php esc_html_e( 'Create checkout session', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Creates a session via POST /v2/checkouts/{checkoutPath}/sessions.', 'vms-elements-fastspring-woo-payment' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_create_checkout_session' ); ?>
				<input type="hidden" name="wpfs_create_checkout_session" value="1" />
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Checkout path', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="checkout_path" required class="regular-text" value="<?php echo esc_attr( $checkout_path ); ?>" placeholder="store-id/popup-checkout" /></label></p>
					<p><label><?php esc_html_e( 'Locale', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="locale" value="en" maxlength="5" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Country', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="country" maxlength="2" placeholder="US" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Buyer IP', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="buyer_ip" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Account ID', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="account_id" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Coupon code', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="coupon_code" class="regular-text" /></label></p>
				</div>

				<h3><?php esc_html_e( 'Customer', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Email', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="email" name="email" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Company', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="company" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'First name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="first_name" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Last name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="last_name" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Phone', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="phone" class="regular-text" /></label></p>
				</div>

				<h3><?php esc_html_e( 'Cart', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Product path', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="product_path" required class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Quantity', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" name="quantity" min="1" value="1" class="small-text" /></label></p>
					<p><label><?php esc_html_e( 'Currency', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="currency" maxlength="3" value="USD" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Custom unit price', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" step="0.01" name="unit_price" class="regular-text" /></label></p>
				</div>
				<p><label><?php esc_html_e( 'Additional products', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="additional_products" class="large-text" placeholder="addon:2:10.00, another-product" /></label></p>
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Order tag key', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="tag_key" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Order tag value', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="tag_value" class="regular-text" /></label></p>
				</div>

				<p><button class="button button-primary"><?php esc_html_e( 'Create session', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render V2 lookup and manage tab.
	 */
	private static function render_lookup_tab() {
		$checkout_path = self::get_checkout_path_input();
		$session_id    = self::get_session_id_input();
		$show_payments = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'payment_methods' );
		?>
		<form method="get" class="vefwp-filters vefwp-filters--lookup">
			<input type="hidden" name="page" value="vms-efwp-sessions" />
			<input type="hidden" name="tab" value="lookup" />
			<label>
				<?php esc_html_e( 'Checkout path', 'vms-elements-fastspring-woo-payment' ); ?>
				<input type="text" name="checkout_path" value="<?php echo esc_attr( $checkout_path ); ?>" class="regular-text" placeholder="store-id/popup-checkout" />
			</label>
			<label>
				<?php esc_html_e( 'Session ID', 'vms-elements-fastspring-woo-payment' ); ?>
				<input type="text" name="session_id" value="<?php echo esc_attr( $session_id ); ?>" class="regular-text" />
			</label>
			<button class="button button-primary"><?php esc_html_e( 'Lookup', 'vms-elements-fastspring-woo-payment' ); ?></button>
			<?php if ( $session_id && $checkout_path ) : ?>
				<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-sessions', 'tab' => 'lookup', 'checkout_path' => $checkout_path, 'session_id' => $session_id, 'payment_methods' => '1' ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Payment methods', 'vms-elements-fastspring-woo-payment' ); ?></a>
			<?php endif; ?>
		</form>
		<?php

		if ( ! $session_id ) {
			printf( '<p class="description">%s</p>', esc_html__( 'Retrieve a session via GET /v2/checkouts/{checkoutPath}/sessions/{sessionId}.', 'vms-elements-fastspring-woo-payment' ) );
			return;
		}

		if ( $show_payments ) {
			$methods = vms_efwp()->api->get_checkout_session_payment_methods( $checkout_path, $session_id );
			VMS_EFWP_Admin_Resource_Base::render_api_detail_card( $methods, __( 'Payment methods', 'vms-elements-fastspring-woo-payment' ) );
		}

		$session = vms_efwp()->api->get_checkout_session( $checkout_path, $session_id );
		if ( is_wp_error( $session ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $session );
			return;
		}

		self::render_session_summary( $session );
		self::render_session_links( $session, $checkout_path );
		self::render_manage_forms( $checkout_path, $session_id, $session );
		VMS_EFWP_Admin_Resource_Base::render_api_detail_card( $session, __( 'Session details', 'vms-elements-fastspring-woo-payment' ) );
	}

	/**
	 * Render session management forms on lookup.
	 *
	 * @param string $checkout_path Checkout path.
	 * @param string $session_id    Session ID.
	 * @param array  $session       Session data.
	 */
	private static function render_manage_forms( $checkout_path, $session_id, $session ) {
		$contact = $session['customer']['billToContact'] ?? array();
		?>
		<div class="vefwp-card">
			<h2><?php esc_html_e( 'Manage session', 'vms-elements-fastspring-woo-payment' ); ?></h2>

			<h3><?php esc_html_e( 'Update customer', 'vms-elements-fastspring-woo-payment' ); ?></h3>
			<form method="post" class="vefwp-inline-form">
				<?php wp_nonce_field( 'wpfs_update_session_customer' ); ?>
				<input type="hidden" name="wpfs_update_session_customer" value="1" />
				<input type="hidden" name="checkout_path" value="<?php echo esc_attr( $checkout_path ); ?>" />
				<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id ); ?>" />
				<div class="vefwp-grid vefwp-grid--two">
					<p><input type="email" name="email" value="<?php echo esc_attr( $contact['email'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Email', 'vms-elements-fastspring-woo-payment' ); ?>" class="regular-text" /></p>
					<p><input type="text" name="first_name" value="<?php echo esc_attr( $contact['firstName'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'First name', 'vms-elements-fastspring-woo-payment' ); ?>" class="regular-text" /></p>
					<p><input type="text" name="last_name" value="<?php echo esc_attr( $contact['lastName'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Last name', 'vms-elements-fastspring-woo-payment' ); ?>" class="regular-text" /></p>
					<p><input type="text" name="company" value="<?php echo esc_attr( $contact['company'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Company', 'vms-elements-fastspring-woo-payment' ); ?>" class="regular-text" /></p>
				</div>
				<p><button class="button"><?php esc_html_e( 'Update customer', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
			</form>

			<h3><?php esc_html_e( 'Cart item actions', 'vms-elements-fastspring-woo-payment' ); ?></h3>
			<form method="post" class="vefwp-inline-form">
				<?php wp_nonce_field( 'wpfs_add_session_item' ); ?>
				<input type="hidden" name="wpfs_add_session_item" value="1" />
				<input type="hidden" name="checkout_path" value="<?php echo esc_attr( $checkout_path ); ?>" />
				<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id ); ?>" />
				<p>
					<input type="text" name="item_product_path" placeholder="<?php esc_attr_e( 'Product path', 'vms-elements-fastspring-woo-payment' ); ?>" class="regular-text" />
					<input type="number" name="item_quantity" min="1" value="1" class="small-text" />
					<button class="button"><?php esc_html_e( 'Add item', 'vms-elements-fastspring-woo-payment' ); ?></button>
				</p>
			</form>

			<form method="post" class="vefwp-inline-form">
				<?php wp_nonce_field( 'wpfs_update_session_item' ); ?>
				<input type="hidden" name="wpfs_update_session_item" value="1" />
				<input type="hidden" name="checkout_path" value="<?php echo esc_attr( $checkout_path ); ?>" />
				<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id ); ?>" />
				<p>
					<input type="text" name="item_product_path" placeholder="<?php esc_attr_e( 'Product path', 'vms-elements-fastspring-woo-payment' ); ?>" class="regular-text" />
					<input type="number" name="item_quantity" min="1" value="1" class="small-text" />
					<button class="button"><?php esc_html_e( 'Update item', 'vms-elements-fastspring-woo-payment' ); ?></button>
				</p>
			</form>

			<form method="post" class="vefwp-inline-form">
				<?php wp_nonce_field( 'wpfs_remove_session_item' ); ?>
				<input type="hidden" name="wpfs_remove_session_item" value="1" />
				<input type="hidden" name="checkout_path" value="<?php echo esc_attr( $checkout_path ); ?>" />
				<input type="hidden" name="session_id" value="<?php echo esc_attr( $session_id ); ?>" />
				<p>
					<input type="text" name="item_product_path" placeholder="<?php esc_attr_e( 'Product path to remove', 'vms-elements-fastspring-woo-payment' ); ?>" class="regular-text" />
					<button class="button"><?php esc_html_e( 'Remove item', 'vms-elements-fastspring-woo-payment' ); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render legacy V1 tab.
	 */
	private static function render_legacy_tab() {
		?>
		<div class="vefwp-card">
			<h2><?php esc_html_e( 'Legacy Sessions v1', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Creates a session via POST /sessions. Maintained for existing WooCommerce checkout integrations.', 'vms-elements-fastspring-woo-payment' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'wpfs_create_session_v1' ); ?>
				<input type="hidden" name="wpfs_create_session_v1" value="1" />
				<div class="vefwp-grid vefwp-grid--two">
					<p><label><?php esc_html_e( 'Account ID', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="account_id" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Email', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="email" name="email" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'First name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="first" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Last name', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="last" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Country', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="country" maxlength="2" value="US" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Language', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="language" value="en" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Products (comma separated)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" required name="products" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Coupon', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="text" name="coupon" class="regular-text" /></label></p>
					<p><label><?php esc_html_e( 'Expiration (days, max 7)', 'vms-elements-fastspring-woo-payment' ); ?><br /><input type="number" name="expiration_days" min="1" max="7" class="small-text" /></label></p>
				</div>
				<p><button class="button button-primary"><?php esc_html_e( 'Create v1 session', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render session summary card.
	 *
	 * @param array $session Session object.
	 */
	private static function render_session_summary( $session ) {
		$cart = $session['cart'] ?? array();
		?>
		<div class="vefwp-card vefwp-card--wide">
			<h2><?php esc_html_e( 'Session summary', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<table class="widefat striped vefwp-table vefwp-table--meta">
				<tbody>
					<tr><th><?php esc_html_e( 'Session ID', 'vms-elements-fastspring-woo-payment' ); ?></th><td><code><?php echo esc_html( $session['id'] ?? '' ); ?></code></td></tr>
					<tr><th><?php esc_html_e( 'Status', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $session['status'] ?? ( $session['checkoutStatus'] ?? '' ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Currency', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $session['currency'] ?? '' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Country', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $session['country'] ?? '' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Expires', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $session['expires'] ?? '' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Total', 'vms-elements-fastspring-woo-payment' ); ?></th><td><?php echo esc_html( $cart['totalDisplay'] ?? ( isset( $cart['total'] ) ? number_format_i18n( (float) $cart['total'], 2 ) : '' ) ); ?></td></tr>
				</tbody>
			</table>

			<?php if ( ! empty( $cart['lineItems'] ) && is_array( $cart['lineItems'] ) ) : ?>
				<h3><?php esc_html_e( 'Line items', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<table class="widefat striped vefwp-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Product', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Display', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Qty', 'vms-elements-fastspring-woo-payment' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $cart['lineItems'] as $item ) : ?>
							<tr>
								<td><code><?php echo esc_html( $item['productPath'] ?? ( $item['product'] ?? '' ) ); ?></code></td>
								<td><?php echo esc_html( $item['display'] ?? '' ); ?></td>
								<td><?php echo esc_html( (string) ( $item['quantity'] ?? 1 ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render checkout URL links when available.
	 *
	 * @param array  $session       Session response.
	 * @param string $checkout_path Optional checkout path for lookup link.
	 */
	private static function render_session_links( $session, $checkout_path = '' ) {
		$session_id = $session['id'] ?? '';
		$web_url    = $session['checkoutUrls']['webcheckoutUrl'] ?? '';
		if ( ! $web_url && $session_id ) {
			$storefront = vms_efwp()->settings->storefront();
			if ( $storefront ) {
				$web_url = sprintf( 'https://%s/session/%s', $storefront, rawurlencode( $session_id ) );
			}
		}

		if ( ! $session_id && ! $web_url ) {
			return;
		}
		?>
		<div class="vefwp-card vefwp-card--actions">
			<?php if ( $web_url ) : ?>
				<a class="button button-primary" href="<?php echo esc_url( $web_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open checkout', 'vms-elements-fastspring-woo-payment' ); ?></a>
			<?php endif; ?>
			<?php if ( $session_id && $checkout_path ) : ?>
				<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-sessions', 'tab' => 'lookup', 'checkout_path' => $checkout_path, 'session_id' => $session_id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Manage session', 'vms-elements-fastspring-woo-payment' ); ?></a>
			<?php endif; ?>
		</div>
		<?php
	}
}
