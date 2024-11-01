<?php
if (!defined('ABSPATH')) {
    exit;
}

class UrubutoPay_Payment
{
    public function __construct()
    {
        $post_type = URUBUTOPAY_POST_TYPE['PAYMENT'];

        add_action(
            'init',
            array($this, 'urubutopay_register_payment_post_type')
        );
        add_filter(
            "manage_{$post_type}_posts_columns",
            array($this, 'urubutopay_register_payment_columns')
        );
        add_action(
            "manage_{$post_type}_posts_custom_column",
            array($this, 'urubutopay_display_payment_columns'),
            10,
            2
        );
    }

    public function urubutopay_register_payment_post_type()
    {
        $labels = array(
            'name' => 'Payments', 'singular_name' => 'Payment',
            'add_new' => 'Add New Payment',
            'add_new_item' => 'Add Payment',
            'edit_item' => 'Edit Payment',
            'not_found' => 'Payments not found'
        );

        register_post_type(URUBUTOPAY_POST_TYPE['PAYMENT'], array(
            'labels' => $labels, 'description' => 'Payments',
            'show_in_menu' => 'edit.php/?post_type=' . URUBUTOPAY_POST_TYPE['PAYMENT'],
            'show_in_rest' => true, // show in rest api
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'rewrite' => array('slug' => 'upgpayments'),
            'show_in_admin_bar' => true,
            'archive' => true,
            'public' => true,
            'supports' => array('title'),
            'capabilities' => array('edit_post' => false)
        ));
    }

    public function urubutopay_register_payment_columns($columns)
    {
        $columns = array(
            'cb' => $columns['cb'],
            'title' => __('Transaction ID'),
            'transaction_date' => __('Transaction Date'),
            'external_transaction_id' => __('Ext Transaction ID'),
            'payer_names' => __('Payer Names'),
            'payer_code' => __('Payer Code'),
            'amount' => __('Amount'),
            'channel_name' => __('Payment Mode'),
            'transaction_status' => __('Transaction Status')
        );
        return $columns;
    }

    public function urubutopay_display_payment_columns($column, $post_id)
    {
        $meta = get_post_meta($post_id, URUBUTOPAY_META['PAYMENT_DETAILS'], true);

        if ($column === 'channel_name' && $meta) {
            $channel_name = isset(json_decode($meta)->channel_name) ? json_decode($meta)->channel_name : 'N/A';
            echo esc_html($channel_name);
        }

        if ($column === 'transaction_date' && $meta) {
            $transaction_date = isset(json_decode($meta)->transaction_date) ? json_decode($meta)->transaction_date : 'N/A';
            echo esc_html($transaction_date);
        }

        if ($column === 'amount') {
            $amount = json_decode($meta)->amount;
            echo 'RWF ' . esc_html($amount);
        }

        if ($column === 'transaction_status') {
            $transaction_status = json_decode($meta)->transaction_status;
            echo esc_html($transaction_status);
        }

        if ($column === 'payer_names') {
            $payer_names = json_decode($meta)->payer_names;
            echo esc_html($payer_names);
        }

        if ($column === 'payer_code') {
            $payer_code = json_decode($meta)->payer_code;
            echo esc_html($payer_code);
        }

        if ($column === 'transaction_id') {
            $trxid = isset(json_decode($meta)->transaction_id) ?
                json_decode($meta)->transaction_id : 'N/A';
            echo esc_html($trxid);
        }

        if ($column === 'external_transaction_id') {
            $txid = isset(json_decode($meta)->external_transaction_id) ?
                json_decode($meta)->external_transaction_id : 'N/A';
            echo esc_html($txid);
        }
    }
}
