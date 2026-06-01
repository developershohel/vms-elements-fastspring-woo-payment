<?php
/**
 * Products screen.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin_Products.
 */
class WP_FastSpring_Admin_Products {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="wrap wpfs-wrap">';
		WP_FastSpring_Admin_Resource_Base::render_header(
			__( 'Products', 'wp-fastspring' ),
			__( 'One-time products in your FastSpring catalog. Subscription products live under FastSpring → Subscriptions → Subscription Products.', 'wp-fastspring' ),
			array( '<button type="button" class="button button-primary" id="wpfs-new-product" data-wpfs-open-form="save-product">' . esc_html__( 'New product', 'wp-fastspring' ) . '</button>' )
		);

		if ( ! WP_FastSpring_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		// Handle delete.
		if ( isset( $_GET['delete'] ) && check_admin_referer( 'wpfs_delete_product' ) ) {
			$path   = sanitize_text_field( wp_unslash( $_GET['delete'] ) );
			$result = wp_fastspring()->api->delete_product( $path );
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
		}

		// Handle create / update (upsert).
		if ( ! empty( $_POST['wpfs_save_product'] ) && WP_FastSpring_Admin_Resource_Base::verify_post( 'wpfs_save_product' ) ) {
			$path     = sanitize_title( wp_unslash( $_POST['product_path'] ?? '' ) );
			$name     = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
			$summary  = sanitize_textarea_field( wp_unslash( $_POST['summary'] ?? '' ) );
			$full     = sanitize_textarea_field( wp_unslash( $_POST['full_description'] ?? '' ) );
			$sku      = sanitize_text_field( wp_unslash( $_POST['sku'] ?? $path ) );
			$image    = esc_url_raw( wp_unslash( $_POST['image'] ?? '' ) );
			$format   = sanitize_text_field( wp_unslash( $_POST['format'] ?? 'digital' ) );
			$pricing  = self::parse_pricing( wp_unslash( $_POST['pricing'] ?? array() ) );

			$payload = array(
				'product'     => $path,
				'sku'         => $sku,
				'format'      => $format,
				'display'     => array( 'en' => $name ),
				'description' => array(
					'summary' => array( 'en' => $summary ),
					'full'    => array( 'en' => $full ),
				),
				'pricing'     => array( 'price' => $pricing ),
			);
			if ( $image ) {
				$payload['image'] = $image;
			}
			$result = wp_fastspring()->api->upsert_product( $payload );
			if ( is_wp_error( $result ) ) {
				WP_FastSpring_Admin_Resource_Base::render_result_notice( $result );
			} else {
				printf(
					'<div class="notice notice-success"><p>%s <code>%s</code></p></div>',
					esc_html__( 'Product saved:', 'wp-fastspring' ),
					esc_html( $path )
				);
			}
		}

		// Pull list and full details for the table.
		$list  = wp_fastspring()->api->list_products();
		$paths = array();
		if ( is_wp_error( $list ) ) {
			WP_FastSpring_Admin_Resource_Base::render_result_notice( $list );
		} else {
			$paths = isset( $list['products'] ) ? $list['products'] : ( isset( $list[0] ) ? $list : array() );
		}

		$products = array();
		if ( ! empty( $paths ) ) {
			$details = wp_fastspring()->api->get_products( array_slice( $paths, 0, 50 ) );
			if ( ! is_wp_error( $details ) ) {
				$products = isset( $details['products'] ) ? $details['products'] : array();
			}
		}

		// Hide subscription/recurring products on this screen — they belong on
		// the Subscriptions → Subscription Products tab.
		$products = array_values(
			array_filter(
				$products,
				static function ( $p ) {
					return ! self::is_subscription_product( $p );
				}
			)
		);

		self::render_form();
		self::render_table( $products );
		WP_FastSpring_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}

	/**
	 * Render the create / edit form.
	 */
	private static function render_form() {
		?>
		<div class="wpfs-card" data-wpfs-form="save-product" hidden>
			<div class="wpfs-card__head">
				<h2 id="wpfs-product-form-title"><?php esc_html_e( 'Create product', 'wp-fastspring' ); ?></h2>
				<button type="button" class="button button-link" data-wpfs-close-form="save-product"><?php esc_html_e( 'Close', 'wp-fastspring' ); ?></button>
			</div>
			<form method="post" id="wpfs-product-form">
				<?php wp_nonce_field( 'wpfs_save_product' ); ?>
				<input type="hidden" name="wpfs_save_product" value="1" />
				<div class="wpfs-grid wpfs-grid--two">
					<p>
						<label><?php esc_html_e( 'Product path (slug)', 'wp-fastspring' ); ?>
							<span class="wpfs-help" title="<?php esc_attr_e( 'Identifier in FastSpring. Re-using an existing path updates that product.', 'wp-fastspring' ); ?>">?</span>
							<br />
							<input type="text" name="product_path" required class="regular-text" placeholder="my-app-pro" data-wpfs-field="product" />
						</label>
					</p>
					<p>
						<label><?php esc_html_e( 'Display name (English)', 'wp-fastspring' ); ?>
							<br /><input type="text" name="display_name" required class="regular-text" data-wpfs-field="display" />
						</label>
					</p>
					<p>
						<label><?php esc_html_e( 'SKU', 'wp-fastspring' ); ?>
							<br /><input type="text" name="sku" class="regular-text" data-wpfs-field="sku" />
						</label>
					</p>
					<p>
						<label><?php esc_html_e( 'Format', 'wp-fastspring' ); ?>
							<br />
							<select name="format" data-wpfs-field="format">
								<option value="digital"><?php esc_html_e( 'Digital', 'wp-fastspring' ); ?></option>
								<option value="physical"><?php esc_html_e( 'Physical', 'wp-fastspring' ); ?></option>
								<option value="service"><?php esc_html_e( 'Service', 'wp-fastspring' ); ?></option>
							</select>
						</label>
					</p>
					<p class="wpfs-grid--full">
						<label><?php esc_html_e( 'Image URL', 'wp-fastspring' ); ?>
							<br /><input type="url" name="image" class="regular-text" data-wpfs-field="image" placeholder="https://..." />
						</label>
					</p>
					<p class="wpfs-grid--full">
						<label><?php esc_html_e( 'Short summary', 'wp-fastspring' ); ?>
							<br /><textarea name="summary" rows="2" class="regular-text" data-wpfs-field="summary"></textarea>
						</label>
					</p>
					<p class="wpfs-grid--full">
						<label><?php esc_html_e( 'Full description', 'wp-fastspring' ); ?>
							<br /><textarea name="full_description" rows="3" class="regular-text" data-wpfs-field="full"></textarea>
						</label>
					</p>
				</div>

				<h3 style="margin-top:8px;"><?php esc_html_e( 'Pricing', 'wp-fastspring' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Add one or more currencies. Use ISO 4217 codes (USD, EUR, GBP, ...).', 'wp-fastspring' ); ?></p>
				<table class="widefat wpfs-pricing">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Currency', 'wp-fastspring' ); ?></th>
							<th><?php esc_html_e( 'Price', 'wp-fastspring' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody id="wpfs-pricing-rows">
						<tr>
							<td><input type="text" name="pricing[currency][]" maxlength="3" value="USD" /></td>
							<td><input type="number" step="0.01" name="pricing[price][]" required value="0" /></td>
							<td><button type="button" class="button button-small wpfs-pricing-remove">&times;</button></td>
						</tr>
					</tbody>
				</table>
				<p>
					<button type="button" class="button" id="wpfs-pricing-add"><?php esc_html_e( 'Add currency', 'wp-fastspring' ); ?></button>
				</p>

				<p>
					<button class="button button-primary" id="wpfs-product-submit"><?php esc_html_e( 'Create product', 'wp-fastspring' ); ?></button>
					<button type="button" class="button" data-wpfs-close-form="save-product"><?php esc_html_e( 'Cancel', 'wp-fastspring' ); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the products table.
	 *
	 * @param array $products Products list.
	 */
	private static function render_table( $products ) {
		?>
		<table class="widefat striped wpfs-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Path', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Display', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'SKU', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Price', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Format', 'wp-fastspring' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'wp-fastspring' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $products ) ) : ?>
					<?php WP_FastSpring_Admin_Resource_Base::render_empty_row( __( 'No products in your FastSpring catalog yet.', 'wp-fastspring' ), 6 ); ?>
				<?php else : ?>
					<?php foreach ( $products as $p ) : ?>
						<?php
						$path          = $p['product'] ?? '';
						$display       = $p['display'] ?? array();
						$display_first = is_array( $display ) ? reset( $display ) : (string) $display;
						$pricing       = $p['pricing']['price'] ?? array();
						$delete_url    = wp_nonce_url(
							add_query_arg( array( 'page' => 'wp-fastspring-products', 'delete' => $path ), admin_url( 'admin.php' ) ),
							'wpfs_delete_product'
						);
						$row_json = wp_json_encode( $p, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
						?>
						<tr>
							<td><code><?php echo esc_html( $path ); ?></code></td>
							<td><?php echo esc_html( $display_first ); ?></td>
							<td><?php echo esc_html( $p['sku'] ?? '' ); ?></td>
							<td>
								<?php
								$out = array();
								foreach ( (array) $pricing as $cur => $val ) {
									$out[] = $cur . ' ' . number_format_i18n( (float) $val, 2 );
								}
								echo esc_html( implode( ', ', $out ) );
								?>
							</td>
							<td><?php echo esc_html( $p['format'] ?? 'digital' ); ?></td>
							<td>
								<button type="button" class="button button-small wpfs-edit-product" data-product="<?php echo esc_attr( $row_json ); ?>"><?php esc_html_e( 'Edit', 'wp-fastspring' ); ?></button>
								<?php WP_FastSpring_Admin_Resource_Base::render_view_button( $p ); ?>
								<a class="button button-small" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Delete product?', 'wp-fastspring' ); ?>');"><?php esc_html_e( 'Delete', 'wp-fastspring' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Detect whether a FastSpring product payload represents a subscription.
	 *
	 * @param array $product Product payload.
	 * @return bool
	 */
	public static function is_subscription_product( $product ) {
		if ( ! is_array( $product ) ) {
			return false;
		}
		if ( ! empty( $product['offers'] ) && is_array( $product['offers'] ) ) {
			foreach ( $product['offers'] as $offer ) {
				if ( isset( $offer['type'] ) && 'subscription' === $offer['type'] ) {
					return true;
				}
			}
		}
		$pricing = isset( $product['pricing'] ) ? $product['pricing'] : array();
		if ( ! empty( $pricing['interval'] ) || ! empty( $pricing['intervalLength'] ) || ! empty( $pricing['intervalUnit'] ) ) {
			return true;
		}
		if ( isset( $product['format'] ) && 'subscription' === $product['format'] ) {
			return true;
		}
		return false;
	}

	/**
	 * Parse pricing from POST array structure: pricing[currency][], pricing[price][].
	 *
	 * @param array $raw POST input.
	 * @return array Map of currency => price.
	 */
	private static function parse_pricing( $raw ) {
		$out = array();
		if ( ! is_array( $raw ) ) {
			return $out;
		}
		$currencies = isset( $raw['currency'] ) ? (array) $raw['currency'] : array();
		$prices     = isset( $raw['price'] ) ? (array) $raw['price'] : array();
		foreach ( $currencies as $i => $cur ) {
			$cur = strtoupper( substr( preg_replace( '/[^A-Za-z]/', '', (string) $cur ), 0, 3 ) );
			if ( ! $cur ) {
				continue;
			}
			$out[ $cur ] = isset( $prices[ $i ] ) ? (float) $prices[ $i ] : 0.0;
		}
		return $out;
	}
}
