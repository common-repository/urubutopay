<?php
if (!defined('ABSPATH')) {
    exit;
}

class UrubutoPay_Helper
{
    public function urubutopay_generate_transaction_id()
    {
        $timestamp = getdate()[0];

        return 'WP-PLUGIN-' . $timestamp;
    }

    public function urubutopay_generate_payer_code()
    {
        //
        $prefix = 'WP-UPG';
        $timestamp = getdate()[0];
        return $prefix . '-' . $timestamp;
    }

    public function handle_transaction_status_response($args)
    {
        if ($args->transaction_status === URUBUTOPAY_TRANSACTION_STATUS['INITIATED']) {
            return URUBUTOPAY_TRANSACTION_STATUS['PENDING'];
        }

        if ($args->transansaction_status === URUBUTOPAY_TRANSACTION_STATUS['PENDING_SETTLEMENT']) {
            return URUBUTOPAY_TRANSACTION_STATUS['VALID'];
        }

        return $args->transaction_status;
    }
}
