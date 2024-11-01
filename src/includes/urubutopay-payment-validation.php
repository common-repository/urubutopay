<?php
if (!defined('ABSPATH')) {
    exit;
}

class UrubutoPay_Validation
{

    public function urubutopay_check_input($value)
    {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value);
        return sanitize_title($value);
    }

    public function is_required($field)
    {
        return $field . ' is required';
    }

    public function urubutopay_validate_payment_input($payment)
    {
        $errors = array();

        $payer_names = $this->urubutopay_check_input($payment->payer_names);
        $phone_number = $this->urubutopay_check_input($payment->phone_number);
        $channel_name = $this->urubutopay_check_input($payment->channel_name);

        if (empty($payer_names)) {
            array_push($errors, array('payer_names' => $this->is_required('payer names')));
        }

        // phone number
        if (empty($phone_number)) {
            array_push($errors, array('phone_number' => $this->is_required('phone number')));
        }

        // channel_name
        if (empty($channel_name)) {
            array_push($errors, array('channel_name' => $this->is_required('channel name')));
        }

        if (
            !empty($channel_name) &&
            $payment->channel_name !== 'MOMO' &&
            $payment->channel_name !== 'AIRTEL_MONEY' &&
            $payment->channel_name !== 'CARD'
        ) {
            array_push($errors, array('channel_name' => 'invalid payment channel'));
        }

        return $errors;
    }

    public function urubutopay_validate_auth($arg)
    {
        $errors = array();

        $username = $this->urubutopay_check_input($arg->user_name);
        $password = $this->urubutopay_check_input($arg->password);

        if (empty($username)) {
            array_push($errors, array('user_name' => $this->is_required('username')));
        }

        if (empty($password)) {
            array_push($errors, array('password' => $this->is_required('password')));
        }

        return $errors;
    }

    public function urubutopay_validate_payment_callback($arg)
    {

        $errors = array();
        $transaction_id = $this->urubutopay_check_input($arg->transaction_id);

        $internal_transaction_id = $this->urubutopay_check_input($arg->internal_transaction_id);

        $transaction_status = $this->urubutopay_check_input($arg->transaction_status);

        if (empty($transaction_id)) {
            array_push($errors, array('transaction_id' => $this->is_required('Transaction ID')));
        }

        if (empty($internal_transaction_id)) {
            array_push($errors, array('internal_transaction_id' => $this->is_required('Internal transaction ID')));
        }

        if (empty($transaction_status)) {
            array_push($errors, array('transaction_status' => $this->is_required('Transaction Status')));
        }

        return $errors;
    }
}
