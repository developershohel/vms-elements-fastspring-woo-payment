<?php
/**
 * Plugin Name:       VMS Elements Payment Gateway with FastSpring for WooCommerce
 * Plugin URI:        https://vmselements.com/product/vms-elements-fastspring-payment-gateway
 * Description:       Connect WooCommerce to FastSpring checkout (classic and blocks), webhooks, and stored orders.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Tested up to:      7.0
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            VMS Elements
 * Author URI:        https://vmselements.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       vms-elements-fastspring-payment-gateway
 * Domain Path:       /languages
 * WC requires at least: 7.0
 * WC tested up to:   10.9
 */

defined( 'ABSPATH' ) || exit;

define( 'VMS_EFPG_VERSION', '1.0.0' );
define( 'VMS_EFPG_FILE', __FILE__ );
define( 'VMS_EFPG_PATH', plugin_dir_path( __FILE__ ) );
define( 'VMS_EFPG_URL', plugin_dir_url( __FILE__ ) );
define( 'VMS_EFPG_BASENAME', plugin_basename( __FILE__ ) );

require_once VMS_EFPG_PATH . 'includes/class-vms-efpg-helpers.php';
require_once VMS_EFPG_PATH . 'includes/class-vms-efpg.php';

/**
 * Returns the main plugin instance.
 *
 * @package VMS_EFPG
 * @return VMS_EFPG
 */
function vms_efpg() {
	return VMS_EFPG::instance();
}

register_activation_hook( __FILE__, array( 'VMS_EFPG', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'VMS_EFPG', 'deactivate' ) );

add_action( 'plugins_loaded', 'vms_efpg', 0 );
