<?php
/**
 * Shared helpers.
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin asset URL helper.
 *
 * @param string $relative_path Path relative to the plugin root.
 * @return string
 */
function vms_efpg_asset_url( $relative_path ) {
	return VMS_EFPG_URL . ltrim( $relative_path, '/' );
}
