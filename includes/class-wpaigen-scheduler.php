<?php if ( ! defined( 'ABSPATH' ) ) exit;

class WPaigen_Scheduler {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wpaigen_scheduled_posts';

        add_action( 'init', array( $this, 'maybe_create_table' ) );
        add_action( 'wpaigen_process_scheduled_posts', array( $this, 'process_scheduled_posts' ) );

        // Add cron schedule if not exists
        add_filter( 'cron_schedules', array( $this, 'add_custom_cron_schedule' ) );

        // Schedule the cron event if not scheduled
        if ( ! wp_next_scheduled( 'wpaigen_process_scheduled_posts' ) ) {
            wp_schedule_event( time(), 'wpaigen_every_minute', 'wpaigen_process_scheduled_posts' );
        }
    }

    public function maybe_create_table() {
        global $wpdb;

        $current_version = get_option( 'wpaigen_scheduler_db_version', 0 );
        $target_version = '1.0';

        if ( version_compare( $current_version, $target_version, '<' ) ) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE {$this->table_name} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                post_id bigint(20) unsigned NULL,
                keyword varchar(255) NOT NULL,
                language varchar(50) NOT NULL,
                length int(11) NOT NULL,
                tone varchar(50) NOT NULL,
                use_featured_image tinyint(1) NOT NULL DEFAULT 0,
                scheduled_date datetime NOT NULL,
                status varchar(20) NOT NULL DEFAULT 'pending',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                error_message text NULL,
                PRIMARY KEY (id),
                KEY idx_status (status),
                KEY idx_scheduled_date (scheduled_date)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            update_option( 'wpaigen_scheduler_db_version', $target_version );
        }
    }

    public function add_custom_cron_schedule( $schedules ) {
        $schedules['wpaigen_every_minute'] = array(
            'interval' => 60, // 60 seconds
            'display'  => __( 'Every Minute (WPaigen)', 'wpaigen-ai-generator' )
        );
        return $schedules;
    }

    public function create_schedule( $data ) {
        global $wpdb;

        $result = $wpdb->insert(
            $this->table_name,
            array(
                'keyword' => sanitize_text_field( $data['keyword'] ),
                'language' => sanitize_text_field( $data['language'] ),
                'length' => absint( $data['length'] ),
                'tone' => sanitize_text_field( $data['tone'] ),
                'use_featured_image' => isset( $data['use_featured_image'] ) ? 1 : 0,
                'scheduled_date' => $data['scheduled_date'],
                'status' => 'pending'
            ),
            array(
                '%s', '%s', '%d', '%s', '%d', '%s', '%s'
            )
        );

        if ( false === $result ) {
            return new WP_Error( 'db_error', 'Failed to create schedule' );
        }

        return $wpdb->insert_id;
    }

    public function get_scheduled_posts( $status = 'all', $limit = 50, $offset = 0 ) {
        global $wpdb;

        $sql = "SELECT * FROM {$this->table_name}";
        $params = array();

        if ( $status !== 'all' ) {
            $sql .= " WHERE status = %s";
            $params[] = $status;
        }

        $sql .= " ORDER BY scheduled_date DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
    }

    public function get_scheduled_post( $id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id ),
            ARRAY_A
        );
    }

    public function update_schedule_status( $id, $status, $post_id = null, $error_message = null ) {
        global $wpdb;

        $update_data = array( 'status' => $status );
        $update_format = array( '%s' );

        if ( $post_id ) {
            $update_data['post_id'] = $post_id;
            $update_format[] = '%d';
        }

        if ( $error_message ) {
            $update_data['error_message'] = $error_message;
            $update_format[] = '%s';
        }

        return $wpdb->update(
            $this->table_name,
            $update_data,
            array( 'id' => $id ),
            $update_format,
            array( '%d' )
        );
    }

    public function delete_schedule( $id ) {
        global $wpdb;

        return $wpdb->delete(
            $this->table_name,
            array( 'id' => $id ),
            array( '%d' )
        );
    }

    public function process_scheduled_posts() {
        global $wpdb;

        $current_time = current_time( 'mysql' );

        $scheduled_posts = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->table_name}
             WHERE status = 'pending'
             AND scheduled_date <= %s
             LIMIT 10",
            $current_time
        ), ARRAY_A );

        if ( empty( $scheduled_posts ) ) {
            return;
        }

        $api_client = new WPaigen_Api();
        $post_manager = new WPaigen_Post_Manager();

        foreach ( $scheduled_posts as $scheduled_post ) {
            // Update status to processing
            $this->update_schedule_status( $scheduled_post['id'], 'processing' );

            try {
                // Generate article using API
                $license_key = get_option( 'wpaigen_license_key' );
                $response = $api_client->generate_article(
                    $license_key,
                    $scheduled_post['keyword'],
                    $scheduled_post['language'],
                    $scheduled_post['length'],
                    $scheduled_post['tone']
                );

                if ( is_wp_error( $response ) ) {
                    $this->update_schedule_status(
                        $scheduled_post['id'],
                        'failed',
                        null,
                        $response->get_error_message()
                    );
                    continue;
                }

                if ( isset( $response['success'] ) && $response['success'] ) {
                    // Create the post
                    $post_id = $post_manager->create_ai_post(
                        $response,
                        $scheduled_post['use_featured_image']
                    );

                    if ( is_wp_error( $post_id ) ) {
                        $this->update_schedule_status(
                            $scheduled_post['id'],
                            'failed',
                            null,
                            $post_id->get_error_message()
                        );
                        continue;
                    }

                    // Update the post to be published immediately instead of draft
                    wp_update_post( array(
                        'ID' => $post_id,
                        'post_status' => 'publish'
                    ) );

                    // Update schedule status
                    $this->update_schedule_status(
                        $scheduled_post['id'],
                        'published',
                        $post_id
                    );
                } else {
                    $this->update_schedule_status(
                        $scheduled_post['id'],
                        'failed',
                        null,
                        'Failed to generate article'
                    );
                }

            } catch ( Exception $e ) {
                $this->update_schedule_status(
                    $scheduled_post['id'],
                    'failed',
                    null,
                    $e->getMessage()
                );
            }
        }
    }

    public function get_scheduled_posts_count( $status = 'all' ) {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$this->table_name}";
        $params = array();

        if ( $status !== 'all' ) {
            $sql .= " WHERE status = %s";
            $params[] = $status;
        }

        return $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
    }

    public function cleanup() {
        // Clean up cron job
        wp_clear_scheduled_hook( 'wpaigen_process_scheduled_posts' );
    }
}