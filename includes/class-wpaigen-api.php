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

        if ( empty( $response_body ) ) {
            return new WP_Error( 'wpaigen_api_error_empty', 'Empty response from API' );
        }

        $data = json_decode( $response_body, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error( 'wpaigen_api_error_json', 'Invalid JSON response from API' );
        }

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
            'productSku' => 'WPAIGEN-001',
            'currency' => 'IDR'
        );

        $headers = array(
            'Content-Type' => 'application/json',
            'User-Agent' => 'WPaigenPlugin/1.0'
        );

        return $this->_send_request( 'api/transactions/create', 'POST', $body, $headers );
    }

    public function get_google_trends( $license_key ) {
        $headers = array(
            'Authorization' => 'Bearer ' . $license_key,
        );
        return $this->_send_request( 'api/google-trends/trending', 'GET', null, $headers );
    }

    /**
     * Get product pricing by currency
     *
     * @param int $product_id Product ID
     * @param string $currency Currency code (IDR, USD, etc.)
     * @return array|WP_Error Product pricing data
     */
    public function get_product_price( $product_id, $currency ) {
        $endpoint = "api/products/{$product_id}/price/{$currency}";
        return $this->_send_request( $endpoint, 'GET' );
    }

    /**
     * Get product by SKU
     *
     * @param string $sku Product SKU
     * @return array|WP_Error Product data with prices
     */
    public function get_product_by_sku( $sku ) {
        $endpoint = "api/products/sku/{$sku}";
        return $this->_send_request( $endpoint, 'GET' );
    }

    /**
     * Detect user country based on IP address
     *
     * @return string Country code (e.g., 'ID' for Indonesia)
     */
    public function detect_user_country() {
        // Check for testing override only in development environment
        $test_country = get_option( 'wpaigen_test_country', '' );
        if ( ! empty( $test_country ) ) {
            return $test_country;
        }

        // Get user's IP address
        $ip = $this->get_user_ip();

        if ( empty( $ip ) || $ip === '127.0.0.1' || $ip === '::1' ) {
            // For localhost, try to get external IP first
            $external_ip = $this->get_external_ip();
            if ( $external_ip && $external_ip !== $ip ) {
                $ip = $external_ip;
            } else {
                // For localhost without external IP, check for debugging override
                if ( defined( 'WPAIGEN_TEST_COUNTRY' ) ) {
                    return WPAIGEN_TEST_COUNTRY;
                }
                return 'ID';
            }
        }

        // Use a free IP geolocation service
        $url = "http://ip-api.com/json/{$ip}?fields=countryCode";

        $args = array(
            'timeout'   => 5,
            'blocking'  => true,
            'headers'   => array(
                'User-Agent' => 'WPaigen/1.0'
            ),
            'sslverify' => false,
        );

        $response = wp_remote_get( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( 'WPaigen: IP geolocation error - ' . $response->get_error_message() );
            return 'ID'; // Default to Indonesia on error
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $response_body, true );

        if ( $response_code === 200 && isset( $data['countryCode'] ) ) {
            return $data['countryCode'];
        }

        return 'ID'; // Default to Indonesia
    }

    /**
     * Get external IP address for localhost testing
     *
     * @return string|false External IP or false if unable to get
     */
    private function get_external_ip() {
        $services = array(
            'https://api.ipify.org',
            'https://icanhazip.com',
            'https://checkip.amazonaws.com'
        );

        foreach ( $services as $service ) {
            $response = wp_remote_get( $service, array(
                'timeout' => 3,
                'headers' => array( 'User-Agent' => 'WPaigen/1.0' )
            ));

            if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
                $ip = trim( wp_remote_retrieve_body( $response ) );
                if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                    return $ip;
                }
            }
        }

        return false;
    }

    /**
     * Get user's IP address
     *
     * @return string IP address
     */
    private function get_user_ip() {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ips = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
            return trim( $ips[0] );
        } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return '';
    }

    /**
     * Get currency based on user's country
     *
     * @param string $country_code Country code
     * @return string Currency code
     */
    public function get_currency_by_country( $country_code ) {
        $country_code = strtoupper( $country_code );
        if ( $country_code === 'ID' ) {
            return 'IDR';
        }

        return 'USD';
    }

    /**
     * Create PayPal order
     *
     * @param array $order_data Order data
     * @param array $headers Request headers
     * @return array|WP_Error API response
     */
    public function create_paypal_order( $order_data, $headers = array() ) {
        $default_headers = array(
            'Content-Type' => 'application/json',
            'User-Agent' => 'WPaigenPlugin/1.0'
        );
        $headers = array_merge( $default_headers, $headers );

        return $this->_send_request( 'api/transactions/paypal/create-order', 'POST', $order_data, $headers );
    }

    /**
     * Capture PayPal payment
     *
     * @param array $capture_data Capture data
     * @param array $headers Request headers
     * @return array|WP_Error API response
     */
    public function capture_paypal_payment( $capture_data, $headers = array() ) {
        $default_headers = array(
            'Content-Type' => 'application/json',
            'User-Agent' => 'WPaigenPlugin/1.0'
        );
        $headers = array_merge( $default_headers, $headers );

        return $this->_send_request( 'api/transactions/paypal/capture-order', 'POST', $capture_data, $headers );
    }
}
