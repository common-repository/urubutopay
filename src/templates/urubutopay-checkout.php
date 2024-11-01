<?php
if (!defined('ABSPATH')) {
    exit;
}

function urubutopay_checkout($product_id, $price)
{
    $options = get_option(URUBUTOPAY_OPTIONS["SETTING"]);

    $button_name = isset($options[URUBUTOPAY_OPTION_FIELD['BUTTON_NAME']]) ? $options[URUBUTOPAY_OPTION_FIELD['BUTTON_NAME']] : 'Pay Now';

    $payment_service = new UrubutoPay_PaymentService();
    $response = $payment_service->urubutopay_get_merchant_detail();

    if ($response['code'] !== URUBUTOPAY_HTTP_CODE['OK'] && $response['code'] !== URUBUTOPAY_HTTP_CODE['CREATED']) {
        return;
    }
    $data = $response['data']->data;

    $accept_card_payment = isset($data->accept_card_payment) ? $data->accept_card_payment : null;
?>
    <div class="upg-modal" style="display: none;" data-product-id="<?php echo esc_attr($product_id); ?>">
        <div class="upg-modal-container">
            <?php urubutopay_check_transaction_loader(); ?>
            <div class="upg-modal-header">
                <div class="upg-modal-header-title">RWF <span class="upg-price-converter"><?php echo esc_html($price); ?></span></div>
                <button type="button" class="upg-close-modal" onclick="closePaymentModal();">
                    <img alt="close" src="<?php echo esc_url(plugins_url('../../public/images/payment/close.png', __FILE__)); ?>" />
                </button>
            </div>
            <div class="upg-modal-body">
                <div class="upg-form-modal">
                    <form action="#" class="up-form">
                        <div class="upg-form-group" data-attr="payer_names">
                            <label for="">Names</label>
                            <input type="text" placeholder="Enter Name" name="payer_names" value="" />
                        </div>
                        <div class="upg-form-group" data-attr="channel_name">
                            <label for="">Choose A Payment Mode</label>
                            <div class="upg-form-payment-mode">
                                <button type="button" class="upg-form-payment-mode-btn" name="<?php echo esc_attr(URUBUTOPAY_PAYMENT_CHANNEL["MOMO"]); ?>">
                                    <img src="<?php echo esc_url(plugins_url('../../public/images/mtn.svg', __FILE__)); ?>" alt="mtn" />
                                </button>
                                <button type="button" class="upg-form-payment-mode-btn" name="<?php echo esc_attr(URUBUTOPAY_PAYMENT_CHANNEL["AIRTEL_MONEY"]); ?>">
                                    <img src="<?php echo esc_url(plugins_url('../../public/images/airtel.svg', __FILE__)); ?>" alt="airtel" />
                                </button>
                                <?php if ($accept_card_payment === URUBUTOPAY_RESPONSE_STATUS['YES']) : ?>
                                    <button type="button" class="upg-form-payment-mode-btn" name="<?php echo esc_attr(URUBUTOPAY_PAYMENT_CHANNEL["CARD"]); ?>">
                                        <img src="<?php echo esc_url(plugins_url('../../public/images/visa.svg', __FILE__)); ?>" alt="visa" />
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="upg-form-group" data-attr="phone_number">
                            <label for="">Phone Number</label>
                            <input type="text" placeholder="Enter Phonenumber" name="phone_number" value="" />
                        </div>
                        <div class="upg-form-group">
                            <button type="button" class="upg-form-send-btn" onclick="initPayment('<?php echo esc_html($product_id); ?>')">
                                <span style="display: block;"><?php echo esc_html($button_name); ?></span>
                                <img src="<?php echo esc_url(plugins_url('../../public/images/spinner/white.svg', __FILE__)); ?>" style="display: none;" alt="white-spinner" class="upg-btn-loader" />
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>