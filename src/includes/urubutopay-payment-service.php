<?php
if (!defined('ABSPATH')) {
    exit;
}

class UrubutoPay_PaymentService
{
    private $merchant_code;

    private $service_code;

    private $token;

    private $apiBaseUrl;

    public function __construct()
    {
        $options = get_option(URUBUTOPAY_OPTIONS['SETTING']);

        $this->merchant_code = isset($options[URUBUTOPAY_OPTION_FIELD['MERCHANT_CODE']]) ?
            $options[URUBUTOPAY_OPTION_FIELD['MERCHANT_CODE']] : null;

        $this->service_code = isset($options[URUBUTOPAY_OPTION_FIELD['SERVICE_CODE']]) ?
            $options[URUBUTOPAY_OPTION_FIELD['SERVICE_CODE']] : null;

        $this->token = isset($options[URUBUTOPAY_OPTION_FIELD['API_KEY']]) ? 'Bearer ' . $options[URUBUTOPAY_OPTION_FIELD['API_KEY']] : null;

        $this->apiBaseUrl = isset($options[URUBUTOPAY_OPTION_FIELD['BASE_URL']]) ? $options[URUBUTOPAY_OPTION_FIELD['BASE_URL']] : null;
    }

    public function urubutopay_initiate_payment($args, $rdurl)
    {

        $endpoint = $this->apiBaseUrl . URUBUTOPAY_PAYMENT_SERVICE_API_ENDPOINT['INITIATE_PAYMENT'];

        $redirection_url = '';

        if ($rdurl && !empty($rdurl)) {
            $redirection_url = $rdurl;
        }

        $body = array_merge($args, array(
            'merchant_code' => $this->merchant_code,
            'service_code' => $this->service_code,
            'payment_initiator_type' => URUBUTOPAY_PAYMENT_INITIATOR_TYPE,
            'redirection_url' => $redirection_url
        ));

        $response = wp_remote_post($endpoint, array(
            'body' => json_encode($body),
            'headers' => array(
                'authorization' => $this->token,
                'Content-Type' => 'application/json'
            )
        ));

        return $this->format_response($response);
    }

    public function urubutopay_check_transaction($transaction_id)
    {
        $url = $this->apiBaseUrl . URUBUTOPAY_PAYMENT_SERVICE_API_ENDPOINT['CHECK_TRANSACTION'];

        $response = wp_remote_post($url, array('body' =>
        array(
            'transaction_id' => $transaction_id,
            'merchant_code' => $this->merchant_code
        ), 'headers' => array('authorization' => $this->token)));

        return $this->format_response($response);
    }

    public static function format_response($response)
    {
        $code = wp_remote_retrieve_response_code($response);

        $body = wp_remote_retrieve_body($response);

        return array('code' => $code, 'data' => json_decode($body));
    }

    public function urubutopay_get_merchant_detail()
    {
        $merchant_code = $this->merchant_code;

        $uri = $this->apiBaseUrl . URUBUTOPAY_PAYMENT_SERVICE_API_ENDPOINT['VALIDATION'];

        $body = array('merchant_code' => $merchant_code, 'payer_code' => 'N/A');

        $response = wp_remote_post($uri, array(
            'body' => json_encode($body),
            'headers' => array('Content-Type' => 'application/json')
        ));

        return UrubutoPay_PaymentService::format_response($response);
    }
}
