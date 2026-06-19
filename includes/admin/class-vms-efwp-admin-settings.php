<?php
/**
 * Settings screen.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Settings.
 */
class VMS_EFWP_Admin_Settings {

	/**
	 * Render settings.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = vms_efwp()->settings;
		$mode     = $settings->get_mode();

		?>
		<div class="wrap vefwp-wrap">
			<h1><?php esc_html_e( 'FastSpring Settings', 'vms-elements-fastspring-woo-payment' ); ?></h1>

			<?php settings_errors( 'vms_efwp' ); ?>

			<?php self::render_checkout_diagnostics(); ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="vefwp-form">
				<?php wp_nonce_field( 'vms_efwp_settings_save', 'vms_efwp_nonce' ); ?>
				<input type="hidden" name="action" value="vms_efwp_save_settings" />

				<div class="vefwp-card">
					<h2><?php esc_html_e( 'Mode', 'vms-elements-fastspring-woo-payment' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Switch between your FastSpring sandbox (test) and live environment. Each mode uses its own API credentials, storefront and webhook secret below.', 'vms-elements-fastspring-woo-payment' ); ?></p>
					<div class="vefwp-mode-switch">
						<label class="vefwp-mode-option <?php echo 'sandbox' === $mode ? 'is-active' : ''; ?>">
							<input type="radio" name="mode" value="sandbox" <?php checked( $mode, 'sandbox' ); ?> />
							<span class="vefwp-mode-option__title"><?php esc_html_e( 'Sandbox / Test', 'vms-elements-fastspring-woo-payment' ); ?></span>
							<span class="vefwp-mode-option__desc"><?php esc_html_e( 'Use test credentials and a *.test.onfastspring.com storefront. No real charges.', 'vms-elements-fastspring-woo-payment' ); ?></span>
						</label>
						<label class="vefwp-mode-option <?php echo 'live' === $mode ? 'is-active' : ''; ?>">
							<input type="radio" name="mode" value="live" <?php checked( $mode, 'live' ); ?> />
							<span class="vefwp-mode-option__title"><?php esc_html_e( 'Live / Production', 'vms-elements-fastspring-woo-payment' ); ?></span>
							<span class="vefwp-mode-option__desc"><?php esc_html_e( 'Real customers, real charges, real revenue.', 'vms-elements-fastspring-woo-payment' ); ?></span>
						</label>
					</div>
				</div>

				<div class="vefwp-grid vefwp-grid--two">
					<div class="vefwp-card">
						<h2><?php esc_html_e( 'Sandbox credentials', 'vms-elements-fastspring-woo-payment' ); ?></h2>
						<table class="form-table">
							<tr>
								<th><label for="sandbox_storefront"><?php esc_html_e( 'Storefront', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
								<td><input type="text" id="sandbox_storefront" name="sandbox_storefront" class="regular-text" placeholder="yourcompany.test.onfastspring.com" value="<?php echo esc_attr( $settings->get( 'sandbox_storefront' ) ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="sandbox_username"><?php esc_html_e( 'API Username', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
								<td><input type="text" id="sandbox_username" name="sandbox_username" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $settings->get( 'sandbox_username' ) ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="sandbox_password"><?php esc_html_e( 'API Password', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
								<td><input type="password" id="sandbox_password" name="sandbox_password" class="regular-text" autocomplete="new-password" value="<?php echo esc_attr( $settings->get( 'sandbox_password' ) ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="webhook_secret_sandbox"><?php esc_html_e( 'Webhook secret', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
								<td>
									<div class="vefwp-secret-field">
										<input type="text" id="webhook_secret_sandbox" name="webhook_secret_sandbox" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $settings->get( 'webhook_secret_sandbox' ) ); ?>" />
										<button type="button" class="button vefwp-generate-secret" data-target="webhook_secret_sandbox"><?php esc_html_e( 'Generate', 'vms-elements-fastspring-woo-payment' ); ?></button>
										<button type="button" class="button vefwp-copy-secret" data-target="webhook_secret_sandbox"><?php esc_html_e( 'Copy', 'vms-elements-fastspring-woo-payment' ); ?></button>
									</div>
									<p class="description"><?php esc_html_e( 'Paste this same value into the HMAC SHA256 Secret box on FastSpring → Integrations → Webhooks.', 'vms-elements-fastspring-woo-payment' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="sandbox_access_key"><?php esc_html_e( 'Store Builder access key', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
								<td>
									<input type="text" id="sandbox_access_key" name="sandbox_access_key" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $settings->get( 'sandbox_access_key' ) ); ?>" placeholder="WVD4LEQOT0UV0HKWYTDJSA" />
									<p class="description"><?php esc_html_e( 'Required for custom WooCommerce prices in the popup. FastSpring App → Developer Tools → Store Builder Library → Access Key (sandbox).', 'vms-elements-fastspring-woo-payment' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<div class="vefwp-card">
						<h2><?php esc_html_e( 'Live credentials', 'vms-elements-fastspring-woo-payment' ); ?></h2>
						<table class="form-table">
							<tr>
								<th><label for="live_storefront"><?php esc_html_e( 'Storefront', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
								<td><input type="text" id="live_storefront" name="live_storefront" class="regular-text" placeholder="yourcompany.onfastspring.com" value="<?php echo esc_attr( $settings->get( 'live_storefront' ) ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="live_username"><?php esc_html_e( 'API Username', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
								<td><input type="text" id="live_username" name="live_username" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $settings->get( 'live_username' ) ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="live_password"><?php esc_html_e( 'API Password', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
								<td><input type="password" id="live_password" name="live_password" class="regular-text" autocomplete="new-password" value="<?php echo esc_attr( $settings->get( 'live_password' ) ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="webhook_secret_live"><?php esc_html_e( 'Webhook secret', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
								<td>
									<div class="vefwp-secret-field">
										<input type="text" id="webhook_secret_live" name="webhook_secret_live" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $settings->get( 'webhook_secret_live' ) ); ?>" />
										<button type="button" class="button vefwp-generate-secret" data-target="webhook_secret_live"><?php esc_html_e( 'Generate', 'vms-elements-fastspring-woo-payment' ); ?></button>
										<button type="button" class="button vefwp-copy-secret" data-target="webhook_secret_live"><?php esc_html_e( 'Copy', 'vms-elements-fastspring-woo-payment' ); ?></button>
									</div>
									<p class="description"><?php esc_html_e( 'Paste this same value into the HMAC SHA256 Secret box on FastSpring → Integrations → Webhooks (Live).', 'vms-elements-fastspring-woo-payment' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="live_access_key"><?php esc_html_e( 'Store Builder access key', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
								<td>
									<input type="text" id="live_access_key" name="live_access_key" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $settings->get( 'live_access_key' ) ); ?>" />
									<p class="description"><?php esc_html_e( 'Live Store Builder access key (same location as sandbox).', 'vms-elements-fastspring-woo-payment' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="vefwp-card">
					<h2><?php esc_html_e( 'Webhook endpoint', 'vms-elements-fastspring-woo-payment' ); ?></h2>
					<ol class="description" style="margin-top:0;">
						<li><?php esc_html_e( 'Open FastSpring App → Integrations → Webhooks → Add Webhook.', 'vms-elements-fastspring-woo-payment' ); ?></li>
						<li><?php esc_html_e( 'Paste the URL below into the Webhook URL field.', 'vms-elements-fastspring-woo-payment' ); ?></li>
						<li><?php esc_html_e( 'In the HMAC SHA256 Secret box, paste the same secret you configured for the active mode above. FastSpring does not auto-generate one — use the Generate button next to the secret field if you don\'t have a value yet.', 'vms-elements-fastspring-woo-payment' ); ?></li>
						<li><?php esc_html_e( 'Tick the events you want to listen to (or "All events"), then save in FastSpring and here.', 'vms-elements-fastspring-woo-payment' ); ?></li>
					</ol>
					<input type="text" readonly value="<?php echo esc_attr( $settings->webhook_url() ); ?>" class="large-text vefwp-readonly" onclick="this.select()" />
					<p>
						<label><input type="checkbox" name="enable_webhook" value="yes" <?php checked( 'yes', $settings->get( 'enable_webhook', 'yes' ) ); ?> /> <?php esc_html_e( 'Enable webhook listener', 'vms-elements-fastspring-woo-payment' ); ?></label>
					</p>
				</div>

				<div class="vefwp-card">
					<h2><?php esc_html_e( 'Pricing strategy', 'vms-elements-fastspring-woo-payment' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Decides how WooCommerce sends prices to FastSpring at checkout. Pick the option that fits your catalog.', 'vms-elements-fastspring-woo-payment' ); ?></p>

					<?php
					$strategy   = $settings->pricing_strategy();
					$saved_path = (string) $settings->get( 'custom_price_product_path', '' );
					?>
					<div class="vefwp-pricing-strategy">
						<label class="vefwp-mode-option <?php echo 'single_custom_price' === $strategy ? 'is-active' : ''; ?>">
							<input type="radio" name="pricing_strategy" value="single_custom_price" <?php checked( $strategy, 'single_custom_price' ); ?> />
							<span class="vefwp-mode-option__title"><?php esc_html_e( 'Single Custom Price product (recommended — works with any product)', 'vms-elements-fastspring-woo-payment' ); ?></span>
							<span class="vefwp-mode-option__desc">
								<?php esc_html_e( 'Every WooCommerce order is charged through ONE catch-all product in FastSpring, using the exact WooCommerce total (after coupons, sales, dynamic pricing). You do NOT need to recreate your WooCommerce catalog in FastSpring — any product can be sold as-is. The plugin auto-creates the catch-all product for you on the first checkout.', 'vms-elements-fastspring-woo-payment' ); ?>
							</span>
						</label>

						<label class="vefwp-mode-option <?php echo 'per_product_override' === $strategy ? 'is-active' : ''; ?>">
							<input type="radio" name="pricing_strategy" value="per_product_override" <?php checked( $strategy, 'per_product_override' ); ?> />
							<span class="vefwp-mode-option__title"><?php esc_html_e( 'Per-product price override', 'vms-elements-fastspring-woo-payment' ); ?></span>
							<span class="vefwp-mode-option__desc">
								<?php esc_html_e( 'Each WC product maps to its own FastSpring product (matched by slug). The actual WC line price is sent as an override. Use this only if you want each product tracked individually in FastSpring analytics — every product must already exist in your FastSpring catalog (enable product sync below to push them).', 'vms-elements-fastspring-woo-payment' ); ?>
							</span>
						</label>

						<label class="vefwp-mode-option <?php echo 'catalog' === $strategy ? 'is-active' : ''; ?>">
							<input type="radio" name="pricing_strategy" value="catalog" <?php checked( $strategy, 'catalog' ); ?> />
							<span class="vefwp-mode-option__title"><?php esc_html_e( 'FastSpring catalog price', 'vms-elements-fastspring-woo-payment' ); ?></span>
							<span class="vefwp-mode-option__desc">
								<?php esc_html_e( 'Customer is charged the price configured in FastSpring for each product. WooCommerce price is ignored. Pick this only if FastSpring is your source of truth for pricing, and every product exists in your FastSpring catalog.', 'vms-elements-fastspring-woo-payment' ); ?>
							</span>
						</label>
					</div>

					<table class="form-table">
						<tr class="vefwp-custom-price-row" <?php echo 'single_custom_price' === $strategy ? '' : 'style="display:none;"'; ?>>
							<th><label for="custom_price_product_path"><?php esc_html_e( 'Catch-all product path', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
							<td>
								<input type="text" id="custom_price_product_path" name="custom_price_product_path" class="regular-text" value="<?php echo esc_attr( $saved_path ); ?>" placeholder="<?php echo esc_attr( VMS_EFWP_Settings::DEFAULT_CUSTOM_PRICE_PATH ); ?>" data-vefwp-slug-target autocomplete="off" />
								<p class="description">
									<?php
									printf(
										/* translators: %s: default product path */
										esc_html__( 'Leave blank to let the plugin auto-create and use "%s". The product price in FastSpring is irrelevant — each order overrides it with the real WooCommerce total. You can also paste the path of an existing FastSpring product here.', 'vms-elements-fastspring-woo-payment' ),
										esc_html( VMS_EFWP_Settings::DEFAULT_CUSTOM_PRICE_PATH )
									);
									?>
								</p>
							</td>
						</tr>
					</table>

					<?php if ( 'single_custom_price' === $strategy ) : ?>
						<p>
							<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=vms_efwp_provision_custom_price' ), 'vms_efwp_provision_custom_price', 'vms_efwp_nonce' ) ); ?>">
								<?php esc_html_e( 'Create / verify catch-all product now', 'vms-elements-fastspring-woo-payment' ); ?>
							</a>
							<span class="description"><?php esc_html_e( 'Optional — checks the catch-all product exists in FastSpring (creating it if needed) so the first real checkout is instant.', 'vms-elements-fastspring-woo-payment' ); ?></span>
						</p>
					<?php endif; ?>
				</div>

				<div class="vefwp-card">
					<h2><?php esc_html_e( 'WooCommerce integration', 'vms-elements-fastspring-woo-payment' ); ?></h2>
					<table class="form-table">
						<tr>
							<th><label for="gateway_title"><?php esc_html_e( 'Gateway title', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
							<td><input type="text" id="gateway_title" name="gateway_title" class="regular-text" value="<?php echo esc_attr( $settings->get( 'gateway_title' ) ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="gateway_description"><?php esc_html_e( 'Gateway description', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
							<td><textarea id="gateway_description" name="gateway_description" class="large-text" rows="2"><?php echo esc_textarea( $settings->get( 'gateway_description' ) ); ?></textarea></td>
						</tr>
						<tr>
							<th><label for="popup_path"><?php esc_html_e( 'Popup checkout path', 'vms-elements-fastspring-woo-payment' ); ?></label></th>
							<td>
								<input type="text" id="popup_path" name="popup_path" class="regular-text" value="<?php echo esc_attr( $settings->get( 'popup_path', '' ) ); ?>" placeholder="popup-vmsuniverse2026" />
								<p class="description">
									<?php esc_html_e( 'Required for popup-only checkout via the FastSpring Store Builder Library (SBL 1.0.7). In FastSpring → Checkouts → Popup Checkouts → "Place on your website", copy the part AFTER the domain from data-storefront (example: vmsuniverse2026.test.onfastspring.com/popup-vmsuniverse2026 → enter popup-vmsuniverse2026). Whitelist your WordPress site under the checkout\'s "Whitelisted websites". Works with both Gutenberg Checkout block and the classic [woocommerce_checkout] shortcode.', 'vms-elements-fastspring-woo-payment' ); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Product sync', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<td>
								<label><input type="checkbox" name="sync_products" value="yes" <?php checked( 'yes', $settings->get( 'sync_products', 'no' ) ); ?> /> <?php esc_html_e( 'Push WooCommerce products to FastSpring on save (one-way)', 'vms-elements-fastspring-woo-payment' ); ?></label>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Logging', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<td>
								<label><input type="checkbox" name="enable_logging" value="yes" <?php checked( 'yes', $settings->get( 'enable_logging', 'yes' ) ); ?> /> <?php esc_html_e( 'Record info-level events to the FastSpring log table', 'vms-elements-fastspring-woo-payment' ); ?></label>
							</td>
						</tr>
					</table>
				</div>

				<p>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Save settings', 'vms-elements-fastspring-woo-payment' ); ?></button>
					<button type="button" class="button" id="vefwp-test-connection"><?php esc_html_e( 'Test connection', 'vms-elements-fastspring-woo-payment' ); ?></button>
					<span class="vefwp-test-result" id="vefwp-test-result"></span>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render a diagnostic panel that explains exactly why the gateway is or
	 * isn't going to appear at checkout. Saves a lot of head-scratching.
	 */
	private static function render_checkout_diagnostics() {
		$settings = vms_efwp()->settings;

		$wc_active    = class_exists( 'WooCommerce' );
		$wc_version   = defined( 'WC_VERSION' ) ? WC_VERSION : ( $wc_active && function_exists( 'WC' ) ? WC()->version : '' );
		$gateway_opt  = get_option( 'woocommerce_vms_efwp_settings', array() );
		$gateway_on   = is_array( $gateway_opt ) && ( $gateway_opt['enabled'] ?? 'no' ) === 'yes';
		$has_creds    = $settings->has_credentials();
		$has_storefr  = '' !== $settings->storefront();
		$has_popup    = $settings->has_popup_checkout();

		// Detect Cart/Checkout Blocks availability via several signals.
		$blocks_pkg    = class_exists( 'Automattic\WooCommerce\Blocks\Package' );
		$blocks_abs    = class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' );
		$blocks_hooked = did_action( 'woocommerce_blocks_loaded' ) > 0;
		$blocks_class  = class_exists( 'VMS_EFWP_WC_Blocks' );
		$blocks_ok     = ( $blocks_pkg || $blocks_abs || $blocks_hooked ) && $blocks_class;

		// Most important check: ask the real Blocks Payment Method Registry
		// whether our gateway is currently registered with it. If this is
		// "no", the gateway will NEVER appear on a block-based checkout.
		$blocks_registered = null;
		$blocks_registry_methods = array();
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Package' ) && class_exists( 'Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry' ) ) {
			try {
				$container = Automattic\WooCommerce\Blocks\Package::container();
				if ( $container ) {
					$registry = $container->get( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry::class );
					if ( $registry && method_exists( $registry, 'get_all_registered' ) ) {
						$registered = $registry->get_all_registered();
						$blocks_registry_methods = is_array( $registered ) ? array_keys( $registered ) : array();
						$blocks_registered = in_array( 'vms_efwp', $blocks_registry_methods, true );
					}
				}
			} catch ( \Throwable $e ) {
				$blocks_registered = null;
			}
		}

		// Build a helpful "fix" string for the Blocks row.
		$blocks_fix = '';
		if ( ! $blocks_ok ) {
			if ( ! $wc_active ) {
				$blocks_fix = __( 'WooCommerce is not active.', 'vms-elements-fastspring-woo-payment' );
			} elseif ( ! $blocks_pkg && ! $blocks_abs && ! $blocks_hooked ) {
				$blocks_fix = sprintf(
					/* translators: %s WC version */
					__( 'Your WooCommerce (%s) does not expose the Cart/Checkout Blocks API. Update WooCommerce to 6.9+ (8.0+ recommended) or install the "WooCommerce Blocks" plugin. The classic checkout still works.', 'vms-elements-fastspring-woo-payment' ),
					$wc_version ? esc_html( $wc_version ) : esc_html__( 'unknown version', 'vms-elements-fastspring-woo-payment' )
				);
			} elseif ( ! $blocks_class ) {
				$blocks_fix = __( 'Block API is available but the plugin\'s Blocks integration did not load. Try deactivating and reactivating VMS Elements Fastspring Woo Payment.', 'vms-elements-fastspring-woo-payment' );
			}
		}

		// Detect whether the checkout page uses the block or the shortcode.
		$checkout_page_id   = 0;
		$checkout_uses_blocks = null;
		if ( function_exists( 'wc_get_page_id' ) ) {
			$checkout_page_id = wc_get_page_id( 'checkout' );
			if ( $checkout_page_id > 0 ) {
				$page_content = (string) get_post_field( 'post_content', $checkout_page_id );
				if ( false !== strpos( $page_content, 'wp:woocommerce/checkout' ) ) {
					$checkout_uses_blocks = 'blocks';
				} elseif ( false !== strpos( $page_content, '[woocommerce_checkout' ) ) {
					$checkout_uses_blocks = 'classic';
				}
			}
		}

		$checks = array(
			array(
				'label' => __( 'WooCommerce active', 'vms-elements-fastspring-woo-payment' ),
				'ok'    => $wc_active,
				'fix'   => __( 'Install and activate WooCommerce.', 'vms-elements-fastspring-woo-payment' ),
			),
			array(
				'label' => __( 'FastSpring gateway enabled in WooCommerce', 'vms-elements-fastspring-woo-payment' ),
				'ok'    => $gateway_on,
				'fix'   => sprintf(
					/* translators: %s URL */
					__( 'Open %s and toggle "Enable FastSpring".', 'vms-elements-fastspring-woo-payment' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=vms_efwp' ) ) . '">WooCommerce → Settings → Payments → FastSpring</a>'
				),
			),
			array(
				'label' => __( 'API credentials configured for active mode', 'vms-elements-fastspring-woo-payment' ),
				'ok'    => $has_creds,
				'fix'   => __( 'Fill in API username and password for the active mode below.', 'vms-elements-fastspring-woo-payment' ),
			),
			array(
				'label' => __( 'Storefront ID configured for active mode', 'vms-elements-fastspring-woo-payment' ),
				'ok'    => $has_storefr,
				'fix'   => __( 'Add your *.onfastspring.com storefront domain for the active mode below (hostname only, e.g. vmsuniverse2026.test.onfastspring.com).', 'vms-elements-fastspring-woo-payment' ),
			),
			array(
				'label' => __( 'Popup checkout path configured (Store Builder overlay)', 'vms-elements-fastspring-woo-payment' ),
				'ok'    => $has_popup,
				'fix'   => __( 'Set the Popup checkout path below (e.g. popup-vmsuniverse2026) from FastSpring → Checkouts → Popup Checkouts → Place on your website.', 'vms-elements-fastspring-woo-payment' ),
			),
			array(
				'label' => __( 'Block-based Checkout integration available', 'vms-elements-fastspring-woo-payment' ),
				'ok'    => $blocks_ok,
				'fix'   => $blocks_fix,
				'warn'  => true,
			),
		);

		$all_ok = true;
		foreach ( $checks as $c ) {
			if ( ! $c['ok'] && empty( $c['warn'] ) ) {
				$all_ok = false;
				break;
			}
		}
		// Run a real WooCommerce availability check so we can show the truth.
		$gateways_available = array();
		$gateways_all       = array();
		$fastspring_visible = null;
		$fs_issues          = array();
		if ( $wc_active && function_exists( 'WC' ) && WC()->payment_gateways() ) {
			$pgs                = WC()->payment_gateways();
			$gateways_all       = $pgs->payment_gateways();
			$gateways_available = $pgs->get_available_payment_gateways();
			$fastspring_visible = isset( $gateways_available['vms_efwp'] );
			if ( isset( $gateways_all['vms_efwp'] ) && method_exists( $gateways_all['vms_efwp'], 'get_availability_issues' ) ) {
				$fs_issues = $gateways_all['vms_efwp']->get_availability_issues();
			}
		}

		$issue_messages = array(
			'gateway_disabled'    => __( 'The FastSpring toggle is OFF in WooCommerce → Settings → Payments → FastSpring.', 'vms-elements-fastspring-woo-payment' ),
			'plugin_not_loaded'   => __( 'The VMS Elements Fastspring Woo Payment plugin failed to initialise. Try deactivating and reactivating it.', 'vms-elements-fastspring-woo-payment' ),
			'missing_credentials' => __( 'API username or password is empty for the active mode.', 'vms-elements-fastspring-woo-payment' ),
			'missing_storefront'  => __( 'Storefront ID is empty for the active mode.', 'vms-elements-fastspring-woo-payment' ),
			'missing_popup_path'  => __( 'Popup checkout path is missing. Set it in WooCommerce integration below (e.g. popup-vmsuniverse2026).', 'vms-elements-fastspring-woo-payment' ),
		);
		?>
		<div class="vefwp-card">
			<h2><?php esc_html_e( 'Checkout availability', 'vms-elements-fastspring-woo-payment' ); ?>
				<?php if ( $all_ok ) : ?>
					<span class="vefwp-badge vefwp-badge--ok"><?php esc_html_e( 'READY', 'vms-elements-fastspring-woo-payment' ); ?></span>
				<?php else : ?>
					<span class="vefwp-badge vefwp-badge--warning"><?php esc_html_e( 'NEEDS ATTENTION', 'vms-elements-fastspring-woo-payment' ); ?></span>
				<?php endif; ?>
			</h2>
			<p class="description">
				<?php
				if ( 'blocks' === $checkout_uses_blocks ) {
					esc_html_e( 'Your Checkout page uses the WooCommerce Checkout block. The gateway needs the Blocks integration row below to be green for customers to see FastSpring.', 'vms-elements-fastspring-woo-payment' );
				} elseif ( 'classic' === $checkout_uses_blocks ) {
					esc_html_e( 'Your Checkout page uses the classic [woocommerce_checkout] shortcode. The Blocks row can be ignored — the gateway will show as long as the first five rows are green.', 'vms-elements-fastspring-woo-payment' );
				} else {
					esc_html_e( 'If FastSpring isn\'t appearing on the checkout page, every required row below should be green.', 'vms-elements-fastspring-woo-payment' );
				}
				?>
			</p>
			<table class="widefat striped vefwp-table">
				<tbody>
				<?php foreach ( $checks as $c ) : ?>
					<tr>
						<td style="width:60px;">
							<?php if ( $c['ok'] ) : ?>
								<span class="vefwp-badge vefwp-badge--ok">OK</span>
							<?php elseif ( ! empty( $c['warn'] ) ) : ?>
								<span class="vefwp-badge vefwp-badge--info">INFO</span>
							<?php else : ?>
								<span class="vefwp-badge vefwp-badge--warning">!</span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $c['label'] ); ?></td>
						<td>
							<?php if ( ! $c['ok'] && ! empty( $c['fix'] ) ) : ?>
								<span class="description"><?php echo wp_kses_post( $c['fix'] ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php if ( $checkout_page_id > 0 ) : ?>
				<p class="description" style="margin-top:8px;">
					<?php
					printf(
						/* translators: 1: checkout page edit URL, 2: page id */
						wp_kses_post( __( 'Checkout page detected: <a href="%1$s">edit page #%2$d</a>. Switching between the classic shortcode and the Checkout block here is the fastest way to verify the gateway appears.', 'vms-elements-fastspring-woo-payment' ) ),
						esc_url( get_edit_post_link( $checkout_page_id ) ),
						(int) $checkout_page_id
					);
					?>
				</p>
			<?php endif; ?>

			<?php if ( $wc_active && null !== $blocks_registered ) : ?>
				<hr style="margin:18px 0;" />
				<h3 style="margin:0 0 8px;"><?php esc_html_e( 'Blocks Checkout registration', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<?php if ( $blocks_registered ) : ?>
					<div class="notice notice-success inline"><p>
						<strong><?php esc_html_e( 'FastSpring IS registered with the Cart/Checkout Blocks Payment Method Registry.', 'vms-elements-fastspring-woo-payment' ); ?></strong>
						<?php esc_html_e( 'It will show on the block-based checkout. If it still doesn\'t appear, force-refresh the page (Ctrl+F5) and clear any caching plugin.', 'vms-elements-fastspring-woo-payment' ); ?>
					</p></div>
				<?php else : ?>
					<div class="notice notice-error inline"><p>
						<strong><?php esc_html_e( 'FastSpring is NOT registered with the Blocks Payment Method Registry.', 'vms-elements-fastspring-woo-payment' ); ?></strong>
						<?php esc_html_e( 'This is why it does not appear on a block-based checkout. Deactivate and reactivate VMS Elements Fastspring Woo Payment once to trigger registration, then revisit this page.', 'vms-elements-fastspring-woo-payment' ); ?>
					</p></div>
				<?php endif; ?>
				<details class="vefwp-details">
					<summary><?php esc_html_e( 'Show all Blocks-registered payment methods', 'vms-elements-fastspring-woo-payment' ); ?></summary>
					<ul class="vefwp-gateway-list">
						<?php if ( empty( $blocks_registry_methods ) ) : ?>
							<li><em><?php esc_html_e( 'None — Blocks payment registry is empty.', 'vms-elements-fastspring-woo-payment' ); ?></em></li>
						<?php else : ?>
							<?php foreach ( $blocks_registry_methods as $name ) : ?>
								<li>
									<span class="vefwp-badge vefwp-badge--<?php echo 'vms_efwp' === $name ? 'ok' : 'info'; ?>"><?php echo 'vms_efwp' === $name ? 'OURS' : 'BLOCK'; ?></span>
									<code><?php echo esc_html( $name ); ?></code>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</details>
			<?php endif; ?>

			<?php if ( $wc_active ) : ?>
				<hr style="margin:18px 0;" />
				<h3 style="margin:0 0 8px;"><?php esc_html_e( 'Live availability test', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<p class="description" style="margin-top:0;">
					<?php esc_html_e( 'This runs WooCommerce\'s actual checkout pipeline right now. If FastSpring appears in the green list below, it will appear at checkout for shoppers in the same situation.', 'vms-elements-fastspring-woo-payment' ); ?>
				</p>

				<?php if ( null === $fastspring_visible ) : ?>
					<div class="notice notice-warning inline"><p><?php esc_html_e( 'Could not query WooCommerce gateways from this admin page.', 'vms-elements-fastspring-woo-payment' ); ?></p></div>
				<?php elseif ( $fastspring_visible ) : ?>
					<div class="notice notice-success inline"><p>
						<strong><?php esc_html_e( 'FastSpring is currently available at checkout.', 'vms-elements-fastspring-woo-payment' ); ?></strong>
						<?php esc_html_e( 'If shoppers still don\'t see it, hard-refresh the page or clear caches.', 'vms-elements-fastspring-woo-payment' ); ?>
					</p></div>
				<?php else : ?>
					<div class="notice notice-error inline"><p>
						<strong><?php esc_html_e( 'FastSpring is currently HIDDEN at checkout.', 'vms-elements-fastspring-woo-payment' ); ?></strong>
						<?php if ( $fs_issues ) : ?>
							<br />
							<?php foreach ( $fs_issues as $code ) : ?>
								&bull; <?php echo esc_html( isset( $issue_messages[ $code ] ) ? $issue_messages[ $code ] : $code ); ?><br />
							<?php endforeach; ?>
						<?php else : ?>
							<br />
							<?php esc_html_e( 'The gateway passes all FastSpring-specific checks, so it is most likely being filtered out by your theme, another plugin, or by a cart-level rule (zero total, country/currency restriction, etc).', 'vms-elements-fastspring-woo-payment' ); ?>
						<?php endif; ?>
					</p></div>
				<?php endif; ?>

				<details class="vefwp-details">
					<summary><?php esc_html_e( 'Show all currently-available gateways', 'vms-elements-fastspring-woo-payment' ); ?></summary>
					<ul class="vefwp-gateway-list">
						<?php if ( empty( $gateways_available ) ) : ?>
							<li><em><?php esc_html_e( 'No gateways are available right now.', 'vms-elements-fastspring-woo-payment' ); ?></em></li>
						<?php else : ?>
							<?php foreach ( $gateways_available as $id => $gw ) : ?>
								<li>
									<span class="vefwp-badge vefwp-badge--ok">SHOWN</span>
									<code><?php echo esc_html( $id ); ?></code> — <?php echo esc_html( $gw->get_title() ); ?>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</details>

				<details class="vefwp-details">
					<summary><?php esc_html_e( 'Show all registered gateways (including hidden)', 'vms-elements-fastspring-woo-payment' ); ?></summary>
					<ul class="vefwp-gateway-list">
						<?php foreach ( $gateways_all as $id => $gw ) : ?>
							<?php $shown = isset( $gateways_available[ $id ] ); ?>
							<li>
								<span class="vefwp-badge vefwp-badge--<?php echo $shown ? 'ok' : 'warning'; ?>"><?php echo $shown ? 'SHOWN' : 'HIDDEN'; ?></span>
								<code><?php echo esc_html( $id ); ?></code> — <?php echo esc_html( $gw->get_method_title() ); ?>
								<?php if ( $gw->enabled !== 'yes' ) : ?>
									<span class="description"><?php esc_html_e( '(disabled)', 'vms-elements-fastspring-woo-payment' ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</details>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Persist settings (admin-post handler).
	 */
	public static function handle_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'vms_efwp_settings_save', 'vms_efwp_nonce' );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Fields sanitized individually below.
		$post = wp_unslash( $_POST );

		$strategy = isset( $post['pricing_strategy'] ) ? sanitize_text_field( $post['pricing_strategy'] ) : 'single_custom_price';
		if ( ! in_array( $strategy, array( 'catalog', 'per_product_override', 'single_custom_price' ), true ) ) {
			$strategy = 'single_custom_price';
		}

		$values = array(
			'mode'                      => isset( $post['mode'] ) && in_array( $post['mode'], array( 'live', 'sandbox' ), true ) ? $post['mode'] : 'sandbox',
			'live_username'             => isset( $post['live_username'] ) ? sanitize_text_field( $post['live_username'] ) : '',
			'live_password'             => isset( $post['live_password'] ) ? sanitize_text_field( $post['live_password'] ) : '',
			'live_storefront'           => isset( $post['live_storefront'] ) ? sanitize_text_field( $post['live_storefront'] ) : '',
			'sandbox_username'          => isset( $post['sandbox_username'] ) ? sanitize_text_field( $post['sandbox_username'] ) : '',
			'sandbox_password'          => isset( $post['sandbox_password'] ) ? sanitize_text_field( $post['sandbox_password'] ) : '',
			'sandbox_storefront'        => isset( $post['sandbox_storefront'] ) ? sanitize_text_field( $post['sandbox_storefront'] ) : '',
			'sandbox_access_key'        => isset( $post['sandbox_access_key'] ) ? sanitize_text_field( $post['sandbox_access_key'] ) : '',
			'live_access_key'           => isset( $post['live_access_key'] ) ? sanitize_text_field( $post['live_access_key'] ) : '',
			'webhook_secret_live'       => isset( $post['webhook_secret_live'] ) ? sanitize_text_field( $post['webhook_secret_live'] ) : '',
			'webhook_secret_sandbox'    => isset( $post['webhook_secret_sandbox'] ) ? sanitize_text_field( $post['webhook_secret_sandbox'] ) : '',
			'enable_webhook'            => ! empty( $post['enable_webhook'] ) ? 'yes' : 'no',
			'enable_logging'            => ! empty( $post['enable_logging'] ) ? 'yes' : 'no',
			'sync_products'             => ! empty( $post['sync_products'] ) ? 'yes' : 'no',
			'popup_path'                => isset( $post['popup_path'] ) ? trim( sanitize_text_field( $post['popup_path'] ), '/' ) : '',
			'gateway_title'             => isset( $post['gateway_title'] ) ? sanitize_text_field( $post['gateway_title'] ) : '',
			'gateway_description'       => isset( $post['gateway_description'] ) ? wp_kses_post( $post['gateway_description'] ) : '',
			'pricing_strategy'          => $strategy,
			'custom_price_product_path' => isset( $post['custom_price_product_path'] ) ? sanitize_title( $post['custom_price_product_path'] ) : '',
		);

		vms_efwp()->settings->update_all( $values );
		vms_efwp()->settings->refresh();

		$wc_gateway = get_option( 'woocommerce_vms_efwp_settings', array() );
		if ( ! is_array( $wc_gateway ) ) {
			$wc_gateway = array();
		}
		$wc_gateway['title']       = $values['gateway_title'];
		$wc_gateway['description'] = $values['gateway_description'];
		update_option( 'woocommerce_vms_efwp_settings', $wc_gateway );

		add_settings_error( 'vms_efwp', 'vms_efwp_saved', __( 'Settings saved.', 'vms-elements-fastspring-woo-payment' ), 'updated' );
		set_transient( 'settings_errors', get_settings_errors(), 30 );

		$redirect = add_query_arg( array( 'page' => 'vms-efwp-settings', 'updated' => 'true' ), admin_url( 'admin.php' ) );
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Create / verify the catch-all custom-price product in FastSpring.
	 */
	public static function handle_provision_custom_price() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'vms_efwp_provision_custom_price', 'vms_efwp_nonce' );

		$settings = vms_efwp()->settings;
		$api      = vms_efwp()->api;

		if ( ! $settings->has_credentials() ) {
			add_settings_error( 'vms_efwp', 'vms_efwp_provision', __( 'Add your FastSpring API credentials for the active mode before creating the catch-all product.', 'vms-elements-fastspring-woo-payment' ), 'error' );
		} else {
			$path   = $settings->custom_price_product_path();
			$result = $api->ensure_catch_all_product( $path, get_bloginfo( 'name' ) . ' ' . __( 'Order', 'vms-elements-fastspring-woo-payment' ) );

			if ( is_wp_error( $result ) ) {
				add_settings_error(
					'vms_efwp',
					'vms_efwp_provision',
					sprintf(
						/* translators: 1: product path, 2: error message */
						__( 'Could not create the catch-all product "%1$s": %2$s', 'vms-elements-fastspring-woo-payment' ),
						$path,
						$result->get_error_message()
					),
					'error'
				);
			} else {
				$settings->set( 'custom_price_product_path', $result );
				add_settings_error(
					'vms_efwp',
					'vms_efwp_provision',
					sprintf(
						/* translators: %s: product path */
						__( 'Catch-all product "%s" is ready in FastSpring. Any WooCommerce product can now be checked out.', 'vms-elements-fastspring-woo-payment' ),
						$result
					),
					'updated'
				);
			}
		}

		set_transient( 'settings_errors', get_settings_errors(), 30 );
		$redirect = add_query_arg( array( 'page' => 'vms-efwp-settings', 'updated' => 'true' ), admin_url( 'admin.php' ) );
		wp_safe_redirect( $redirect );
		exit;
	}
}
