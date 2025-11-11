<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPaigen_Admin {

    private $api_client;
    private $post_manager;

    public function __construct() {
        $this->api_client   = new WPaigen_Api();
        $this->post_manager = new WPaigen_Post_Manager();

        add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_ajax_wpaigen_validate_license', array( $this, 'ajax_validate_license' ) );
        add_action( 'wp_ajax_wpaigen_generate_article', array( $this, 'ajax_generate_article' ) );
        add_action( 'wp_ajax_wpaigen_create_transaction', array( $this, 'ajax_create_transaction' ) );
        add_action( 'wp_ajax_wpaigen_get_google_trends', array( $this, 'ajax_get_google_trends' ) );

        add_action( 'admin_init', array( $this, 'reset_daily_usage_if_needed' ) );
    }

    public function add_plugin_menu() {
        add_menu_page(
            __( 'WPaigen AI Generator', 'wpaigen-ai-generator' ),
            __( 'WPaigen', 'wpaigen-ai-generator' ),
            'manage_options',
            'wpaigen',
            array( $this, 'display_dashboard_page' ),
            'dashicons-superhero',
            80
        );

        add_submenu_page(
            'wpaigen',
            __( 'Dashboard', 'wpaigen-ai-generator' ),
            __( 'Dashboard', 'wpaigen-ai-generator' ),
            'manage_options',
            'wpaigen',
            array( $this, 'display_dashboard_page' )
        );

        add_submenu_page(
            'wpaigen',
            __( 'Generate Article', 'wpaigen-ai-generator' ),
            __( 'Generate Article', 'wpaigen-ai-generator' ),
            'manage_options',
            'wpaigen-generate',
            array( $this, 'display_generate_page' )
        );

        add_submenu_page(
            'wpaigen',
            __( 'License', 'wpaigen-ai-generator' ),
            __( 'License', 'wpaigen-ai-generator' ),
            'manage_options',
            'wpaigen-license',
            array( $this, 'display_license_page' )
        );
    }


    public function enqueue_admin_assets( $hook_suffix ) {
        if ( strpos( $hook_suffix, 'wpaigen' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'wpaigen-admin-css',
            WPAIGEN_URL . 'admin/css/wpaigen-admin.css',
            array(),
            WPAIGEN_VERSION
        );

        wp_enqueue_script(
            'wpaigen-admin-js',
            WPAIGEN_URL . 'admin/js/wpaigen-admin.js',
            array( 'jquery' ),
            WPAIGEN_VERSION,
            true
        );

        wp_localize_script(
            'wpaigen-admin-js',
            'wpaigen_ajax_object',
            array(
                'ajax_url'            => admin_url( 'admin-ajax.php' ),
                'nonce'               => wp_create_nonce( 'wpaigen_nonce' ),
                'is_pro'              => ( get_option( 'wpaigen_license_type', 'free' ) === 'pro' ),
                'daily_limit'         => (int) get_option( 'wpaigen_daily_limit', 2 ),
                'usage_today'         => (int) get_option( 'wpaigen_usage_today', 0 ),
                'midtrans_client_key' => WPAIGEN_MIDTRANS_CLIENT_KEY,
                'current_license_key' => get_option('wpaigen_license_key', ''),
                'base_api_url'        => WPAIGEN_BASE_API_URL,
            )
        );

        wp_enqueue_script(
            'midtrans-snap',
            WPAIGEN_MIDTRANS_SNAP_URL,
            array(),
            WPAIGEN_VERSION,
            true
        );
    }

    public function display_dashboard_page() {
        include_once WPAIGEN_DIR . 'admin/views/wpaigen-dashboard.php';
    }

    public function display_generate_page() {
        include_once WPAIGEN_DIR . 'admin/views/wpaigen-generate.php';
    }

    public function display_license_page() {
        include_once WPAIGEN_DIR . 'admin/views/wpaigen-license.php';
    }

    public function reset_daily_usage_if_needed() {
        $last_usage_date = get_option( 'wpaigen_last_usage_date', gmdate( 'Y-m-d' ) );
        if ( $last_usage_date !== gmdate( 'Y-m-d' ) ) {
            update_option( 'wpaigen_usage_today', 0 );
            update_option( 'wpaigen_last_usage_date', gmdate( 'Y-m-d' ) );
        }
    }

    public function ajax_validate_license() {
        check_ajax_referer( 'wpaigen_nonce', 'nonce' );

        $license_key = '';
		if ( isset( $_POST['license_key'] ) ) {
			$license_key = '';
			if ( isset( $_POST['license_key'] ) ) {
				$license_key = sanitize_text_field( wp_unslash( $_POST['license_key'] ) );
			}
		}
        $domain = get_site_url();

        if ( empty( $license_key ) ) {
            wp_send_json_error( array( 'message' => __( 'License key cannot be empty.', 'wpaigen-ai-generator' ) ) );
        }

        $response = $this->api_client->validate_license( $license_key, $domain );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        if ( isset( $response['valid'] ) && $response['valid'] ) {
            update_option( 'wpaigen_license_key', $license_key );
            update_option( 'wpaigen_license_type', strtolower( $response['type'] ) );
            if ( isset( $response['quota'] ) ) {
                update_option( 'wpaigen_daily_limit', $response['quota'] );
            } else {
                update_option( 'wpaigen_daily_limit', -1 );
            }
            update_option( 'wpaigen_usage_today', isset( $response['used'] ) ? $response['used'] : 0 );
            wp_send_json_success( array(
                'message' => __( 'License activated successfully!', 'wpaigen-ai-generator' ),
                'type'    => strtolower( $response['type'] ),
                'quota'   => isset( $response['quota'] ) ? $response['quota'] : -1,
                'used'    => isset( $response['used'] ) ? $response['used'] : 0,
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Invalid or inactive license key.', 'wpaigen-ai-generator' ) ) );
        }
    }

    public function ajax_generate_article() {
        check_ajax_referer( 'wpaigen_nonce', 'nonce' );

        $license_key = get_option( 'wpaigen_license_key' );
        $license_type = get_option( 'wpaigen_license_type', 'free' );
        $usage_today = (int) get_option( 'wpaigen_usage_today', 0 );
        $daily_limit = (int) get_option( 'wpaigen_daily_limit', 2 );

        if ( empty( $license_key ) ) {
             wp_send_json_error( array( 'message' => __( 'No license key found. Please activate your license.', 'wpaigen-ai-generator' ) ) );
        }

        if ( $license_type === 'free' && $usage_today >= $daily_limit ) {
            wp_send_json_error( array( 'message' => __( 'You have reached your daily generation limit. Please upgrade to Pro for unlimited access.', 'wpaigen-ai-generator' ) ) );
        }

        $keyword = '';
		if ( isset( $_POST['keyword'] ) ) {
			$keyword = sanitize_text_field( wp_unslash( $_POST['keyword'] ) );
		}

		$language = '';
		if ( isset( $_POST['language'] ) ) {
			$language = sanitize_text_field( wp_unslash( $_POST['language'] ) );
		}

        $length = 0;
		if ( isset( $_POST['length'] ) && ! empty( $_POST['length'] ) ) {
    		$length = absint( wp_unslash( $_POST['length'] ) );
		}

        $tone = '';
		if ( isset( $_POST['tone'] ) ) {
			$tone = sanitize_text_field( wp_unslash( $_POST['tone'] ) );
		}

        $use_featured_image = isset( $_POST['use_featured_image'] ) && $_POST['use_featured_image'] === 'true';


        if ( $license_type === 'free' ) {
            if ( $length > 200 ) {
                wp_send_json_error( array( 'message' => __( 'Free version is limited to 200 words.', 'wpaigen-ai-generator' ) ) );
            }
            if ( ! in_array( $language, array( 'indonesian', 'english' ) ) ) {
                wp_send_json_error( array( 'message' => __( 'Free version supports only Indonesian and English.', 'wpaigen-ai-generator' ) ) );
            }
            if ( $tone !== 'professional' ) {
                wp_send_json_error( array( 'message' => __( 'Free version supports only professional tone.', 'wpaigen-ai-generator' ) ) );
            }
        }


        $response = $this->api_client->generate_article( $license_key, $keyword, $language, $length, $tone );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        if ( isset( $response['success'] ) && $response['success'] ) {
            $post_id = $this->post_manager->create_ai_post( $response, $use_featured_image );

            if ( is_wp_error( $post_id ) ) {
                wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );
            }

            $new_usage_today = (int) get_option( 'wpaigen_usage_today', 0 ) + 1;
            update_option( 'wpaigen_usage_today', $new_usage_today );
            update_option( 'wpaigen_last_usage_date', gmdate( 'Y-m-d' ) );

            if ( isset( $response['quota_remaining'] ) ) {
                if ($license_type === 'free') {
                }
            }


            wp_send_json_success( array(
                'message'       => __( 'Article generated and saved as draft!', 'wpaigen-ai-generator' ),
                'post_id'       => $post_id,
                'edit_post_url' => get_edit_post_link( $post_id, 'raw' ),
                'quota_remaining' => ( $license_type === 'free' ? ($daily_limit - $new_usage_today) : -1 ),
                'usage_today'   => $new_usage_today
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to generate article. Please try again.', 'wpaigen-ai-generator' ) ) );
        }
    }

    public function ajax_create_transaction() {
        check_ajax_referer( 'wpaigen_nonce', 'nonce' );

		$email = '';
        $domain = parse_url( home_url(), PHP_URL_HOST );
		if ( isset( $_POST['email'] ) ) {
			$email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
		}

        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'wpaigen-ai-generator' ) ) );
        }

        $response = $this->api_client->create_transaction( $email, $domain );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        if ( isset( $response['success'] ) && $response['success'] ) {
            wp_send_json_success( array(
                'message'     => __( 'Transaction initiated.', 'wpaigen-ai-generator' ),
                'token'       => $response['token'],
                'redirect_url' => $response['redirect_url'],
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to initiate transaction.', 'wpaigen-ai-generator' ) ) );
        }
    }

    public function ajax_get_google_trends() {
        check_ajax_referer( 'wpaigen_nonce', 'nonce' );

        $license_key = get_option( 'wpaigen_license_key' );

        if ( empty( $license_key ) ) {
            wp_send_json_error( array( 'message' => __( 'No license key found. Please activate your license first.', 'wpaigen-ai-generator' ) ) );
        }

        $response = $this->api_client->get_google_trends( $license_key );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        if ( isset( $response['success'] ) && $response['success'] ) {
            wp_send_json_success( array( 'trends' => $response['data'] ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to fetch Google Trends data.', 'wpaigen-ai-generator' ) ) );
        }
    }
}
