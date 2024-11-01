<?php
if (!defined('ABSPATH')) {
    exit;
}

define('URUBUTOPAY_POST_TYPE', array(
    'PAYMENT' => 'urubutopay_payments',
    'PRODUCT' => 'urubutopay_products',
    'PAGE' => 'page'
));

define('URUBUTOPAY_OPTIONS', array('SETTING' => 'urubutopay_options'));

define(
    'URUBUTOPAY_OPTION_FIELD',
    array(
        'MERCHANT_CODE' => 'urubutopay_merchant_code_setting_field',
        'SERVICE_CODE' => 'urubutopay_service_code_setting_field',
        'API_KEY' => 'urubutopay_api_key_setting_field',
        'BUTTON_NAME' => 'urubutopay_buy_button_setting_field',
        'USERNAME' => 'urubutopay_auth_username_setting_field',
        'PASSWORD' => 'urubutopay_auth_password_setting_field',
        'SECRET_KEY' => 'urubutopay_auth_secret_key_setting_field',
        'BASE_URL' => 'urubutopay_base_url_setting_field'
    )
);

define('URUBUTOPAY_META_BOX', array('PRICE' => 'urubutopay_price'));

define('URUBUTOPAY_META', array('PAYMENT_DETAILS' => 'urubutopay_payment_details'));

define('URUBUTOPAY_PAYMENT_SERVICE_API_ENDPOINT', array(
    'INITIATE_PAYMENT' => '/api/payment/initiate-vubavuba',
    'CHECK_TRANSACTION' => '/api/payment/transaction/status',
    'VALIDATION' => '/api/payment/validate'
));

define('URUBUTOPAY_TRANSACTION_STATUS', array(
    'VALID' => 'VALID',
    'PENDING' => 'PENDING',
    'FAILED' => 'FAILED',
    'PENDING_SETTLEMENT' => 'PENDING_SETTLEMENT',
    'CANCELED' => 'CANCELED',
    'INITIATED' => 'INITIATED'
));

define('URUBUTOPAY_PAYMENT_CHANNEL', array('MOMO' => 'MOMO', 'AIRTEL_MONEY' => 'AIRTEL_MONEY', 'CARD' => 'CARD'));

define('URUBUTOPAY_JWT_ALGORITHM', 'HS256');

define('URUBUTOPAY_PAYMENT_INITIATOR_TYPE', 'ECOMMERCE_REQUEST');

define('URUBUTOPAY_PRODUCT_PAGE_NAME', array('PRODUCTS' => 'Products'));

define('URUBUTOPAY_PRODUCT_PAGE_SLUG', array('PRODUCTS' => 'upg-products-front'));

define('URUBUTOPAY_RESPONSE_STATUS', array(
    'YES' => 'YES',
    'NO' => 'NO'
));

define(
    'URUBUTOPAY_SHORTCODE',
    array(
        'SHOW_ALL_PRODUCTS' => 'urubutopay-show-products',
        'SHOW_BUY_BUTTON' => 'urubutopay-show-buy-button',
        'SHOW_PRODUCT' => 'urubutopay-show-product'
    )
);
