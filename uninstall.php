<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WPaigen
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Clear all plugin options
delete_option( 'wpaigen_license_key' );
delete_option( 'wpaigen_license_type' );
delete_option( 'wpaigen_daily_limit' );
delete_option( 'wpaigen_usage_today' );
delete_option( 'wpaigen_last_usage_date' );
delete_option( 'wpaigen_midtrans_client_key' ); // If you save it as option

// Optional: Delete posts created by the plugin
// This is dangerous and should usually be left to the user
// $args = array(
//     'post_type' => 'post', // Or your custom post type
//     'meta_key'  => '_wpaigen_generated', // A custom field indicating it's from this plugin
//     'posts_per_page' => -1,
//     'fields' => 'ids',
// );
// $posts = get_posts( $args );
// foreach ( $posts as $post_id ) {
//     wp_delete_post( $post_id, true ); // true to force delete
// }