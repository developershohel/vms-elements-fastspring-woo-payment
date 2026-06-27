<?php
/**
 * Feature gate helpers (free plugin). Pro implementation files are not bundled here.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Features.
 */
class VMS_EFWP_Features {

	const PRO_URL = 'https://vmselements.com/product/vms-elements-fastspring-woo-payment-pro';

	/**
	 * Whether Pro is active and licensed.
	 *
	 * @return bool
	 */
	public static function is_pro() {
		return (bool) apply_filters( 'vms_efwp_is_pro', false );
	}

	/**
	 * Check whether a Pro feature is available.
	 *
	 * @param string $feature Feature slug (unused in free build — always false unless Pro is licensed).
	 * @return bool
	 */
	public static function has( $feature ) {
		$free = array(
			'gateway',
			'webhooks',
			'stored_orders',
			'settings',
			'single_custom_price',
			'test_connection',
			'dashboard_basic',
			'dashboard_analytics',
			'popup_checkout',
			'checkout_blocks',
		);

		if ( in_array( sanitize_key( (string) $feature ), $free, true ) ) {
			return true;
		}

		return self::is_pro();
	}

	/**
	 * Require a feature or terminate the request.
	 *
	 * @param string $feature Feature slug.
	 */
	public static function require( $feature ) {
		if ( self::has( $feature ) ) {
			return;
		}

		if ( wp_doing_ajax() ) {
			wp_send_json_error(
				array(
					'message' => self::upgrade_message(),
					'code'    => 'pro_required',
				),
				403
			);
		}

		wp_die(
			wp_kses_post( self::upgrade_message_html() ),
			esc_html__( 'Pro feature', 'vms-elements-fastspring-woo-payment' ),
			array( 'response' => 403 )
		);
	}

	/**
	 * Pro product URL.
	 *
	 * @return string
	 */
	public static function pro_url() {
		return (string) apply_filters( 'vms_efwp_pro_url', self::PRO_URL );
	}

	/**
	 * Plain upgrade message.
	 *
	 * @return string
	 */
	public static function upgrade_message() {
		return __( 'This feature requires VMS Elements Fastspring Woo Payment Pro.', 'vms-elements-fastspring-woo-payment' );
	}

	/**
	 * Upgrade message with link.
	 *
	 * @return string
	 */
	public static function upgrade_message_html() {
		return sprintf(
			/* translators: %s: Pro product URL */
			__( 'This feature requires the separate <strong>Pro add-on plugin</strong>. <a href="%s" target="_blank" rel="noopener noreferrer">Get Pro on VMS Elements</a>, install it, and activate your license.', 'vms-elements-fastspring-woo-payment' ),
			esc_url( self::pro_url() )
		);
	}

	/**
	 * Render a standard Pro upsell notice.
	 *
	 * @param string $context Optional context line.
	 */
	public static function render_upgrade_notice( $context = '' ) {
		echo '<div class="notice notice-info vms-efwp-pro-notice"><p>';
		if ( $context ) {
			echo esc_html( $context ) . ' ';
		}
		echo wp_kses_post( self::upgrade_message_html() );
		echo '</p></div>';
	}
}

/**
 * Whether Pro is active.
 *
 * @return bool
 */
function vms_efwp_is_pro() {
	return VMS_EFWP_Features::is_pro();
}

/**
 * Whether a feature is available.
 *
 * @param string $feature Feature slug.
 * @return bool
 */
function vms_efwp_feature( $feature ) {
	return VMS_EFWP_Features::has( $feature );
}

/**
 * Free plugin asset URL helper.
 *
 * @param string $relative_path Path relative to the free plugin root.
 * @return string
 */
function vms_efwp_asset_url( $relative_path ) {
	return VMS_EFWP_URL . ltrim( $relative_path, '/' );
}

/**
 * Pro add-on asset URL helper (defined only when Pro plugin is loaded).
 *
 * @param string $relative_path Path relative to the Pro plugin root.
 * @return string
 */
function vms_efwp_pro_asset_url( $relative_path ) {
	if ( defined( 'VMS_EFWP_PRO_URL' ) ) {
		return VMS_EFWP_PRO_URL . ltrim( $relative_path, '/' );
	}
	return '';
}
