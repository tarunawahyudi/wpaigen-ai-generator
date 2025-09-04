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

        $args = array(
            'method'    => $method,
            'timeout'   => 30, // seconds
            'blocking'  => true,
            'headers'   => array_merge(
                array(
                    'Content-Type' => 'application/json',
                ),
                $headers
            ),
            'sslverify' => false,
            'data_format' => 'body',
        );

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
}
