<?php
/**
 * One-way product sync: WooCommerce -> FastSpring.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Product_Sync.
 */
class VMS_EFWP_Product_Sync {

	/**
	 * API.
	 *
	 * @var VMS_EFWP_API
	 */
	private $api;

	/**
	 * Settings.
	 *
	 * @var VMS_EFWP_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param VMS_EFWP_API      $api      API.
	 * @param VMS_EFWP_Settings $settings Settings.
	 */
	public function __construct( VMS_EFWP_API $api, VMS_EFWP_Settings $settings ) {
		$this->api      = $api;
		$this->settings = $settings;

		add_action( 'save_post_product', array( $this, 'on_product_save' ), 20, 3 );
	}

	/**
	 * Sync a product on save when the option is enabled.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 * @param bool    $update  Update flag.
	 */
	public function on_product_save( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( 'yes' !== $this->settings->get( 'sync_products', 'no' ) ) {
			return;
		}
		if ( ! $this->settings->has_credentials() ) {
			return;
		}
		if ( ! function_exists( 'wc_get_product' ) ) {
			return;
		}

		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}

		$slug = $this->build_path( $product );

		$payload = array(
			'product'        => $slug,
			'display'        => array( 'en' => $product->get_name() ),
			'description'    => array(
				'summary' => array( 'en' => wp_strip_all_tags( $product->get_short_description() ) ),
				'full'    => array( 'en' => wp_strip_all_tags( $product->get_description() ) ),
			),
			'pricing'        => array(
				'price' => array(
					strtoupper( get_woocommerce_currency() ) => (float) $product->get_price(),
				),
			),
			'sku'            => $product->get_sku() ? $product->get_sku() : 'wc-' . $product->get_id(),
		);

		$result = $this->api->upsert_product( $payload );
		if ( is_wp_error( $result ) ) {
			VMS_EFWP_Logger::error( 'Product sync failed: ' . $result->get_error_message(), 'sync', array( 'product_id' => $post_id ) );
			update_post_meta( $post_id, '_fastspring_sync_error', $result->get_error_message() );
		} else {
			update_post_meta( $post_id, '_vms_efwp_product_path', $slug );
			delete_post_meta( $post_id, '_fastspring_sync_error' );
			VMS_EFWP_Logger::info( 'Synced product to FastSpring: ' . $slug, 'sync' );
		}
	}

	/**
	 * Build the FastSpring product path slug.
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	private function build_path( $product ) {
		$slug = sanitize_title( $product->get_slug() );
		if ( '' === $slug ) {
			$slug = 'wc-' . $product->get_id();
		}
		return $slug;
	}

	/**
	 * Sync all published WooCommerce products to FastSpring.
	 *
	 * @return array{synced:int,failed:int,skipped:int,errors:string[]}
	 */
	public function sync_all_products() {
		$summary = array(
			'synced'   => 0,
			'failed'   => 0,
			'skipped'  => 0,
			'errors'   => array(),
		);

		if ( ! function_exists( 'wc_get_products' ) ) {
			$summary['errors'][] = __( 'WooCommerce is not available.', 'vms-elements-fastspring-woo-payment' );
			return $summary;
		}

		if ( ! $this->settings->has_credentials() ) {
			$summary['errors'][] = __( 'FastSpring API credentials are not configured.', 'vms-elements-fastspring-woo-payment' );
			return $summary;
		}

		$page = 1;
		do {
			$products = wc_get_products(
				array(
					'status' => 'publish',
					'limit'  => 50,
					'page'   => $page,
					'return' => 'objects',
				)
			);

			if ( empty( $products ) ) {
				break;
			}

			foreach ( $products as $product ) {
				if ( ! $product instanceof WC_Product ) {
					++$summary['skipped'];
					continue;
				}

				$slug    = $this->build_path( $product );
				$payload = array(
					'product'        => $slug,
					'display'        => array( 'en' => $product->get_name() ),
					'description'    => array(
						'summary' => array( 'en' => wp_strip_all_tags( $product->get_short_description() ) ),
						'full'    => array( 'en' => wp_strip_all_tags( $product->get_description() ) ),
					),
					'pricing'        => array(
						'price' => array(
							strtoupper( get_woocommerce_currency() ) => (float) $product->get_price(),
						),
					),
					'sku'            => $product->get_sku() ? $product->get_sku() : 'wc-' . $product->get_id(),
				);

				$result = $this->api->upsert_product( $payload );
				if ( is_wp_error( $result ) ) {
					++$summary['failed'];
					$summary['errors'][] = $slug . ': ' . $result->get_error_message();
					VMS_EFWP_Logger::error( 'Bulk product sync failed: ' . $result->get_error_message(), 'sync', array( 'product_id' => $product->get_id() ) );
					continue;
				}

				update_post_meta( $product->get_id(), '_vms_efwp_product_path', $slug );
				delete_post_meta( $product->get_id(), '_fastspring_sync_error' );
				++$summary['synced'];
			}

			++$page;
		} while ( count( $products ) === 50 );

		return $summary;
	}
}
