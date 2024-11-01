<?php
if (!defined('ABSPATH')) {
    exit;
}

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UrubutoPay_PaymentRoute
{

    private $payment_detail_meta_key = URUBUTOPAY_META['PAYMENT_DETAILS'];

    private $payment_service;

    private $validation;

    private $helper;

    public function __construct()
    {
        $this->payment_service = new UrubutoPay_PaymentService();

        $this->validation = new UrubutoPay_Validation();

        $this->helper = new UrubutoPay_Helper();

        add_action('rest_api_init', array($this, 'urubutopay_register_payment_route'));
    }

    public function urubutopay_register_payment_route()
    {
        $namespace = 'urubutopay';

        register_rest_route(
            $namespace,
            "/create",
            array(
                'methods' => 'POST',
                'callback' => array($this, 'urubutopay_initiate_payment'),
                'permission_callback' => '__return_true'
            )
        );

        register_rest_route(
            $namespace,
            "/read",
            array(
                'methods' => 'GET',
                'callback' => array($this, 'urubutopay_get_payment_meta'),
                'permission_callback' => '__return_true'
            )
        );

        register_rest_route(
            $namespace,
            '/transaction/check',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'urubutopay_check_transaction'),
                'permission_callback' => '__return_true'
            ),
        );

        register_rest_route(
            $namespace,
            '/payment/callback',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'urubutopay_payment_callback'),
                'permission_callback' => '__return_true'
            ),
        );

        register_rest_route(
            $namespace,
            '/authenticate',
            array(
                'methods' => 'POST',
                'callback' => array($this, 'urubutopay_authenticate'),
                'permission_callback' => '__return_true'
            )
        );
    }

    public function urubutopay_initiate_payment($request)
    {
        try {
            /**
             * body payload
             * productId
             * payer name
             * phone number
             * channel_name
             */
            $body = json_decode($request->get_body());

            $validate = $this->validation->urubutopay_validate_payment_input($body); // run validation

            if (count($validate) > 0) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['BAD_REQUEST'],
                    'validation error',
                    array(
                        'errors' => $validate,
                        'status' => URUBUTOPAY_HTTP_CODE['BAD_REQUEST']
                    )
                );
            }

            $product = get_post($body->product_id);
            if (null === $product) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['NOT_FOUND'],
                    'product not found',
                    array('status' => URUBUTOPAY_HTTP_CODE['NOT_FOUND'])
                );
            }

            $amount = get_post_meta($product->ID, URUBUTOPAY_META_BOX['PRICE'], true);
            if (!isset($amount) || empty($amount)) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['BAD_REQUEST'],
                    'amount must be provided',
                    array('status' => URUBUTOPAY_HTTP_CODE['BAD_REQUEST'])
                );
            }

            $options = get_option(URUBUTOPAY_OPTIONS["SETTING"]);

            $merchant_code = $options[URUBUTOPAY_OPTION_FIELD['MERCHANT_CODE']];

            $service_code = $options[URUBUTOPAY_OPTION_FIELD['SERVICE_CODE']];

            $api_key = $options[URUBUTOPAY_OPTION_FIELD['API_KEY']];

            // check if merchant code and service code and api key are not available
            if (!isset($merchant_code) && !isset($service_code)) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['SERVICE_UNAVAILABLE'],
                    'unable to initiate payment, contact support',
                    array('status' => URUBUTOPAY_HTTP_CODE['SERVICE_UNAVAILABLE'])
                );
            }

            if (empty($merchant_code) || empty($service_code) || empty($api_key)) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['SERVICE_UNAVAILABLE'],
                    'unable to initiate payment, contact support',
                    array('status' => URUBUTOPAY_HTTP_CODE['SERVICE_UNAVAILABLE'])
                );
            }

            $meta_value = array(
                'payer_names' => $body->payer_names,
                'phone_number' => $body->phone_number,
                'channel_name' => $body->channel_name,
                'amount' => $amount,
                'transaction_status' => URUBUTOPAY_TRANSACTION_STATUS['PENDING'],
                'payer_code' => $this->helper->urubutopay_generate_payer_code(),
                'product_id' => $product->ID,
                'transaction_id' => $this->helper->urubutopay_generate_transaction_id(),
            );

            $init = $this->payment_service->urubutopay_initiate_payment($meta_value, $body->rdurl);

            if ($init['code'] !== URUBUTOPAY_HTTP_CODE['OK']) {
                $errorMessage = isset($init['data']) && isset($init['data']->message) ?
                    $init['data']->message : 'unable to initiate payment';

                return new WP_Error($init['code'], $errorMessage, array(
                    'status' => $init['code'],
                    'errors' => $init['data']
                ));
            }

            // create payment post
            $post_id = wp_insert_post(array(
                'post_title' => $meta_value['transaction_id'],
                'post_type' => URUBUTOPAY_POST_TYPE["PAYMENT"],
                'post_status' => 'publish'
            ));

            if ($post_id) {
                add_post_meta($post_id, $this->payment_detail_meta_key, json_encode($meta_value));

                return rest_ensure_response(array(
                    'message' => 'payment initiated successfully',
                    'data' => array(
                        'transaction_id' => $meta_value['transaction_id'], 'post_id' => $post_id,
                        'message' =>  $init['data']->message,
                        'card_processing_url' => isset($init['data']->card_processing_url) ? $init['data']->card_processing_url : null
                    ),
                ));
            }

            return new WP_Error(
                URUBUTOPAY_HTTP_CODE['INTERNAL_SERVER_ERROR'],
                'unable to initiate payment',
                array('status' => URUBUTOPAY_HTTP_CODE['INTERNAL_SERVER_ERROR'])
            );
        } catch (\Throwable $th) {
            return new WP_Error(
                URUBUTOPAY_HTTP_CODE['INTERNAL_SERVER_ERROR'],
                'internal server error',
                array('status' => URUBUTOPAY_HTTP_CODE['INTERNAL_SERVER_ERROR'])
            );
        }
    }

    public function urubutopay_get_payment_meta()
    {
        $response = array('data' => array(array('name' => 'gratien')));
        return rest_ensure_response($response);
    }

    public function urubutopay_check_transaction($request)
    {
        $body = json_decode($request->get_body());

        $post = get_post($body->post_id);

        if (null === $post || !$post) {
            return new WP_Error(
                URUBUTOPAY_HTTP_CODE['NOT_FOUND'],
                'payment not found',
                array('status' => URUBUTOPAY_HTTP_CODE['NOT_FOUND'])
            );
        }

        $post_meta = get_post_meta($post->ID, URUBUTOPAY_META['PAYMENT_DETAILS'], true);

        if (null === $post_meta) {
            return new WP_Error(
                URUBUTOPAY_HTTP_CODE['NOT_FOUND'],
                'payment not found',
                array('status' => URUBUTOPAY_HTTP_CODE['NOT_FOUND'])
            );
        }

        if (
            json_decode($post_meta)->transaction_id === null || !json_decode($post_meta)->transaction_id ||
            json_decode($post_meta)->transaction_id !== $body->transaction_id
        ) {
            $msg = 'invalid transaction ID';
            return new WP_Error(
                URUBUTOPAY_HTTP_CODE['CONFLICT'],
                $msg,
                array('status' => URUBUTOPAY_HTTP_CODE['CONFLICT'], 'message' => $msg)
            );
        }

        // check transaction
        $check = $this->payment_service->urubutopay_check_transaction($body->transaction_id);

        if ($check['code'] !== URUBUTOPAY_HTTP_CODE['OK']) {
            return new WP_Error(
                $check['code'],
                'unable to check transaction',
                array('status' => $check['code'], 'retry' => true)
            );
        }

        $data = $check['data']->data ? $check['data']->data : null;

        if (null !== $data && $data->transaction_status && $data->transaction_id === $body->transaction_id) {
            $new_meta = json_decode($post_meta);
            $new_meta->transaction_status = $data->transaction_status ===
                'INITIATED' ? 'PENDING' : $data->transaction_status;

            update_post_meta($post->ID, URUBUTOPAY_META['PAYMENT_DETAILS'], json_encode($new_meta));
        }

        $transaction_status = $this->helper->handle_transaction_status_response($data);
        $response = array('transaction_status' => $transaction_status);
        return rest_ensure_response(array(
            'message' => 'successfuly',
            'data' => $response
        ));
    }

    public function urubutopay_payment_callback($request)
    {
        try {
            $body = json_decode($request->get_body());

            $token = $request->get_header('x_authorization');

            $unauth_message = 'unauthorized access';

            if (null === $token) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['UNAUTHORIZED'],
                    $unauth_message,
                    array('status' => URUBUTOPAY_HTTP_CODE['UNAUTHORIZED'], 'message' => $unauth_message)
                );
            }

            $explode = explode(" ", $token);

            if (count($explode) <= 0) {
                die(json_encode($explode));
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['UNAUTHORIZED'],
                    $unauth_message,
                    array('status' => URUBUTOPAY_HTTP_CODE['UNAUTHORIZED'], 'message' => $unauth_message)
                );
            }

            $pickToken = count($explode) === 1 ? $explode[0] : $explode[1];

            $option = get_option(URUBUTOPAY_OPTIONS['SETTING']);

            $decode = JWT::decode($pickToken, new Key(
                $option[URUBUTOPAY_OPTION_FIELD['SECRET_KEY']],
                URUBUTOPAY_JWT_ALGORITHM
            ));

            // check if decoded username from token is not equal to the one from setting page
            if ($decode->user_name !== $option[URUBUTOPAY_OPTION_FIELD['USERNAME']]) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['UNAUTHORIZED'],
                    $unauth_message,
                    array('status' => URUBUTOPAY_HTTP_CODE['UNAUTHORIZED'], 'message' => $unauth_message)
                );
            }
            // validate transaction id, external transaction id, transaction status
            $validate = $this->validation->urubutopay_validate_payment_callback($body);
            if (count($validate) > 0) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['BAD_REQUEST'],
                    'validation error',
                    array(
                        'status' => URUBUTOPAY_HTTP_CODE['BAD_REQUEST'], 'message' => 'validation error',
                        'errors' => $validate
                    )
                );
            }

            $posts = get_posts([
                'title' => $body->transaction_id,
                'post_type' => URUBUTOPAY_POST_TYPE['PAYMENT']
            ]);

            $not_found_msg = 'payment not found';

            if (count($posts) > 1) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['NOT_FOUND'],
                    $not_found_msg,
                    array(
                        'status' => URUBUTOPAY_HTTP_CODE['NOT_FOUND'],
                        'message' => $not_found_msg
                    )
                );
            }

            $post = $posts[0];

            $post_meta = get_post_meta($post->ID, URUBUTOPAY_META['PAYMENT_DETAILS'], true);

            if (null === $post_meta || !isset($post_meta)) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['NOT_FOUND'],
                    $not_found_msg,
                    array(
                        'status' => URUBUTOPAY_HTTP_CODE['NOT_FOUND'],
                        'message' => $not_found_msg
                    )
                );
            }

            $decode = json_decode($post_meta);

            if ($decode->transaction_id !== $body->transaction_id) {
                $msg = 'invalid transaction ID';
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['CONFLICT'],
                    $msg,
                    array('status' => URUBUTOPAY_HTTP_CODE['CONFLICT'], 'message' => $msg)
                );
            }
            $decode->external_transaction_id = $body->internal_transaction_id;
            $decode->transaction_status = $body->transaction_status;
            $decode->transaction_date = $body->payment_date_time;

            update_post_meta($post->ID, URUBUTOPAY_META['PAYMENT_DETAILS'], json_encode($decode));

            $response = array(
                'internal_transaction_id' => $decode->transaction_id,
                'external_transaction_id' => $decode->external_transaction_id,
                'payer_phone_number' => $decode->phone_number
            );
            return rest_ensure_response(array('data' => $response));
        } catch (\Throwable $th) {
            return new WP_Error(
                URUBUTOPAY_HTTP_CODE['INTERNAL_SERVER_ERROR'],
                'Internal server error',
                array(
                    'status' => URUBUTOPAY_HTTP_CODE['INTERNAL_SERVER_ERROR'],
                    'message' => 'Internal server error'
                )
            );
        }
    }


    public function urubutopay_authenticate($request)
    {
        try {
            $body = json_decode($request->get_body());

            // validation
            $validate = $this->validation->urubutopay_validate_auth($body);
            if ($validate && count($validate) > 0) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['BAD_REQUEST'],
                    'validation error',
                    array('status' => URUBUTOPAY_HTTP_CODE['BAD_REQUEST'], 'errors' => $validate)
                );
            }

            $option = get_option(URUBUTOPAY_OPTIONS['SETTING']);
            if (
                !isset($option[URUBUTOPAY_OPTION_FIELD['USERNAME']]) ||
                !isset($option[URUBUTOPAY_OPTION_FIELD['PASSWORD']]) ||
                !isset($option[URUBUTOPAY_OPTION_FIELD['SECRET_KEY']])
            ) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['UNAUTHORIZED'],
                    'incorrect username and password',
                    array('status' => URUBUTOPAY_HTTP_CODE['UNAUTHORIZED'])
                );
            }

            if (
                $body->user_name !== $option[URUBUTOPAY_OPTION_FIELD['USERNAME']] ||
                $body->password !== $option[URUBUTOPAY_OPTION_FIELD['PASSWORD']]
            ) {
                return new WP_Error(
                    URUBUTOPAY_HTTP_CODE['UNAUTHORIZED'],
                    'incorrect username and password',
                    array('status' => URUBUTOPAY_HTTP_CODE['UNAUTHORIZED'])
                );
            }

            $jwt = JWT::encode(
                array(
                    "user_name" => $body->user_name,
                    "exp" => time() + (60 * 60) // expire in 1 hour
                ),
                $option[URUBUTOPAY_OPTION_FIELD['SECRET_KEY']],
                URUBUTOPAY_JWT_ALGORITHM
            );

            return rest_ensure_response(array(
                'data' => array(
                    'token' => 'Bearer ' . $jwt,
                    'message' => 'authenticated successfully',
                )
            ));
        } catch (\Throwable $th) {
            return new WP_Error(
                URUBUTOPAY_HTTP_CODE['INTERNAL_SERVER_ERROR'],
                'Internal server error',
                array('status' => URUBUTOPAY_HTTP_CODE['INTERNAL_SERVER_ERROR'])
            );
        }
    }
}
