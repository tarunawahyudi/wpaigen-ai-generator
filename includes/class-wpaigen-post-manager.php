<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPaigen_Post_Manager {

    public function create_ai_post( $api_response, $use_featured_image = true ) {
        $post_title   = isset( $api_response['title'] ) ? sanitize_text_field( $api_response['title'] ) : 'Generated Article';
        $post_content = isset( $api_response['content'] ) ? wp_kses_post( $api_response['content'] ) : '';
        $seo_data     = isset( $api_response['seo'] ) ? $api_response['seo'] : array();

        $post_data = array(
            'post_title'   => $post_title,
            'post_content' => $post_content,
            'post_status'  => 'draft',
            'post_type'    => 'post',
            'post_name'    => isset( $seo_data['slug'] ) ? sanitize_title( $seo_data['slug'] ) : sanitize_title( $post_title ),
            'post_excerpt' => isset( $seo_data['excerpt'] ) ? sanitize_text_field( $seo_data['excerpt'] ) : '',
        );

        $post_id = wp_insert_post( $post_data, true );

        if ( is_wp_error( $post_id ) ) {
            return new WP_Error( 'wpaigen_post_error', 'Failed to create WordPress post: ' . $post_id->get_error_message() );
        }

        if ( ! empty( $seo_data ) ) {
            update_post_meta( $post_id, '_wpaigen_meta_title', sanitize_text_field( $seo_data['meta_title'] ?? '' ) );
            update_post_meta( $post_id, '_wpaigen_meta_description', sanitize_text_field( $seo_data['meta_description'] ?? '' ) );

            $this->set_seo_meta_for_plugins( $post_id, $seo_data );
        }

        if ( $use_featured_image && ! empty( $api_response['featured_image'] ) ) {
            $this->set_featured_image_from_url( $post_id, $api_response['featured_image'] );
        }

        return $post_id;
    }

    private function set_featured_image_from_url( $post_id, $image_url ) {

        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        $image_id = media_sideload_image( $image_url, $post_id, null, 'id' );

        if ( ! is_wp_error( $image_id ) ) {
            set_post_thumbnail( $post_id, $image_id );
        }
    }

    private function set_seo_meta_for_plugins( $post_id, $seo_data ) {
        if ( class_exists( 'WPSEO_Meta' ) ) {
            if ( ! empty( $seo_data['meta_title'] ) ) {
                update_post_meta( $post_id, '_yoast_wpseo_title', $seo_data['meta_title'] );
            }
            if ( ! empty( $seo_data['meta_description'] ) ) {
                update_post_meta( $post_id, '_yoast_wpseo_metadesc', $seo_data['meta_description'] );
            }
        }
        if ( defined( 'RANK_MATH_VERSION' ) ) {
            if ( ! empty( $seo_data['meta_title'] ) ) {
                update_post_meta( $post_id, 'rank_math_title', $seo_data['meta_title'] );
            }
            if ( ! empty( $seo_data['meta_description'] ) ) {
                update_post_meta( $post_id, 'rank_math_description', $seo_data['meta_description'] );
            }
        }
    }
}
