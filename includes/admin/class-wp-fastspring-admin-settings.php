<?php
/**
 * Settings screen.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Settings.
 */
class WP_FastSpring_Admin_Settings {

	/**
	 * Render settings.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = wp_fastspring()->settings;
		$mode     = $settings->get_mode();

		?>
		<div class="wrap wpfs-wrap">
			<h1><?php esc_html_e( 'FastSpring Settings', 'wp-fastspring' ); ?></h1>

			<?php settings_errors( 'wp_fastspring' ); ?>

			<?php self::render_checkout_diagnostics(); ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wpfs-form">
				<?php wp_nonce_field( 'wp_fastspring_settings_save', 'wp_fastspring_nonce' ); ?>
				<input type="hidden" name="action" value="wp_fastspring_save_settings" />

				<div class="wpfs-card">
					<h2><?php esc_html_e( 'Mode', 'wp-fastspring' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Switch between your FastSpring sandbox (test) and live environment. Each mode uses its own API credentials, storefront and webhook secret below.', 'wp-fastspring' ); ?></p>
					<div class="wpfs-mode-switch">
						<label class="wpfs-mode-option <?php echo 'sandbox' === $mode ? 'is-active' : ''; ?>">
							<input type="radio" name="mode" value="sandbox" <?php checked( $mode, 'sandbox' ); ?> />
							<span class="wpfs-mode-option__title"><?php esc_html_e( 'Sandbox / Test', 'wp-fastspring' ); ?></span>
							<span class="wpfs-mode-option__desc"><?php esc_html_e( 'Use test credentials and a *.test.onfastspring.com storefront. No real charges.', 'wp-fastspring' ); ?></span>
						</label>
						<label class="wpfs-mode-option <?php echo 'live' === $mode ? 'is-active' : ''; ?>">
							<input type="radio" name="mode" value="live" <?php checked( $mode, 'live' ); ?> />
							<span class="wpfs-mode-option__title"><?php esc_html_e( 'Live / Production', 'wp-fastspring' ); ?></span>
							<span class="wpfs-mode-option__desc"><?php esc_html_e( 'Real customers, real charges, real revenue.', 'wp-fastspring' ); ?></span>
						</label>
					</div>
				</div>

				<div class="wpfs-grid wpfs-grid--two">
					<div class="wpfs-card">
						<h2><?php esc_html_e( 'Sandbox credentials', 'wp-fastspring' ); ?></h2>
						<table class="form-table">
							<tr>
								<th><label for="sandbox_storefront"><?php esc_html_e( 'Storefront', 'wp-fastspring' ); ?></label></th>
								<td><input type="text" id="sandbox_storefront" name="sandbox_storefront" class="regular-text" placeholder="yourcompany.test.onfastspring.com" value="<?php echo esc_attr( $settings->get( 'sandbox_storefront' ) ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="sandbox_username"><?php esc_html_e( 'API Username', 'wp-fastspring' ); ?></label></th>
								<td><input type="text" id="sandbox_username" name="sandbox_username" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $settings->get( 'sandbox_username' ) ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="sandbox_password"><?php esc_html_e( 'API Password', 'wp-fastspring' ); ?></label></th>
								<td><input type="password" id="sandbox_password" name="sandbox_password" class="regular-text" autocomplete="new-password" value="<?php echo esc_attr( $settings->get( 'sandbox_password' ) ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="webhook_secret_sandbox"><?php esc_html_e( 'Webhook secret', 'wp-fastspring' ); ?></label></th>
								<td>
									<div class="wpfs-secret-field">
										<input type="text" id="webhook_secret_sandbox" name="webhook_secret_sandbox" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $settings->get( 'webhook_secret_sandbox' ) ); ?>" />
										<button type="button" class="button wpfs-generate-secret" data-target="webhook_secret_sandbox"><?php esc_html_e( 'Generate', 'wp-fastspring' ); ?></button>
										<button type="button" class="button wpfs-copy-secret" data-target="webhook_secret_sandbox"><?php esc_html_e( 'Copy', 'wp-fastspring' ); ?></button>
									</div>
									<p class="description"><?php esc_html_e( 'Paste this same value into the HMAC SHA256 Secret box on FastSpring → Integrations → Webhooks.', 'wp-fastspring' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<div class="wpfs-card">
						<h2><?php esc_html_e( 'Live credentials', 'wp-fastspring' ); ?></h2>
						<table class="form-table">
							<tr>
								<th><label for="live_storefront"><?php esc_html_e( 'Storefront', 'wp-fastspring' ); ?></label></th>
								<td><input type="text" id="live_storefront" name="live_storefront" class="regular-text" placeholder="yourcompany.onfastspring.com" value="<?php echo esc_attr( $settings->get( 'live_storefront' ) ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="live_username"><?php esc_html_e( 'API Username', 'wp-fastspring' ); ?></label></th>
								<td><input type="text" id="live_username" name="live_username" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $settings->get( 'live_username' ) ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="live_password"><?php esc_html_e( 'API Password', 'wp-fastspring' ); ?></label></th>
								<td><input type="password" id="live_password" name="live_password" class="regular-text" autocomplete="new-password" value="<?php echo esc_attr( $settings->get( 'live_password' ) ); ?>" /></td>
							</tr>
							<tr>
								<th><label for="webhook_secret_live"><?php esc_html_e( 'Webhook secret', 'wp-fastspring' ); ?></label></th>
								<td>
									<div class="wpfs-secret-field">
										<input type="text" id="webhook_secret_live" name="webhook_secret_live" class="regular-text" autocomplete="off" value="<?php echo esc_attr( $settings->get( 'webhook_secret_live' ) ); ?>" />
										<button type="button" class="button wpfs-generate-secret" data-target="webhook_secret_live"><?php esc_html_e( 'Generate', 'wp-fastspring' ); ?></button>
										<button type="button" class="button wpfs-copy-secret" data-target="webhook_secret_live"><?php esc_html_e( 'Copy', 'wp-fastspring' ); ?></button>
									</div>
									<p class="description"><?php esc_html_e( 'Paste this same value into the HMAC SHA256 Secret box on FastSpring → Integrations → Webhooks (Live).', 'wp-fastspring' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="wpfs-card">
					<h2><?php esc_html_e( 'Webhook endpoint', 'wp-fastspring' ); ?></h2>
					<ol class="description" style="margin-top:0;">
						<li><?php esc_html_e( 'Open FastSpring App → Integrations → Webhooks → Add Webhook.', 'wp-fastspring' ); ?></li>
						<li><?php esc_html_e( 'Paste the URL below into the Webhook URL field.', 'wp-fastspring' ); ?></li>
						<li><?php esc_html_e( 'In the HMAC SHA256 Secret box, paste the same secret you configured for the active mode above. FastSpring does not auto-generate one — use the Generate button next to the secret field if you don\'t have a value yet.', 'wp-fastspring' ); ?></li>
						<li><?php esc_html_e( 'Tick the events you want to listen to (or "All events"), then save in FastSpring and here.', 'wp-fastspring' ); ?></li>
					</ol>
					<input type="text" readonly value="<?php echo esc_attr( $settings->webhook_url() ); ?>" class="large-text wpfs-readonly" onclick="this.select()" />
					<p>
						<label><input type="checkbox" name="enable_webhook" value="yes" <?php checked( 'yes', $settings->get( 'enable_webhook', 'yes' ) ); ?> /> <?php esc_html_e( 'Enable webhook listener', 'wp-fastspring' ); ?></label>
					</p>
				</div>

				<div class="wpfs-card">
					<h2><?php esc_html_e( 'Pricing strategy', 'wp-fastspring' ); ?></h2>
					<p class="description"><?php esc_html_e( 'Decides how WooCommerce sends prices to FastSpring at checkout. Pick the option that fits your catalog.', 'wp-fastspring' ); ?></p>

					<?php $strategy = $settings->pricing_strategy(); ?>
					<div class="wpfs-pricing-strategy">
						<label class="wpfs-mode-option <?php echo 'catalog' === $strategy ? 'is-active' : ''; ?>">
							<input type="radio" name="pricing_strategy" value="catalog" <?php checked( $strategy, 'catalog' ); ?> />
							<span class="wpfs-mode-option__title"><?php esc_html_e( 'FastSpring catalog price', 'wp-fastspring' ); ?></span>
							<span class="wpfs-mode-option__desc">
								<?php esc_html_e( 'Customer is charged the price configured in FastSpring for each product. WooCommerce price is ignored. Pick this only if FastSpring is your source of truth for pricing.', 'wp-fastspring' ); ?>
							</span>
						</label>

						<label class="wpfs-mode-option <?php echo 'per_product_override' === $strategy ? 'is-active' : ''; ?>">
							<input type="radio" name="pricing_strategy" value="per_product_override" <?php checked( $strategy, 'per_product_override' ); ?> />
							<span class="wpfs-mode-option__title"><?php esc_html_e( 'Per-product price override (recommended)', 'wp-fastspring' ); ?></span>
							<span class="wpfs-mode-option__desc">
								<?php esc_html_e( 'Each WC product maps to its own FastSpring product (synced via slug). The actual WC line price — after coupons, sales, dynamic pricing — is sent as an override. Best for stores with proper catalogs (fashion, software, books, etc).', 'wp-fastspring' ); ?>
								<br /><strong><?php esc_html_e( 'Requirement:', 'wp-fastspring' ); ?></strong>
								<?php esc_html_e( 'each FastSpring product must have "Allow Price Override" enabled.', 'wp-fastspring' ); ?>
							</span>
						</label>

						<label class="wpfs-mode-option <?php echo 'single_custom_price' === $strategy ? 'is-active' : ''; ?>">
							<input type="radio" name="pricing_strategy" value="single_custom_price" <?php checked( $strategy, 'single_custom_price' ); ?> />
							<span class="wpfs-mode-option__title"><?php esc_html_e( 'Single Custom Price product', 'wp-fastspring' ); ?></span>
							<span class="wpfs-mode-option__desc">
								<?php esc_html_e( 'All cart items are charged through one Custom Price product in FastSpring. Useful for donations, services, quotes, or stores where products do not need to be tracked individually in FastSpring analytics.', 'wp-fastspring' ); ?>
							</span>
						</label>
					</div>

					<table class="form-table">
						<tr class="wpfs-custom-price-row" <?php echo 'single_custom_price' === $strategy ? '' : 'style="display:none;"'; ?>>
							<th><label for="custom_price_product_path"><?php esc_html_e( 'Custom Price product path', 'wp-fastspring' ); ?></label></th>
							<td>
								<input type="text" id="custom_price_product_path" name="custom_price_product_path" class="regular-text" value="<?php echo esc_attr( $settings->custom_price_product_path() ); ?>" placeholder="wc-dynamic" />
								<p class="description">
									<?php esc_html_e( 'The path of the FastSpring product configured as "Custom Price". Create it in FastSpring App → Products → New Product, set Type = Custom Price, save the path, and paste it here.', 'wp-fastspring' ); ?>
								</p>
							</td>
						</tr>
					</table>
				</div>

				<div class="wpfs-card">
					<h2><?php esc_html_e( 'WooCommerce integration', 'wp-fastspring' ); ?></h2>
					<table class="form-table">
						<tr>
							<th><label for="gateway_title"><?php esc_html_e( 'Gateway title', 'wp-fastspring' ); ?></label></th>
							<td><input type="text" id="gateway_title" name="gateway_title" class="regular-text" value="<?php echo esc_attr( $settings->get( 'gateway_title' ) ); ?>" /></td>
						</tr>
						<tr>
							<th><label for="gateway_description"><?php esc_html_e( 'Gateway description', 'wp-fastspring' ); ?></label></th>
							<td><textarea id="gateway_description" name="gateway_description" class="large-text" rows="2"><?php echo esc_textarea( $settings->get( 'gateway_description' ) ); ?></textarea></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Product sync', 'wp-fastspring' ); ?></th>
							<td>
								<label><input type="checkbox" name="sync_products" value="yes" <?php checked( 'yes', $settings->get( 'sync_products', 'no' ) ); ?> /> <?php esc_html_e( 'Push WooCommerce products to FastSpring on save (one-way)', 'wp-fastspring' ); ?></label>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Logging', 'wp-fastspring' ); ?></th>
							<td>
								<label><input type="checkbox" name="enable_logging" value="yes" <?php checked( 'yes', $settings->get( 'enable_logging', 'yes' ) ); ?> /> <?php esc_html_e( 'Record info-level events to the FastSpring log table', 'wp-fastspring' ); ?></label>
							</td>
						</tr>
					</table>
				</div>

				<p>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Save settings', 'wp-fastspring' ); ?></button>
					<button type="button" class="button" id="wpfs-test-connection"><?php esc_html_e( 'Test connection', 'wp-fastspring' ); ?></button>
					<span class="wpfs-test-result" id="wpfs-test-result"></span>
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
		$settings = wp_fastspring()->settings;

		$wc_active    = class_exists( 'WooCommerce' );
		$wc_version   = defined( 'WC_VERSION' ) ? WC_VERSION : ( $wc_active && function_exists( 'WC' ) ? WC()->version : '' );
		$gateway_opt  = get_option( 'woocommerce_wp_fastspring_settings', array() );
		$gateway_on   = is_array( $gateway_opt ) && ( $gateway_opt['enabled'] ?? 'no' ) === 'yes';
		$has_creds    = $settings->has_credentials();
		$has_storefr  = '' !== $settings->storefront();

		// Detect Cart/Checkout Blocks availability via several signals.
		$blocks_pkg    = class_exists( 'Automattic\WooCommerce\Blocks\Package' );
		$blocks_abs    = class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' );
		$blocks_hooked = did_action( 'woocommerce_blocks_loaded' ) > 0;
		$blocks_class  = class_exists( 'WP_FastSpring_WC_Blocks' );
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
						$blocks_registered = in_array( 'wp_fastspring', $blocks_registry_methods, true );
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
				$blocks_fix = __( 'WooCommerce is not active.', 'wp-fastspring' );
			} elseif ( ! $blocks_pkg && ! $blocks_abs && ! $blocks_hooked ) {
				$blocks_fix = sprintf(
					/* translators: %s WC version */
					__( 'Your WooCommerce (%s) does not expose the Cart/Checkout Blocks API. Update WooCommerce to 6.9+ (8.0+ recommended) or install the "WooCommerce Blocks" plugin. The classic checkout still works.', 'wp-fastspring' ),
					$wc_version ? esc_html( $wc_version ) : esc_html__( 'unknown version', 'wp-fastspring' )
				);
			} elseif ( ! $blocks_class ) {
				$blocks_fix = __( 'Block API is available but the plugin\'s Blocks integration did not load. Try deactivating and reactivating WP FastSpring.', 'wp-fastspring' );
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
				'label' => __( 'WooCommerce active', 'wp-fastspring' ),
				'ok'    => $wc_active,
				'fix'   => __( 'Install and activate WooCommerce.', 'wp-fastspring' ),
			),
			array(
				'label' => __( 'FastSpring gateway enabled in WooCommerce', 'wp-fastspring' ),
				'ok'    => $gateway_on,
				'fix'   => sprintf(
					/* translators: %s URL */
					__( 'Open %s and toggle "Enable FastSpring".', 'wp-fastspring' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wp_fastspring' ) ) . '">WooCommerce → Settings → Payments → FastSpring</a>'
				),
			),
			array(
				'label' => __( 'API credentials configured for active mode', 'wp-fastspring' ),
				'ok'    => $has_creds,
				'fix'   => __( 'Fill in API username and password for the active mode below.', 'wp-fastspring' ),
			),
			array(
				'label' => __( 'Storefront ID configured for active mode', 'wp-fastspring' ),
				'ok'    => $has_storefr,
				'fix'   => __( 'Add your *.onfastspring.com storefront ID for the active mode below.', 'wp-fastspring' ),
			),
			array(
				'label' => __( 'Block-based Checkout integration available', 'wp-fastspring' ),
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
			$fastspring_visible = isset( $gateways_available['wp_fastspring'] );
			if ( isset( $gateways_all['wp_fastspring'] ) && method_exists( $gateways_all['wp_fastspring'], 'get_availability_issues' ) ) {
				$fs_issues = $gateways_all['wp_fastspring']->get_availability_issues();
			}
		}

		$issue_messages = array(
			'gateway_disabled'    => __( 'The FastSpring toggle is OFF in WooCommerce → Settings → Payments → FastSpring.', 'wp-fastspring' ),
			'plugin_not_loaded'   => __( 'The WP FastSpring plugin failed to initialise. Try deactivating and reactivating it.', 'wp-fastspring' ),
			'missing_credentials' => __( 'API username or password is empty for the active mode.', 'wp-fastspring' ),
			'missing_storefront'  => __( 'Storefront ID is empty for the active mode.', 'wp-fastspring' ),
		);
		?>
		<div class="wpfs-card">
			<h2><?php esc_html_e( 'Checkout availability', 'wp-fastspring' ); ?>
				<?php if ( $all_ok ) : ?>
					<span class="wpfs-badge wpfs-badge--ok"><?php esc_html_e( 'READY', 'wp-fastspring' ); ?></span>
				<?php else : ?>
					<span class="wpfs-badge wpfs-badge--warning"><?php esc_html_e( 'NEEDS ATTENTION', 'wp-fastspring' ); ?></span>
				<?php endif; ?>
			</h2>
			<p class="description">
				<?php
				if ( 'blocks' === $checkout_uses_blocks ) {
					esc_html_e( 'Your Checkout page uses the WooCommerce Checkout block. The gateway needs the Blocks integration row below to be green for customers to see FastSpring.', 'wp-fastspring' );
				} elseif ( 'classic' === $checkout_uses_blocks ) {
					esc_html_e( 'Your Checkout page uses the classic [woocommerce_checkout] shortcode. The Blocks row can be ignored — the gateway will show as long as the first four rows are green.', 'wp-fastspring' );
				} else {
					esc_html_e( 'If FastSpring isn\'t appearing on the checkout page, every required row below should be green.', 'wp-fastspring' );
				}
				?>
			</p>
			<table class="widefat striped wpfs-table">
				<tbody>
				<?php foreach ( $checks as $c ) : ?>
					<tr>
						<td style="width:60px;">
							<?php if ( $c['ok'] ) : ?>
								<span class="wpfs-badge wpfs-badge--ok">OK</span>
							<?php elseif ( ! empty( $c['warn'] ) ) : ?>
								<span class="wpfs-badge wpfs-badge--info">INFO</span>
							<?php else : ?>
								<span class="wpfs-badge wpfs-badge--warning">!</span>
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
						wp_kses_post( __( 'Checkout page detected: <a href="%1$s">edit page #%2$d</a>. Switching between the classic shortcode and the Checkout block here is the fastest way to verify the gateway appears.', 'wp-fastspring' ) ),
						esc_url( get_edit_post_link( $checkout_page_id ) ),
						(int) $checkout_page_id
					);
					?>
				</p>
			<?php endif; ?>

			<?php if ( $wc_active && null !== $blocks_registered ) : ?>
				<hr style="margin:18px 0;" />
				<h3 style="margin:0 0 8px;"><?php esc_html_e( 'Blocks Checkout registration', 'wp-fastspring' ); ?></h3>
				<?php if ( $blocks_registered ) : ?>
					<div class="notice notice-success inline"><p>
						<strong><?php esc_html_e( 'FastSpring IS registered with the Cart/Checkout Blocks Payment Method Registry.', 'wp-fastspring' ); ?></strong>
						<?php esc_html_e( 'It will show on the block-based checkout. If it still doesn\'t appear, force-refresh the page (Ctrl+F5) and clear any caching plugin.', 'wp-fastspring' ); ?>
					</p></div>
				<?php else : ?>
					<div class="notice notice-error inline"><p>
						<strong><?php esc_html_e( 'FastSpring is NOT registered with the Blocks Payment Method Registry.', 'wp-fastspring' ); ?></strong>
						<?php esc_html_e( 'This is why it does not appear on a block-based checkout. Deactivate and reactivate WP FastSpring once to trigger registration, then revisit this page.', 'wp-fastspring' ); ?>
					</p></div>
				<?php endif; ?>
				<details class="wpfs-details">
					<summary><?php esc_html_e( 'Show all Blocks-registered payment methods', 'wp-fastspring' ); ?></summary>
					<ul class="wpfs-gateway-list">
						<?php if ( empty( $blocks_registry_methods ) ) : ?>
							<li><em><?php esc_html_e( 'None — Blocks payment registry is empty.', 'wp-fastspring' ); ?></em></li>
						<?php else : ?>
							<?php foreach ( $blocks_registry_methods as $name ) : ?>
								<li>
									<span class="wpfs-badge wpfs-badge--<?php echo 'wp_fastspring' === $name ? 'ok' : 'info'; ?>"><?php echo 'wp_fastspring' === $name ? 'OURS' : 'BLOCK'; ?></span>
									<code><?php echo esc_html( $name ); ?></code>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</details>
			<?php endif; ?>

			<?php if ( $wc_active ) : ?>
				<hr style="margin:18px 0;" />
				<h3 style="margin:0 0 8px;"><?php esc_html_e( 'Live availability test', 'wp-fastspring' ); ?></h3>
				<p class="description" style="margin-top:0;">
					<?php esc_html_e( 'This runs WooCommerce\'s actual checkout pipeline right now. If FastSpring appears in the green list below, it will appear at checkout for shoppers in the same situation.', 'wp-fastspring' ); ?>
				</p>

				<?php if ( null === $fastspring_visible ) : ?>
					<div class="notice notice-warning inline"><p><?php esc_html_e( 'Could not query WooCommerce gateways from this admin page.', 'wp-fastspring' ); ?></p></div>
				<?php elseif ( $fastspring_visible ) : ?>
					<div class="notice notice-success inline"><p>
						<strong><?php esc_html_e( 'FastSpring is currently available at checkout.', 'wp-fastspring' ); ?></strong>
						<?php esc_html_e( 'If shoppers still don\'t see it, hard-refresh the page or clear caches.', 'wp-fastspring' ); ?>
					</p></div>
				<?php else : ?>
					<div class="notice notice-error inline"><p>
						<strong><?php esc_html_e( 'FastSpring is currently HIDDEN at checkout.', 'wp-fastspring' ); ?></strong>
						<?php if ( $fs_issues ) : ?>
							<br />
							<?php foreach ( $fs_issues as $code ) : ?>
								&bull; <?php echo esc_html( isset( $issue_messages[ $code ] ) ? $issue_messages[ $code ] : $code ); ?><br />
							<?php endforeach; ?>
						<?php else : ?>
							<br />
							<?php esc_html_e( 'The gateway passes all FastSpring-specific checks, so it is most likely being filtered out by your theme, another plugin, or by a cart-level rule (zero total, country/currency restriction, etc).', 'wp-fastspring' ); ?>
						<?php endif; ?>
					</p></div>
				<?php endif; ?>

				<details class="wpfs-details">
					<summary><?php esc_html_e( 'Show all currently-available gateways', 'wp-fastspring' ); ?></summary>
					<ul class="wpfs-gateway-list">
						<?php if ( empty( $gateways_available ) ) : ?>
							<li><em><?php esc_html_e( 'No gateways are available right now.', 'wp-fastspring' ); ?></em></li>
						<?php else : ?>
							<?php foreach ( $gateways_available as $id => $gw ) : ?>
								<li>
									<span class="wpfs-badge wpfs-badge--ok">SHOWN</span>
									<code><?php echo esc_html( $id ); ?></code> — <?php echo esc_html( $gw->get_title() ); ?>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				</details>

				<details class="wpfs-details">
					<summary><?php esc_html_e( 'Show all registered gateways (including hidden)', 'wp-fastspring' ); ?></summary>
					<ul class="wpfs-gateway-list">
						<?php foreach ( $gateways_all as $id => $gw ) : ?>
							<?php $shown = isset( $gateways_available[ $id ] ); ?>
							<li>
								<span class="wpfs-badge wpfs-badge--<?php echo $shown ? 'ok' : 'warning'; ?>"><?php echo $shown ? 'SHOWN' : 'HIDDEN'; ?></span>
								<code><?php echo esc_html( $id ); ?></code> — <?php echo esc_html( $gw->get_method_title() ); ?>
								<?php if ( $gw->enabled !== 'yes' ) : ?>
									<span class="description"><?php esc_html_e( '(disabled)', 'wp-fastspring' ); ?></span>
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
			wp_die( esc_html__( 'Unauthorized.', 'wp-fastspring' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'wp_fastspring_settings_save', 'wp_fastspring_nonce' );

		$post = wp_unslash( $_POST );

		$strategy = isset( $post['pricing_strategy'] ) ? sanitize_text_field( $post['pricing_strategy'] ) : 'per_product_override';
		if ( ! in_array( $strategy, array( 'catalog', 'per_product_override', 'single_custom_price' ), true ) ) {
			$strategy = 'per_product_override';
		}

		$values = array(
			'mode'                      => isset( $post['mode'] ) && in_array( $post['mode'], array( 'live', 'sandbox' ), true ) ? $post['mode'] : 'sandbox',
			'live_username'             => isset( $post['live_username'] ) ? sanitize_text_field( $post['live_username'] ) : '',
			'live_password'             => isset( $post['live_password'] ) ? sanitize_text_field( $post['live_password'] ) : '',
			'live_storefront'           => isset( $post['live_storefront'] ) ? sanitize_text_field( $post['live_storefront'] ) : '',
			'sandbox_username'          => isset( $post['sandbox_username'] ) ? sanitize_text_field( $post['sandbox_username'] ) : '',
			'sandbox_password'          => isset( $post['sandbox_password'] ) ? sanitize_text_field( $post['sandbox_password'] ) : '',
			'sandbox_storefront'        => isset( $post['sandbox_storefront'] ) ? sanitize_text_field( $post['sandbox_storefront'] ) : '',
			'webhook_secret_live'       => isset( $post['webhook_secret_live'] ) ? sanitize_text_field( $post['webhook_secret_live'] ) : '',
			'webhook_secret_sandbox'    => isset( $post['webhook_secret_sandbox'] ) ? sanitize_text_field( $post['webhook_secret_sandbox'] ) : '',
			'enable_webhook'            => ! empty( $post['enable_webhook'] ) ? 'yes' : 'no',
			'enable_logging'            => ! empty( $post['enable_logging'] ) ? 'yes' : 'no',
			'sync_products'             => ! empty( $post['sync_products'] ) ? 'yes' : 'no',
			'gateway_title'             => isset( $post['gateway_title'] ) ? sanitize_text_field( $post['gateway_title'] ) : '',
			'gateway_description'       => isset( $post['gateway_description'] ) ? wp_kses_post( $post['gateway_description'] ) : '',
			'pricing_strategy'          => $strategy,
			'custom_price_product_path' => isset( $post['custom_price_product_path'] ) ? sanitize_title( $post['custom_price_product_path'] ) : '',
		);

		wp_fastspring()->settings->update_all( $values );
		wp_fastspring()->settings->refresh();

		add_settings_error( 'wp_fastspring', 'wp_fastspring_saved', __( 'Settings saved.', 'wp-fastspring' ), 'updated' );
		set_transient( 'settings_errors', get_settings_errors(), 30 );

		$redirect = add_query_arg( array( 'page' => 'wp-fastspring-settings', 'updated' => 'true' ), admin_url( 'admin.php' ) );
		wp_safe_redirect( $redirect );
		exit;
	}
}
