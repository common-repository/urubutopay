<?php
if (!defined('ABSPATH')) {
    exit;
}

class UrubutoPay_Script
{
    public function __construct()
    {
        //load script
        add_action('wp_enqueue_scripts', array($this, 'urubutopay_register_assets'));
    }

    public function urubutopay_register_assets()
    {
        //BackBone JS 
        wp_enqueue_script('wp-api');

        //JQuery
        wp_enqueue_script('jquery', null, null, null, true);

        //customscript
        wp_register_style('custom-css', plugins_url('../../public/css/styles.css', __FILE__), null, null, false);
        wp_enqueue_style('custom-css');

        // custom script
        wp_register_script('urubuto-pay-now-script', plugins_url('../../public/js/pay.js', __FILE__), null, null, true);
        wp_enqueue_script('urubuto-pay-now-script');
        wp_localize_script(
            'urubuto-pay-now-script',
            'Assets',
            array(
                'failed-icon' => plugins_url('../../public/images/payment/failed.png', __FILE__),
                'success-icon' => plugins_url('../../public/images/payment/success.png', __FILE__),
                'loading-icon-blue' => plugins_url('../../public/images/spinner/blue.svg', __FILE__),
                'close-icon' => plugins_url('../../public/images/payment/close.png', __FILE__)
            )
        );
        wp_localize_script(
            'urubuto-pay-now-script',
            'TransactionStatus',
            array(
                'PENDING' => URUBUTOPAY_TRANSACTION_STATUS['PENDING'],
                'INITIATED' => URUBUTOPAY_TRANSACTION_STATUS['INITIATED'],
                'VALID' => URUBUTOPAY_TRANSACTION_STATUS['VALID'],
                'FAILED' => URUBUTOPAY_TRANSACTION_STATUS['FAILED'],
                'PENDING_SETTLEMENT' => URUBUTOPAY_TRANSACTION_STATUS['PENDING_SETTLEMENT'],
                'CANCELED' => URUBUTOPAY_TRANSACTION_STATUS['CANCELED'],
            )
        );
        wp_localize_script(
            'urubuto-pay-now-script',
            'PaymentChannel',
            array(
                'MOMO' => URUBUTOPAY_PAYMENT_CHANNEL['MOMO'],
                'AIRTEL_MONEY' => URUBUTOPAY_PAYMENT_CHANNEL['AIRTEL_MONEY'],
                'CARD' => URUBUTOPAY_PAYMENT_CHANNEL['CARD']
            )
        );
    }
}
