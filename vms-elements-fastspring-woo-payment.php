<?php
/**
 * Plugin Name:       VMS Elements Fastspring Woo Payment
 * Plugin URI:        https://vmselements.com/product/vms-elements-fastspring-woo-payment
 * Description:       Integrate FastSpring as a WooCommerce payment processor with webhooks, analytics dashboard, and subscription management.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            VMS Elements
 * Author URI:        https://vmselements.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       vms-elements-fastspring-woo-payment
 * Domain Path:       /languages
 * WC requires at least: 7.0
 * WC tested up to:   9.0
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

define( 'VMS_EFWP_VERSION', '1.0.0' );
define( 'VMS_EFWP_FILE', __FILE__ );
define( 'VMS_EFWP_PATH', plugin_dir_path( __FILE__ ) );
define( 'VMS_EFWP_URL', plugin_dir_url( __FILE__ ) );
define( 'VMS_EFWP_BASENAME', plugin_basename( __FILE__ ) );

require_once VMS_EFWP_PATH . 'includes/class-vms-efwp-features.php';
require_once VMS_EFWP_PATH . 'includes/class-vms-efwp.php';

/**
 * Returns the main plugin instance.
 *
 * @return VMS_EFWP
 */
function vms_efwp() {
	return VMS_EFWP::instance();
}

register_activation_hook( __FILE__, array( 'VMS_EFWP', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'VMS_EFWP', 'deactivate' ) );

add_action( 'plugins_loaded', 'vms_efwp', 0 );
