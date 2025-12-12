<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPaigen_Admin {

    private $api_client;
    private $post_manager;
    private $scheduler;

    public function __construct() {
        $this->api_client   = new WPaigen_Api();
        $this->post_manager = new WPaigen_Post_Manager();
        $this->scheduler    = new WPaigen_Scheduler();

        add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_ajax_wpaigen_validate_license', array( $this, 'ajax_validate_license' ) );
        add_action( 'wp_ajax_wpaigen_generate_article', array( $this, 'ajax_generate_article' ) );
        add_action( 'wp_ajax_wpaigen_create_transaction', array( $this, 'ajax_create_transaction' ) );
        add_action( 'wp_ajax_wpaigen_get_google_trends', array( $this, 'ajax_get_google_trends' ) );
        add_action( 'wp_ajax_wpaigen_schedule_article', array( $this, 'ajax_schedule_article' ) );
        add_action( 'wp_ajax_wpaigen_get_scheduled_posts', array( $this, 'ajax_get_scheduled_posts' ) );
        add_action( 'wp_ajax_wpaigen_delete_schedule', array( $this, 'ajax_delete_schedule' ) );
        add_action( 'wp_ajax_wpaigen_get_product_price', array( $this, 'ajax_get_product_price' ) );

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
            __( 'Scheduled Articles', 'wpaigen-ai-generator' ),
            __( 'Scheduled Articles', 'wpaigen-ai-generator' ),
            'manage_options',
            'wpaigen-schedule',
            array( $this, 'display_schedule_page' )
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
                'admin_url'           => admin_url(),
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

    public function ajax_schedule_article() {
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

        // Validate form data
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

        $scheduled_date = '';
        if ( isset( $_POST['scheduled_date'] ) ) {
            $scheduled_date = sanitize_text_field( wp_unslash( $_POST['scheduled_date'] ) );
        }

        $timezone = 'Asia/Jakarta'; // Default
        if ( isset( $_POST['timezone'] ) ) {
            $timezone = sanitize_text_field( wp_unslash( $_POST['timezone'] ) );
            // Save as plugin setting
            update_option( 'wpaigen_timezone', $timezone );
        }

        // Validate scheduled date and store consistently
        $scheduled_datetime = DateTime::createFromFormat( 'Y-m-d\TH:i', $scheduled_date );
        if ( ! $scheduled_datetime ) {
            wp_send_json_error( array( 'message' => __( 'Invalid date format. Please select a valid date and time.', 'wpaigen-ai-generator' ) ) );
        }

        // Convert to timestamp and then to MySQL datetime format
        $scheduled_timestamp = $scheduled_datetime->getTimestamp();
        $current_local_timestamp = current_time( 'timestamp' );

        // Validate that scheduled time is in the future
        if ( $scheduled_timestamp <= $current_local_timestamp ) {
            wp_send_json_error( array( 'message' => __( 'Please select a valid future date and time.', 'wpaigen-ai-generator' ) ) );
        }

        // Convert to MySQL datetime format
        $scheduled_mysql_time = date( 'Y-m-d H:i:s', $scheduled_timestamp );

        // Apply license restrictions
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

        $schedule_data = array(
            'keyword' => $keyword,
            'language' => $language,
            'length' => $length,
            'tone' => $tone,
            'use_featured_image' => $use_featured_image,
            'scheduled_date' => $scheduled_mysql_time
        );

        $schedule_id = $this->scheduler->create_schedule( $schedule_data );

        if ( is_wp_error( $schedule_id ) ) {
            wp_send_json_error( array( 'message' => $schedule_id->get_error_message() ) );
        }

        // Update usage count
        $new_usage_today = (int) get_option( 'wpaigen_usage_today', 0 ) + 1;
        update_option( 'wpaigen_usage_today', $new_usage_today );
        update_option( 'wpaigen_last_usage_date', gmdate( 'Y-m-d' ) );

        wp_send_json_success( array(
            'message' => __( 'Article scheduled successfully!', 'wpaigen-ai-generator' ),
            'schedule_id' => $schedule_id,
            'scheduled_date' => $scheduled_datetime->format( 'Y-m-d H:i:s' ),
            'quota_remaining' => ( $license_type === 'free' ? ($daily_limit - $new_usage_today) : -1 ),
            'usage_today' => $new_usage_today
        ) );
    }

    public function ajax_get_scheduled_posts() {
        check_ajax_referer( 'wpaigen_nonce', 'nonce' );

        $status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all';
        $limit = isset( $_GET['limit'] ) ? absint( wp_unslash( $_GET['limit'] ) ) : 20;
        $page = isset( $_GET['page'] ) ? absint( wp_unslash( $_GET['page'] ) ) : 1;
        $search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';

        // Ensure page is at least 1
        $page = max( 1, $page );
        $offset = ( $page - 1 ) * $limit;

        $scheduled_posts = $this->scheduler->get_scheduled_posts( $status, $limit, $offset, $search );

        // Get pagination info
        $pagination_info = $this->scheduler->get_scheduled_posts_pagination_info( $status, $limit, $search );
        $pagination_info['current_page'] = $page;

        // Get cached stats or calculate new ones
        $stats_cache_key = 'wpaigen_schedule_stats_' . md5( $status . $search );
        $stats = get_transient( $stats_cache_key );

        if ( false === $stats ) {
            $stats = array(
                'pending' => $this->scheduler->get_scheduled_posts_count( 'pending' ),
                'processing' => $this->scheduler->get_scheduled_posts_count( 'processing' ),
                'published' => $this->scheduler->get_scheduled_posts_count( 'published' ),
                'failed' => $this->scheduler->get_scheduled_posts_count( 'failed' ),
                'cancelled' => $this->scheduler->get_scheduled_posts_count( 'cancelled' ),
                'total' => $this->scheduler->get_scheduled_posts_count( 'all' ),
            );

            // Cache stats for 5 minutes
            set_transient( $stats_cache_key, $stats, 5 * MINUTE_IN_SECONDS );
        }

        wp_send_json_success( array(
            'posts' => $scheduled_posts,
            'pagination' => $pagination_info,
            'stats' => $stats,
            'status' => $status,
            'search' => $search
        ) );
    }

    public function ajax_delete_schedule() {
        check_ajax_referer( 'wpaigen_nonce', 'nonce' );

        $schedule_id = 0;
        if ( isset( $_POST['schedule_id'] ) ) {
            $schedule_id = absint( wp_unslash( $_POST['schedule_id'] ) );
        }

        if ( ! $schedule_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid schedule ID.', 'wpaigen-ai-generator' ) ) );
        }

        $scheduled_post = $this->scheduler->get_scheduled_post( $schedule_id );
        if ( ! $scheduled_post ) {
            wp_send_json_error( array( 'message' => __( 'Schedule not found.', 'wpaigen-ai-generator' ) ) );
        }

        if ( $scheduled_post['status'] === 'processing' ) {
            wp_send_json_error( array( 'message' => __( 'Cannot delete schedule that is currently being processed.', 'wpaigen-ai-generator' ) ) );
        }

        $result = $this->scheduler->update_schedule_status( $schedule_id, 'cancelled' );

        if ( false === $result ) {
            wp_send_json_error( array( 'message' => __( 'Failed to cancel schedule.', 'wpaigen-ai-generator' ) ) );
        }

        wp_send_json_success( array(
            'message' => __( 'Schedule cancelled successfully!', 'wpaigen-ai-generator' )
        ) );
    }

    
    public function ajax_test_cron() {
        // Debug log
        error_log( 'WPaigen: Test cron AJAX called' );

        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpaigen_nonce' ) ) {
            error_log( 'WPaigen: Invalid nonce' );
            wp_send_json_error( array( 'message' => 'Security check failed' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            error_log( 'WPaigen: Permission denied' );
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }

        try {
            // Get debug info
            $debug_info = $this->scheduler->debug_cron_status();

            // Force process ALL pending posts regardless of scheduled time for debugging
            global $wpdb;
            $table_name = $wpdb->prefix . 'wpaigen_scheduled_posts';

            $pending_posts = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE status = %s LIMIT 5",
                'pending'
            ), ARRAY_A );

            error_log( 'WPaigen: Found ' . count( $pending_posts ) . ' pending posts to force process' );

            if ( ! empty( $pending_posts ) ) {
                // Manually trigger the process
                $this->scheduler->process_scheduled_posts();
            } else {
                error_log( 'WPaigen: No pending posts found to process' );
            }

            $response_data = array(
                'message' => 'Cron test executed successfully',
                'debug_info' => $debug_info,
                'current_wp_time' => current_time( 'mysql' ),
                'current_gmt_time' => gmdate( 'Y-m-d H:i:s' ),
                'timezone' => get_option( 'timezone_string', 'UTC' ),
                'pending_posts_found' => count( $pending_posts ),
                'debug' => 'If you see this, AJAX is working!'
            );

            error_log( 'WPaigen: Test cron success' );
            wp_send_json_success( $response_data );

        } catch ( Exception $e ) {
            error_log( 'WPaigen: Test cron error - ' . $e->getMessage() );
            wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }

    public function ajax_force_process() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wpaigen_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }

        try {
            error_log( 'WPaigen: Force process called' );

            // Check if diagnostic mode is requested
            $diagnostic_mode = isset( $_POST['diagnostic'] ) && $_POST['diagnostic'] === 'true';

            // Force process ALL pending posts regardless of scheduled time
            global $wpdb;
            $table_name = $wpdb->prefix . 'wpaigen_scheduled_posts';

            $all_pending = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE status = %s LIMIT 10",
                'pending'
            ), ARRAY_A );

            error_log( 'WPaigen: Force processing ' . count( $all_pending ) . ' pending posts (Diagnostic: ' . ( $diagnostic_mode ? 'Yes' : 'No' ) . ')' );

            if ( empty( $all_pending ) ) {
                wp_send_json_success( array(
                    'message' => 'No pending posts found to process',
                    'processed' => 0,
                    'diagnostic_mode' => $diagnostic_mode
                ) );
            }

            $processed = 0;
            $api_client = new WPaigen_Api();
            $post_manager = new WPaigen_Post_Manager();

            foreach ( $all_pending as $post ) {
                error_log( 'WPaigen: Force processing post ID ' . $post['id'] . ' - Keyword: ' . $post['keyword'] );

                // Update to processing status
                $wpdb->update(
                    $table_name,
                    array( 'status' => 'processing' ),
                    array( 'id' => $post['id'] ),
                    array( '%s' ),
                    array( '%d' )
                );

                if ( $diagnostic_mode ) {
                    // Diagnostic mode: Create dummy article without API call
                    error_log( 'WPaigen: Diagnostic mode - creating dummy post for ID ' . $post['id'] );

                    $dummy_response = array(
                        'success' => true,
                        'title' => 'Test Article: ' . $post['keyword'],
                        'content' => 'This is a test article generated for diagnostic purposes. Original keyword: ' . $post['keyword'] . '. Language: ' . $post['language'] . '. Length: ' . $post['length'] . ' words. Tone: ' . $post['tone'] . '. Generated on: ' . date( 'Y-m-d H:i:s' ),
                        'meta_title' => 'Test Article: ' . $post['keyword'],
                        'meta_description' => 'Test article diagnostic for keyword: ' . $post['keyword']
                    );

                    $post_id = $post_manager->create_ai_post( $dummy_response, $post['use_featured_image'] );

                    if ( is_wp_error( $post_id ) ) {
                        error_log( 'WPaigen: Diagnostic mode - Failed to create post for schedule ' . $post['id'] . ': ' . $post_id->get_error_message() );
                        $wpdb->update(
                            $table_name,
                            array( 'status' => 'failed', 'error_message' => 'Diagnostic mode failed: ' . $post_id->get_error_message() ),
                            array( 'id' => $post['id'] ),
                            array( '%s', '%s' ),
                            array( '%d' )
                        );
                        continue;
                    }

                } else {
                    // Normal mode: Call API
                    $license_key = get_option( 'wpaigen_license_key' );
                    error_log( 'WPaigen: Using license key for post ' . $post['id'] . ': ' . ( $license_key ? 'present' : 'missing' ) );

                    // Validate license key
                    if ( ! $license_key ) {
                        error_log( 'WPaigen: No license key for post ' . $post['id'] );
                        $wpdb->update(
                            $table_name,
                            array( 'status' => 'failed', 'error_message' => 'No license key configured' ),
                            array( 'id' => $post['id'] ),
                            array( '%s', '%s' ),
                            array( '%d' )
                        );
                        continue;
                    }

                    $response = $api_client->generate_article(
                        $license_key,
                        $post['keyword'],
                        $post['language'],
                        $post['length'],
                        $post['tone']
                    );

                    error_log( 'WPaigen: API response for post ' . $post['id'] . ': ' . print_r( $response, true ) );

                    if ( is_wp_error( $response ) || ! isset( $response['success'] ) || ! $response['success'] ) {
                        $error = is_wp_error( $response ) ? $response->get_error_message() : 'API call failed';
                        error_log( 'WPaigen: Force process failed for post ' . $post['id'] . ': ' . $error );

                        $wpdb->update(
                            $table_name,
                            array( 'status' => 'failed', 'error_message' => $error ),
                            array( 'id' => $post['id'] ),
                            array( '%s', '%s' ),
                            array( '%d' )
                        );
                        continue;
                    }

                    // Create post
                    $post_id = $post_manager->create_ai_post( $response, $post['use_featured_image'] );

                    if ( is_wp_error( $post_id ) ) {
                        error_log( 'WPaigen: Failed to create post for schedule ' . $post['id'] . ': ' . $post_id->get_error_message() );
                        $wpdb->update(
                            $table_name,
                            array( 'status' => 'failed', 'error_message' => $post_id->get_error_message() ),
                            array( 'id' => $post['id'] ),
                            array( '%s', '%s' ),
                            array( '%d' )
                        );
                        continue;
                    }
                }

                // Publish post
                wp_update_post( array(
                    'ID' => $post_id,
                    'post_status' => 'publish'
                ) );

                // Update status to published
                $wpdb->update(
                    $table_name,
                    array( 'status' => 'published', 'post_id' => $post_id ),
                    array( 'id' => $post['id'] ),
                    array( '%s', '%d' ),
                    array( '%d' )
                );

                $processed++;
                error_log( 'WPaigen: Successfully processed post ID ' . $post['id'] . ' (Diagnostic: ' . ( $diagnostic_mode ? 'Yes' : 'No' ) . ')' );
            }

            wp_send_json_success( array(
                'message' => "Force processing completed. Processed {$processed} posts." . ( $diagnostic_mode ? ' (Diagnostic Mode - bypassed API)' : '' ),
                'processed' => $processed,
                'diagnostic_mode' => $diagnostic_mode
            ) );

        } catch ( Exception $e ) {
            error_log( 'WPaigen: Force process error - ' . $e->getMessage() );
            wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }

    public function ajax_get_product_price() {
        check_ajax_referer( 'wpaigen_nonce', 'nonce' );

        // Default to Pro product with SKU 'WPAIGEN-PRO-LIFETIME'
        $product_sku = 'WPAIGEN-001';

        // Detect user country and get currency
        $country = $this->api_client->detect_user_country();
        $currency = $this->api_client->get_currency_by_country( $country );
        $product_response = $this->api_client->get_product_by_sku( $product_sku );

        if ( is_wp_error( $product_response ) ) {
            // Fallback to hardcoded product ID 1 if SKU lookup fails
            $product_id = 1;
            $price_response = $this->api_client->get_product_price( $product_id, $currency );

            if ( is_wp_error( $price_response ) ) {
                // Final fallback - return updated default pricing
                wp_send_json_success( array(
                    'price' => $currency === 'IDR' ? 99000 : 6,
                    'currency' => $currency,
                    'formatted_price' => $currency === 'IDR' ? 'Rp 99.000' : '$6.00',
                    'product_name' => 'WPaigen Pro',
                    'country' => $country,
                    'fallback' => true
                ) );
            } else {
                $price = isset( $price_response['data']['price'] ) ? $price_response['data']['price'] : ($currency === 'IDR' ? 99000 : 6);
                wp_send_json_success( array(
                    'price' => $price,
                    'currency' => $currency,
                    'formatted_price' => $this->format_price( $price, $currency ),
                    'product_name' => isset( $price_response['data']['productName'] ) ? $price_response['data']['productName'] : 'WPaigen Pro',
                    'country' => $country,
                    'fallback' => false
                ) );
            }
        } else {
            // Use SKU-based product lookup
            $product_data = isset( $product_response['data'] ) ? $product_response['data'] : null;

            if ( $product_data && isset( $product_data['prices'] ) && is_array( $product_data['prices'] ) ) {
                // Find price for the user's currency
                $target_price = null;
                foreach ( $product_data['prices'] as $price_info ) {
                    if ( $price_info['currency'] === $currency ) {
                        $target_price = $price_info;
                        break;
                    }
                }

                if ( $target_price ) {
                    // API returns prices in display format, no conversion needed
                    $display_price = $target_price['price'];
                    wp_send_json_success( array(
                        'price' => $display_price,
                        'currency' => $currency,
                        'formatted_price' => $this->format_price( $display_price, $currency ),
                        'product_name' => $product_data['name'],
                        'country' => $country,
                        'fallback' => false
                    ) );
                } else {
                    // Currency not found, use updated fallback pricing
                    wp_send_json_success( array(
                        'price' => $currency === 'IDR' ? 99000 : 6,
                        'currency' => $currency,
                        'formatted_price' => $currency === 'IDR' ? 'Rp 99.000' : '$6.00',
                        'product_name' => $product_data['name'],
                        'country' => $country,
                        'fallback' => true,
                        'message' => 'Price for your currency not found, showing default pricing'
                    ) );
                }
            } else {
                // Product structure unexpected, use fallback
                wp_send_json_success( array(
                    'price' => $currency === 'IDR' ? 99000 : 6,
                    'currency' => $currency,
                    'formatted_price' => $currency === 'IDR' ? 'Rp 99.000' : '$6.00',
                    'product_name' => isset( $product_data['name'] ) ? $product_data['name'] : 'WPaigen Pro',
                    'country' => $country,
                    'fallback' => true
                ) );
            }
        }
    }

    /**
     * Format price based on currency
     *
     * @param float $price Price amount
     * @param string $currency Currency code
     * @return string Formatted price
     */
    private function format_price( $price, $currency ) {
        switch ( $currency ) {
            case 'IDR':
                return 'Rp ' . number_format( $price, 0, ',', '.' );
            case 'USD':
                return '$' . number_format( $price, 2, '.', ',' );
            default:
                return $currency . ' ' . number_format( $price, 2, '.', ',' );
        }
    }

    public function display_schedule_page() {
        include_once WPAIGEN_DIR . 'admin/views/wpaigen-schedule.php';
    }
}
