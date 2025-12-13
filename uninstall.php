<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WPaigen
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

$license_type = get_option( 'wpaigen_license_type', 'free' );
$license_key = get_option( 'wpaigen_license_key', '' );

if ( $license_type === 'free' || empty( $license_key ) ) {
    delete_option( 'wpaigen_license_key' );
    delete_option( 'wpaigen_license_type' );
    delete_option( 'wpaigen_daily_limit' );
}

delete_option( 'wpaigen_usage_today' );
delete_option( 'wpaigen_last_usage_date' );
delete_option( 'wpaigen_midtrans_client_key' );

if ( $license_type === 'pro' && ! empty( $license_key ) ) {
    error_log( "WPaigen: Preserving Pro license (Key: " . substr( $license_key, 0, 8 ) . "... ) during uninstall" );
}