<?php
if (!defined('ABSPATH')) {
    exit;
}

function urubutopay_buy_button($post)
{
    $options = get_option(URUBUTOPAY_OPTIONS["SETTING"]);

    $button_name = URUBUTOPAY_OPTION_FIELD['BUTTON_NAME'];

    $product_id = $post->ID;

    $price = get_post_meta($post->ID, URUBUTOPAY_META_BOX['PRICE'], true);

    if (!$price) {
        return '';
    }

    $output = "<div class='upg-single-product-buy-button-wrapper'>";
    $output .= "<button type='button' class='upg-buy-btn upg-single-product-buy-button' data-attr='$product_id'>";
    $output .= isset($options[$button_name]) ? $options[$button_name] : 'Pay now';
    $output .= '</button>';
    $output .= urubutopay_checkout($product_id, $price);
    $output .= '</div>';

    return wp_kses_post($output);
}
