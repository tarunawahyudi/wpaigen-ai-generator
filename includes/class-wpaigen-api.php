<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPaigen_Api {

    private $base_url;

    public function __construct() {
        $this->base_url = WPAIGEN_BASE_API_URL;
    }

    private function _send_request( $endpoint, $method = 'POST', $body = null, $headers = array() ) {
        $url = trailingslashit( $this->base_url ) . $endpoint;

        // Always use browser-like User Agent to avoid API discrimination
        // This ensures both web and cron requests look identical to the API server
        $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

        // Add random cache buster and unique identifiers to avoid API caching/rate limiting
        $cache_buster = md5( microtime( true ) . rand( 1000, 9999 ) );
        $request_id = uniqid( 'wpaigen_', true );

        $args = array(
            'method'    => $method,
            'timeout'   => 45, // Increased timeout for better reliability
            'blocking'  => true,
            'headers'   => array_merge(
                array(
                    'Content-Type' => 'application/json',
                    'User-Agent'   => $user_agent,
                    'X-Requested-With' => 'XMLHttpRequest',
                    'X-WPaigen-Request-ID' => $request_id,
                    'X-WPaigen-Cache-Buster' => $cache_buster,
                    'Accept' => 'application/json, text/plain, */*',
                    'Accept-Language' => 'en-US,en;q=0.9,id;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Connection' => 'keep-alive',
                    'Referer' => home_url( '/wp-admin/' ),
                ),
                $headers
            ),
            'sslverify' => false,
            'data_format' => 'body',
        );

        // Add cache buster to URL if it's a GET request
        if ( $method === 'GET' ) {
            $url = add_query_arg( 'cb', $cache_buster, $url );
        }

        if ( $body ) {
            $args['body'] = wp_json_encode( $body );
        }

        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'wpaigen_api_error', $response->get_error_message() );
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $response_body, true );

        if ( $response_code >= 200 && $response_code < 300 ) {
            return $data;
        } else {
            return new WP_Error( 'wpaigen_api_error_' . $response_code, isset( $data['error'] ) ? $data['error'] : 'Unknown API error.', $data );
        }
    }

     public function register_free_license( $domain, $email ) {
        $body = array(
            'domain'    => $domain,
            'email'     => $email,
        );
        return $this->_send_request( 'api/license/register', 'POST', $body );
    }

    public function activate_license( $license_key, $domain ) {
        $body = array(
            'license_key' => $license_key,
            'domain'      => $domain,
        );
        return $this->_send_request( 'api/license/activate', 'POST', $body );
    }

    public function validate_license( $license_key, $domain ) {
        $headers = array(
            'Authorization' => 'Bearer ' . $license_key,
        );
        $body = array ('domain' => $domain);
        return $this->_send_request( 'api/license/validate', 'POST', $body, $headers );
    }

    public function generate_article( $license_key, $keyword, $language, $length, $tone ) {
        $headers = array(
            'Authorization' => 'Bearer ' . $license_key,
        );
        $body = array(
            'keyword'  => $keyword,
            'language' => $language,
            'length'   => (int) $length,
            'tone'     => $tone,
        );
        return $this->_send_request( 'api/articles/generate', 'POST', $body, $headers );
    }

    public function create_transaction( $email, $domain ) {
        $body = array(
            'email' => $email,
            'domain' => $domain,
        );
        return $this->_send_request( 'api/transactions/create', 'POST', $body );
    }

    public function get_google_trends( $license_key ) {
        $headers = array(
            'Authorization' => 'Bearer ' . $license_key,
        );
        return $this->_send_request( 'api/google-trends/trending', 'GET', null, $headers );
    }
}
