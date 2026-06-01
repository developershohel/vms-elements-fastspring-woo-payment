<?php
/**
 * One-way product sync: WooCommerce -> FastSpring.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Product_Sync.
 */
class WP_FastSpring_Product_Sync {

	/**
	 * API.
	 *
	 * @var WP_FastSpring_API
	 */
	private $api;

	/**
	 * Settings.
	 *
	 * @var WP_FastSpring_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param WP_FastSpring_API      $api      API.
	 * @param WP_FastSpring_Settings $settings Settings.
	 */
	public function __construct( WP_FastSpring_API $api, WP_FastSpring_Settings $settings ) {
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
			'fulfillments'   => array(),
		);

		$result = $this->api->upsert_product( $payload );
		if ( is_wp_error( $result ) ) {
			WP_FastSpring_Logger::error( 'Product sync failed: ' . $result->get_error_message(), 'sync', array( 'product_id' => $post_id ) );
			update_post_meta( $post_id, '_fastspring_sync_error', $result->get_error_message() );
		} else {
			update_post_meta( $post_id, '_fastspring_product_path', $slug );
			delete_post_meta( $post_id, '_fastspring_sync_error' );
			WP_FastSpring_Logger::info( 'Synced product to FastSpring: ' . $slug, 'sync' );
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
}
