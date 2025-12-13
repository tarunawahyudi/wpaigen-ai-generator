<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const WPAIGEN_BASE_API_URL = 'https://wpaigen-api.stacklab.id';
const WPAIGEN_MIDTRANS_CLIENT_KEY = 'Mid-client-f_jUk6xl3gsdvIk3';
const WPAIGEN_MIDTRANS_SNAP_URL= 'https://api.midtrans.com/snap/snap.js';
const WPAIGEN_PAYPAL_ENVIRONMENT = 'production'; // 'sandbox' for development, 'production' for live
const WPAIGEN_PAYPAL_SANDBOX_CLIENT_ID = 'AUpYz2wybD0jmzF0d5FD6K1_rMD9Mp-7Gt0LF7N2T1fpZx1Ui4OGT9gCTWh7lnFddpAy05_gEeOqYcCC'; // Development/Sandbox
const WPAIGEN_PAYPAL_PRODUCTION_CLIENT_ID = 'AZWebOuQvvWdVuNFg6ngoQYe8awvgxUKhcx99ipNpGkWiAjOLNaJI2juRaMCOcDVwIV2MpqQ1dzqUfPD'; // Add production client ID here when ready for live
const WPAIGEN_PAYPAL_CLIENT_ID = WPAIGEN_PAYPAL_ENVIRONMENT === 'production'
    ? WPAIGEN_PAYPAL_PRODUCTION_CLIENT_ID
    : WPAIGEN_PAYPAL_SANDBOX_CLIENT_ID;