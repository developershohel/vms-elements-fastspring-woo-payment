<?php
/**
 * Products screen.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin_Products.
 */
class VMS_EFWP_Admin_Products {

	/**
	 * Render screen.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab  = VMS_EFWP_Admin_Resource_Base::get_filter_key( 'tab', 'catalog' );
		$tabs = array(
			'catalog' => __( 'Catalog', 'vms-elements-fastspring-woo-payment' ),
			'lookup'  => __( 'Lookup', 'vms-elements-fastspring-woo-payment' ),
			'prices'  => __( 'Prices', 'vms-elements-fastspring-woo-payment' ),
			'offers'  => __( 'Offers', 'vms-elements-fastspring-woo-payment' ),
		);
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'catalog';
		}
		$base = admin_url( 'admin.php?page=vms-efwp-products' );

		echo '<div class="wrap vefwp-wrap">';
		VMS_EFWP_Admin_Resource_Base::render_header(
			__( 'Products', 'vms-elements-fastspring-woo-payment' ),
			__( 'One-time products in your FastSpring catalog. Subscription products live under FastSpring → Subscriptions → Subscription Products.', 'vms-elements-fastspring-woo-payment' ),
			'catalog' === $tab
				? array( '<button type="button" class="button button-primary" id="vefwp-new-product" data-vefwp-open-form="save-product">' . esc_html__( 'New product', 'vms-elements-fastspring-woo-payment' ) . '</button>' )
				: array()
		);

		if ( ! VMS_EFWP_Admin_Resource_Base::require_credentials() ) {
			echo '</div>';
			return;
		}

		$api = vms_efwp()->api;

		if ( 'catalog' === $tab ) {
			$delete_path = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'delete' );
			if ( $delete_path ) {
				check_admin_referer( 'wpfs_delete_product' );
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $api->delete_product( $delete_path ) );
			}

			if ( VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_save_product' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_save_product' ) ) {
				$payload = self::build_product_payload_from_post();
				if ( is_wp_error( $payload ) ) {
					VMS_EFWP_Admin_Resource_Base::render_result_notice( $payload );
				} else {
					VMS_EFWP_Admin_Resource_Base::render_result_notice( $api->upsert_product( $payload ) );
				}
			}
		}

		if ( 'offers' === $tab && VMS_EFWP_Admin_Resource_Base::is_post_submit( 'wpfs_save_product_offer' ) && VMS_EFWP_Admin_Resource_Base::verify_post( 'wpfs_save_product_offer' ) ) {
			self::handle_save_offer( $api );
		}

		VMS_EFWP_Admin_Resource_Base::render_nav_tabs( $tabs, $tab, $base );

		if ( 'lookup' === $tab ) {
			self::render_lookup( $api );
		} elseif ( 'prices' === $tab ) {
			self::render_prices( $api );
		} elseif ( 'offers' === $tab ) {
			self::render_offers( $api );
		} else {
			self::render_catalog( $api );
			self::render_form();
		}

		VMS_EFWP_Admin_Resource_Base::render_json_modal();
		echo '</div>';
	}

	/**
	 * Build a FastSpring product payload from the admin form.
	 *
	 * @return array|WP_Error
	 */
	private static function build_product_payload_from_post() {
		$name = VMS_EFWP_Admin_Resource_Base::post_text( 'display_name' );
		$path = VMS_EFWP_Admin_Resource_Base::sanitize_product_path(
			VMS_EFWP_Admin_Resource_Base::post_text( 'product_path' ),
			$name
		);
		if ( ! $path ) {
			return new WP_Error( 'vms_efwp_invalid_product', __( 'A product path is required.', 'vms-elements-fastspring-woo-payment' ) );
		}
		$summary   = VMS_EFWP_Admin_Resource_Base::post_textarea( 'summary' );
		$action    = VMS_EFWP_Admin_Resource_Base::post_textarea( 'action_text' );
		$full      = VMS_EFWP_Admin_Resource_Base::post_textarea( 'full_description' );
		$sku       = VMS_EFWP_Admin_Resource_Base::post_text( 'sku', $path );
		$image     = esc_url_raw( VMS_EFWP_Admin_Resource_Base::post_text( 'image' ) );
		$format = VMS_EFWP_Admin_Resource_Base::post_text( 'format', 'digital' );
		if ( ! in_array( $format, array( 'digital', 'physical', 'digital-and-physical' ), true ) ) {
			$format = 'digital';
		}
		$badge     = VMS_EFWP_Admin_Resource_Base::post_text( 'badge' );
		$rank      = VMS_EFWP_Admin_Resource_Base::post_int( 'rank', 0 );
		$instructions = VMS_EFWP_Admin_Resource_Base::post_textarea( 'fulfillment_instructions' );
		$pricing   = self::parse_pricing( VMS_EFWP_Admin_Resource_Base::post_array( 'pricing' ) );

		$description = array();
		if ( $summary ) {
			$description['summary'] = array( 'en' => $summary );
		}
		if ( $action ) {
			$description['action'] = array( 'en' => $action );
		}
		if ( $full ) {
			$description['full'] = array( 'en' => $full );
		}

		$payload = array(
			'product'     => $path,
			'sku'         => $sku,
			'format'      => $format,
			'display'     => array( 'en' => $name ),
			'description' => $description,
			'pricing'     => array( 'price' => $pricing ),
		);

		if ( $image ) {
			$payload['image'] = $image;
		}
		if ( $badge ) {
			$payload['badge'] = array( 'en' => $badge );
		}
		if ( $rank > 0 ) {
			$payload['rank'] = $rank;
		}
		if ( $instructions ) {
			$payload['fulfillment'] = array(
				'instructions' => array( 'en' => $instructions ),
			);
		}

		return $payload;
	}

	/**
	 * Catalog tab.
	 *
	 * @param VMS_EFWP_API $api API client.
	 */
	private static function render_catalog( $api ) {
		$page     = max( 1, VMS_EFWP_Admin_Resource_Base::get_filter_int( 'paged', 1 ) );
		$per_page = 50;
		$list     = $api->list_products();

		$paths    = array();
		$has_next = false;
		$total    = 0;

		if ( is_wp_error( $list ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $list );
		} else {
			$all_paths = $api->extract_product_paths( $list );
			$paged     = VMS_EFWP_API::paginate_items( $all_paths, $page, $per_page );
			$paths     = $paged['items'];
			$has_next  = $paged['has_next'];
			$total     = $paged['total'];
		}

		$products = array();
		if ( ! empty( $paths ) ) {
			$details = $api->get_products( $paths );
			if ( is_wp_error( $details ) ) {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $details );
			} else {
				$products = $api->parse_products( $details );
			}
		}

		$products = array_values(
			array_filter(
				$products,
				static function ( $p ) {
					return ! self::is_subscription_product( $p );
				}
			)
		);

		self::render_table( $products );

		if ( $page > 1 || $has_next ) {
			echo '<div class="tablenav bottom"><div class="tablenav-pages">';
			if ( $page > 1 ) {
				printf(
					'<a class="button" href="%s">&larr; %s</a> ',
					esc_url( add_query_arg( array( 'page' => 'vms-efwp-products', 'tab' => 'catalog', 'paged' => $page - 1 ), admin_url( 'admin.php' ) ) ),
					esc_html__( 'Previous', 'vms-elements-fastspring-woo-payment' )
				);
			}
			printf(
				'<span class="displaying-num">%s</span> ',
				esc_html(
					$total
						? sprintf(
							/* translators: 1: page number, 2: total products */
							__( 'Page %1$d (%2$s products)', 'vms-elements-fastspring-woo-payment' ),
							$page,
							number_format_i18n( $total )
						)
						: sprintf( __( 'Page %d', 'vms-elements-fastspring-woo-payment' ), $page )
				)
			);
			if ( $has_next ) {
				printf(
					'<a class="button" href="%s">%s &rarr;</a>',
					esc_url( add_query_arg( array( 'page' => 'vms-efwp-products', 'tab' => 'catalog', 'paged' => $page + 1 ), admin_url( 'admin.php' ) ) ),
					esc_html__( 'Next', 'vms-elements-fastspring-woo-payment' )
				);
			}
			echo '</div></div>';
		}
	}

	/**
	 * Lookup tab.
	 *
	 * @param VMS_EFWP_API $api API client.
	 */
	private static function render_lookup( $api ) {
		$path = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'product_path' );
		VMS_EFWP_Admin_Resource_Base::render_lookup_form(
			'vms-efwp-products',
			'product_path',
			__( 'Product path', 'vms-elements-fastspring-woo-payment' ),
			__( 'e.g. my-app-pro', 'vms-elements-fastspring-woo-payment' ),
			$path,
			array( 'tab' => 'lookup' )
		);
		if ( ! $path ) {
			return;
		}
		$product = $api->parse_product( $api->get_product( $path ) );
		if ( is_wp_error( $product ) ) {
			VMS_EFWP_Admin_Resource_Base::render_result_notice( $product );
			return;
		}
		VMS_EFWP_Admin_Resource_Base::render_api_detail_card( $product, __( 'Product details', 'vms-elements-fastspring-woo-payment' ) );
	}

	/**
	 * Prices tab.
	 *
	 * @param VMS_EFWP_API $api API client.
	 */
	private static function render_prices( $api ) {
		$path     = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'price_lookup' );
		$country  = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'country', 'US' );
		$currency = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'currency' );
		$page     = max( 1, VMS_EFWP_Admin_Resource_Base::get_filter_int( 'paged', 1 ) );
		?>
		<div class="vefwp-card vefwp-card--wide">
			<h2><?php esc_html_e( 'Single product price', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<form method="get" class="vefwp-filters">
				<input type="hidden" name="page" value="vms-efwp-products" />
				<input type="hidden" name="tab" value="prices" />
				<label><?php esc_html_e( 'Product path', 'vms-elements-fastspring-woo-payment' ); ?> <input type="text" name="price_lookup" value="<?php echo esc_attr( $path ); ?>" class="regular-text" /></label>
				<label><?php esc_html_e( 'Country', 'vms-elements-fastspring-woo-payment' ); ?> <input type="text" name="country" maxlength="2" value="<?php echo esc_attr( $country ); ?>" class="regular-text" /></label>
				<label><?php esc_html_e( 'Currency', 'vms-elements-fastspring-woo-payment' ); ?> <input type="text" name="currency" maxlength="3" value="<?php echo esc_attr( $currency ); ?>" class="regular-text" placeholder="USD" /></label>
				<button class="button button-primary"><?php esc_html_e( 'Get price', 'vms-elements-fastspring-woo-payment' ); ?></button>
			</form>
			<?php
			if ( $path ) {
				$params = array_filter(
					array(
						'country'  => strtoupper( $country ),
						'currency' => strtoupper( $currency ),
					)
				);
				VMS_EFWP_Admin_Resource_Base::render_api_detail_card(
					$api->get_product_price( $path, $params ),
					sprintf(
						/* translators: %s: product path */
						__( 'Price for %s', 'vms-elements-fastspring-woo-payment' ),
						$path
					)
				);
			}
			?>
		</div>

		<div class="vefwp-card vefwp-card--wide">
			<h2><?php esc_html_e( 'All product prices', 'vms-elements-fastspring-woo-payment' ); ?></h2>
			<form method="get" class="vefwp-filters">
				<input type="hidden" name="page" value="vms-efwp-products" />
				<input type="hidden" name="tab" value="prices" />
				<?php if ( $path ) : ?>
					<input type="hidden" name="price_lookup" value="<?php echo esc_attr( $path ); ?>" />
				<?php endif; ?>
				<label><?php esc_html_e( 'Country', 'vms-elements-fastspring-woo-payment' ); ?> <input type="text" name="country" maxlength="2" value="<?php echo esc_attr( $country ); ?>" class="regular-text" /></label>
				<label><?php esc_html_e( 'Currency', 'vms-elements-fastspring-woo-payment' ); ?> <input type="text" name="currency" maxlength="3" value="<?php echo esc_attr( $currency ); ?>" class="regular-text" /></label>
				<button class="button"><?php esc_html_e( 'List prices', 'vms-elements-fastspring-woo-payment' ); ?></button>
			</form>
			<?php
			$list_params = array_filter(
				array(
					'country'  => strtoupper( $country ),
					'currency' => strtoupper( $currency ),
					'page'     => $page,
					'limit'    => 50,
				)
			);
			$price_list  = $api->list_product_prices( $list_params );
			if ( is_wp_error( $price_list ) ) {
				VMS_EFWP_Admin_Resource_Base::render_result_notice( $price_list );
			} else {
				VMS_EFWP_Admin_Resource_Base::render_api_detail_card( $price_list, __( 'Localized prices', 'vms-elements-fastspring-woo-payment' ) );
				if ( ! empty( $price_list['nextPage'] ) ) {
					printf(
						'<p><a class="button" href="%s">%s &rarr;</a></p>',
						esc_url(
							add_query_arg(
								array(
									'page'         => 'vms-efwp-products',
									'tab'          => 'prices',
									'paged'        => $page + 1,
									'country'      => $country,
									'currency'     => $currency,
									'price_lookup' => $path,
								),
								admin_url( 'admin.php' )
							)
						),
						esc_html__( 'Next page', 'vms-elements-fastspring-woo-payment' )
					);
				}
			}
			?>
		</div>
		<?php
	}

	/**
	 * Offers tab.
	 *
	 * @param VMS_EFWP_API $api API client.
	 */
	private static function render_offers( $api ) {
		$path       = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'offer_product' );
		$offer_type = VMS_EFWP_Admin_Resource_Base::get_filter_text( 'offer_type' );
		?>
		<form method="get" class="vefwp-filters">
			<input type="hidden" name="page" value="vms-efwp-products" />
			<input type="hidden" name="tab" value="offers" />
			<label><?php esc_html_e( 'Product path', 'vms-elements-fastspring-woo-payment' ); ?> <input type="text" name="offer_product" value="<?php echo esc_attr( $path ); ?>" class="regular-text" required /></label>
			<select name="offer_type">
				<option value=""><?php esc_html_e( 'All offer types', 'vms-elements-fastspring-woo-payment' ); ?></option>
				<?php foreach ( self::offer_types() as $type ) : ?>
					<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $offer_type, $type ); ?>><?php echo esc_html( $type ); ?></option>
				<?php endforeach; ?>
			</select>
			<button class="button button-primary"><?php esc_html_e( 'Load offers', 'vms-elements-fastspring-woo-payment' ); ?></button>
		</form>

		<?php if ( $path ) : ?>
			<?php
			$params = $offer_type ? array( 'type' => $offer_type ) : array();
			$offers = $api->parse_product_offers( $api->get_product_offers( $path, $params ) );
			?>
			<div class="vefwp-card vefwp-card--wide">
				<h2><?php esc_html_e( 'Current offers', 'vms-elements-fastspring-woo-payment' ); ?></h2>
				<?php if ( empty( $offers ) ) : ?>
					<p><em><?php esc_html_e( 'No offers configured for this product yet.', 'vms-elements-fastspring-woo-payment' ); ?></em></p>
				<?php else : ?>
					<table class="widefat striped vefwp-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Type', 'vms-elements-fastspring-woo-payment' ); ?></th>
								<th><?php esc_html_e( 'Display', 'vms-elements-fastspring-woo-payment' ); ?></th>
								<th><?php esc_html_e( 'Items', 'vms-elements-fastspring-woo-payment' ); ?></th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $offers as $offer ) : ?>
								<tr>
									<td><code><?php echo esc_html( $offer['type'] ?? '' ); ?></code></td>
									<td><?php echo esc_html( is_array( $offer['display'] ?? null ) ? ( $offer['display']['en'] ?? reset( $offer['display'] ) ) : '' ); ?></td>
									<td><?php echo esc_html( is_array( $offer['items'] ?? null ) ? implode( ', ', $offer['items'] ) : '' ); ?></td>
									<td><?php VMS_EFWP_Admin_Resource_Base::render_view_button( $offer ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<div class="vefwp-card vefwp-card--wide">
				<h2><?php esc_html_e( 'Add or replace an offer', 'vms-elements-fastspring-woo-payment' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Saving an offer type replaces any existing offer of the same type for this product.', 'vms-elements-fastspring-woo-payment' ); ?></p>
				<form method="post">
					<?php wp_nonce_field( 'wpfs_save_product_offer' ); ?>
					<input type="hidden" name="wpfs_save_product_offer" value="1" />
					<input type="hidden" name="offer_product" value="<?php echo esc_attr( $path ); ?>" />
					<div class="vefwp-grid vefwp-grid--two">
						<p>
							<label><?php esc_html_e( 'Offer type', 'vms-elements-fastspring-woo-payment' ); ?><br />
								<select name="new_offer_type" required>
									<?php foreach ( self::offer_types() as $type ) : ?>
										<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $type ); ?></option>
									<?php endforeach; ?>
								</select>
							</label>
						</p>
						<p>
							<label><?php esc_html_e( 'Display message (English)', 'vms-elements-fastspring-woo-payment' ); ?><br />
								<input type="text" name="new_offer_display" class="regular-text" />
							</label>
						</p>
						<p class="vefwp-grid--full">
							<label><?php esc_html_e( 'Offer product paths (comma separated)', 'vms-elements-fastspring-woo-payment' ); ?><br />
								<input type="text" name="new_offer_items" required class="regular-text" placeholder="addon-product, upsell-product" />
							</label>
						</p>
					</div>
					<p><button class="button button-primary"><?php esc_html_e( 'Save offer', 'vms-elements-fastspring-woo-payment' ); ?></button></p>
				</form>
			</div>
		<?php endif;
	}

	/**
	 * Save or replace a product offer.
	 *
	 * @param VMS_EFWP_API $api API client.
	 */
	private static function handle_save_offer( $api ) {
		$path  = VMS_EFWP_Admin_Resource_Base::post_text( 'offer_product' );
		$type  = VMS_EFWP_Admin_Resource_Base::post_text( 'new_offer_type' );
		$items = array_filter( array_map( 'trim', explode( ',', VMS_EFWP_Admin_Resource_Base::post_text( 'new_offer_items' ) ) ) );
		if ( ! $path || ! $type || empty( $items ) ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>', esc_html__( 'Product path, offer type, and at least one item are required.', 'vms-elements-fastspring-woo-payment' ) );
			return;
		}

		$existing = $api->parse_product_offers( $api->get_product_offers( $path ) );
		$offers   = array();
		foreach ( $existing as $offer ) {
			if ( ( $offer['type'] ?? '' ) !== $type ) {
				$offers[] = $offer;
			}
		}

		$new_offer = array(
			'type'  => $type,
			'items' => array_values( $items ),
		);
		$display = VMS_EFWP_Admin_Resource_Base::post_text( 'new_offer_display' );
		if ( $display ) {
			$new_offer['display'] = array( 'en' => $display );
		}
		$offers[] = $new_offer;

		VMS_EFWP_Admin_Resource_Base::render_result_notice( $api->upsert_product_offers( $path, $offers ) );
	}

	/**
	 * Supported FastSpring offer types.
	 *
	 * @return string[]
	 */
	private static function offer_types() {
		return array(
			'addon',
			'alternatives',
			'cross-sell',
			'crossgrade',
			'downgrade',
			'downsell',
			'upsell',
			'upgrade',
		);
	}

	/**
	 * Render the create / edit form.
	 */
	private static function render_form() {
		?>
		<div class="vefwp-card" data-vefwp-form="save-product" hidden>
			<div class="vefwp-card__head">
				<h2 id="vefwp-product-form-title"><?php esc_html_e( 'Create product', 'vms-elements-fastspring-woo-payment' ); ?></h2>
				<button type="button" class="button button-link" data-vefwp-close-form="save-product"><?php esc_html_e( 'Close', 'vms-elements-fastspring-woo-payment' ); ?></button>
			</div>
			<form method="post" id="vefwp-product-form" data-vefwp-slug-form>
				<?php wp_nonce_field( 'wpfs_save_product' ); ?>
				<input type="hidden" name="wpfs_save_product" value="1" />
				<div class="vefwp-grid vefwp-grid--two">
					<p>
						<label><?php esc_html_e( 'Display name (English)', 'vms-elements-fastspring-woo-payment' ); ?>
							<br /><input type="text" name="display_name" required class="regular-text" data-vefwp-field="display" data-vefwp-slug-source autocomplete="off" />
						</label>
					</p>
					<p>
						<label><?php esc_html_e( 'Product path (slug)', 'vms-elements-fastspring-woo-payment' ); ?>
							<span class="vefwp-help" title="<?php esc_attr_e( 'Identifier in FastSpring. Re-using an existing path updates that product.', 'vms-elements-fastspring-woo-payment' ); ?>">?</span>
							<br />
							<input type="text" name="product_path" required class="regular-text" placeholder="my-app-pro" data-vefwp-field="product" data-vefwp-slug-target autocomplete="off" />
						</label>
						<span class="description"><?php esc_html_e( 'Auto-generated from the display name. You can edit it — spaces and capitals are converted to a slug (e.g. VMS Fastspring Plugin → vms-fastspring-plugin).', 'vms-elements-fastspring-woo-payment' ); ?></span>
					</p>
					<p>
						<label><?php esc_html_e( 'SKU', 'vms-elements-fastspring-woo-payment' ); ?>
							<br /><input type="text" name="sku" class="regular-text" data-vefwp-field="sku" />
						</label>
					</p>
					<p>
						<label><?php esc_html_e( 'Format', 'vms-elements-fastspring-woo-payment' ); ?>
							<br />
							<select name="format" data-vefwp-field="format">
								<option value="digital"><?php esc_html_e( 'Digital', 'vms-elements-fastspring-woo-payment' ); ?></option>
								<option value="physical"><?php esc_html_e( 'Physical', 'vms-elements-fastspring-woo-payment' ); ?></option>
								<option value="digital-and-physical"><?php esc_html_e( 'Digital and physical', 'vms-elements-fastspring-woo-payment' ); ?></option>
							</select>
						</label>
					</p>
					<p class="description">
						<?php esc_html_e( 'Product visibility (public/private) is read-only in the API and must be changed in the FastSpring app.', 'vms-elements-fastspring-woo-payment' ); ?>
					</p>
					<p>
						<label><?php esc_html_e( 'Badge (English)', 'vms-elements-fastspring-woo-payment' ); ?>
							<br /><input type="text" name="badge" class="regular-text" data-vefwp-field="badge" placeholder="<?php esc_attr_e( 'Best Value', 'vms-elements-fastspring-woo-payment' ); ?>" />
						</label>
					</p>
					<p>
						<label><?php esc_html_e( 'Rank', 'vms-elements-fastspring-woo-payment' ); ?>
							<br /><input type="number" min="0" name="rank" class="regular-text" data-vefwp-field="rank" value="0" />
						</label>
					</p>
					<p class="vefwp-grid--full">
						<label><?php esc_html_e( 'Image URL', 'vms-elements-fastspring-woo-payment' ); ?>
							<br /><input type="url" name="image" class="regular-text" data-vefwp-field="image" placeholder="https://..." />
						</label>
					</p>
					<p class="vefwp-grid--full">
						<label><?php esc_html_e( 'Short summary', 'vms-elements-fastspring-woo-payment' ); ?>
							<br /><textarea name="summary" rows="2" class="regular-text" data-vefwp-field="summary"></textarea>
						</label>
					</p>
					<p class="vefwp-grid--full">
						<label><?php esc_html_e( 'Call to action', 'vms-elements-fastspring-woo-payment' ); ?>
							<br /><textarea name="action_text" rows="2" class="regular-text" data-vefwp-field="action"></textarea>
						</label>
					</p>
					<p class="vefwp-grid--full">
						<label><?php esc_html_e( 'Full description', 'vms-elements-fastspring-woo-payment' ); ?>
							<br /><textarea name="full_description" rows="3" class="regular-text" data-vefwp-field="full"></textarea>
						</label>
					</p>
					<p class="vefwp-grid--full">
						<label><?php esc_html_e( 'Fulfillment instructions', 'vms-elements-fastspring-woo-payment' ); ?>
							<br /><textarea name="fulfillment_instructions" rows="2" class="regular-text" data-vefwp-field="fulfillment"></textarea>
						</label>
					</p>
				</div>

				<h3 style="margin-top:8px;"><?php esc_html_e( 'Pricing', 'vms-elements-fastspring-woo-payment' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Add one or more currencies. Use ISO 4217 codes (USD, EUR, GBP, ...).', 'vms-elements-fastspring-woo-payment' ); ?></p>
				<table class="widefat vefwp-pricing">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Currency', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th><?php esc_html_e( 'Price', 'vms-elements-fastspring-woo-payment' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody id="vefwp-pricing-rows">
						<tr>
							<td><input type="text" name="pricing[currency][]" maxlength="3" value="USD" /></td>
							<td><input type="number" step="0.01" name="pricing[price][]" required value="0" /></td>
							<td><button type="button" class="button button-small vefwp-pricing-remove">&times;</button></td>
						</tr>
					</tbody>
				</table>
				<p>
					<button type="button" class="button" id="vefwp-pricing-add"><?php esc_html_e( 'Add currency', 'vms-elements-fastspring-woo-payment' ); ?></button>
				</p>

				<p>
					<button class="button button-primary" id="vefwp-product-submit"><?php esc_html_e( 'Create product', 'vms-elements-fastspring-woo-payment' ); ?></button>
					<button type="button" class="button" data-vefwp-close-form="save-product"><?php esc_html_e( 'Cancel', 'vms-elements-fastspring-woo-payment' ); ?></button>
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
		<table class="widefat striped vefwp-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Path', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Display', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'SKU', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Price', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Format', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Visibility', 'vms-elements-fastspring-woo-payment' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'vms-elements-fastspring-woo-payment' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $products ) ) : ?>
					<?php VMS_EFWP_Admin_Resource_Base::render_empty_row( __( 'No products in your FastSpring catalog yet.', 'vms-elements-fastspring-woo-payment' ), 7 ); ?>
				<?php else : ?>
					<?php foreach ( $products as $p ) : ?>
						<?php
						$path          = $p['product'] ?? '';
						$display       = $p['display'] ?? array();
						$display_first = is_array( $display ) ? reset( $display ) : (string) $display;
						$pricing       = $p['pricing']['price'] ?? array();
						$delete_url    = wp_nonce_url(
							add_query_arg( array( 'page' => 'vms-efwp-products', 'tab' => 'catalog', 'delete' => $path ), admin_url( 'admin.php' ) ),
							'wpfs_delete_product'
						);
						$row_json = wp_json_encode( $p, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
						?>
						<tr>
							<td>
								<code><?php echo esc_html( $path ); ?></code>
								<?php if ( ! empty( $p['badge']['en'] ) ) : ?>
									<span class="vefwp-badge vefwp-badge--ok"><?php echo esc_html( $p['badge']['en'] ); ?></span>
								<?php endif; ?>
							</td>
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
							<td><?php echo esc_html( $p['visibility'] ?? '—' ); ?></td>
							<td class="vefwp-row-actions">
								<button type="button" class="button button-small vefwp-edit-product" data-product="<?php echo esc_attr( $row_json ); ?>"><?php esc_html_e( 'Edit', 'vms-elements-fastspring-woo-payment' ); ?></button>
								<?php VMS_EFWP_Admin_Resource_Base::render_view_button( $p ); ?>
								<a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-products', 'tab' => 'lookup', 'product_path' => $path ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Lookup', 'vms-elements-fastspring-woo-payment' ); ?></a>
								<a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-products', 'tab' => 'prices', 'price_lookup' => $path ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Price', 'vms-elements-fastspring-woo-payment' ); ?></a>
								<a class="button button-small" href="<?php echo esc_url( add_query_arg( array( 'page' => 'vms-efwp-products', 'tab' => 'offers', 'offer_product' => $path ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Offers', 'vms-elements-fastspring-woo-payment' ); ?></a>
								<a class="button button-small" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Delete product?', 'vms-elements-fastspring-woo-payment' ); ?>');"><?php esc_html_e( 'Delete', 'vms-elements-fastspring-woo-payment' ); ?></a>
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
