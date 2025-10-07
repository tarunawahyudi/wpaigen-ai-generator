<?php
/**
 * Plugin Name: WPaigen AI Generator
 * Plugin URI:  https://wpaigen.stacklab.id/
 * Description: Generate high-quality articles and SEO with AI, powered by WPaigen.
 * Version:     3.2.0
 * Author:      Taruna Wahyudi
 * Author URI:  https://tarunawahyudi.github.io/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpaigen-ai-generator
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WPAIGEN_VERSION', '3.2.0' );
define( 'WPAIGEN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPAIGEN_URL', plugin_dir_url( __FILE__ ) );
define('WPAIGEN_PLUGIN_FILE', __FILE__);

require_once WPAIGEN_DIR . 'includes/wpaigen-constants.php';

spl_autoload_register( 'wpaigen_autoloader' );
function wpaigen_autoloader( $class_name ) {
    if ( strpos( $class_name, 'WPaigen_' ) !== 0 ) {
        return;
    }

    $file_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';
    $file_path = WPAIGEN_DIR . 'includes/' . $file_name;

    if ( file_exists( $file_path ) ) {
        require_once $file_path;
    }
}

class WPaigen {

    protected static $instance = null;

    private function __construct() {
        $this->load_dependencies();
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function load_dependencies() {}

    public function init_plugin() {
        if ( is_admin() ) {
            new WPaigen_Admin();
        }
    }

    public function activate() {
        add_option( 'wpaigen_license_key', '' );
        add_option( 'wpaigen_license_type', 'free' );
        add_option( 'wpaigen_usage_today', 0 );
        add_option( 'wpaigen_daily_limit', 2 );
        add_option( 'wpaigen_last_usage_date', gmdate( 'Y-m-d' ) );

        $api_client = new WPaigen_Api();
        $current_license_key = get_option( 'wpaigen_license_key', '' );
        $domain = get_site_url();
        $plugin_version = WPAIGEN_VERSION;

        $admin_email = get_option( 'admin_email', '' );

        if ( empty( $current_license_key ) ) {
            $response = $api_client->register_free_license( $domain, $admin_email );

            if ( ! is_wp_error( $response ) && isset( $response['success'] ) && $response['success'] ) {
                update_option( 'wpaigen_license_key', sanitize_text_field( $response['license_key'] ) );
                update_option( 'wpaigen_license_type', 'free' );
                update_option( 'wpaigen_daily_limit', (int) $response['quota'] );
            } else {
                update_option( 'wpaigen_license_type', 'free' );
            }
        } else {
            $response = $api_client->validate_license( $current_license_key, $domain );
            if ( ! is_wp_error( $response ) && isset( $response['success'] ) && $response['success'] ) {
                update_option( 'wpaigen_license_type', $response['type'] );
                update_option( 'wpaigen_daily_limit', (int) $response['daily_limit'] );
            } else {
                update_option( 'wpaigen_license_type', 'free' );
            }
        }
    }

    public function deactivate() {}
}

WPaigen::get_instance();
