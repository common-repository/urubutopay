<?php
if (!defined('ABSPATH')) {
    exit;
}

class UrubutoPay_ShortCode
{
    public function __construct()
    {
        add_action('init', array($this, 'urubutopay_register_shortcode'));
        add_filter('the_content', array($this, 'urubutopay_add_buy_button_shortcode'));
    }

    public function urubutopay_register_shortcode()
    {
        add_shortcode(URUBUTOPAY_SHORTCODE['SHOW_ALL_PRODUCTS'], array($this, 'urubutopay_get_all_product'));
        add_shortcode(URUBUTOPAY_SHORTCODE['SHOW_BUY_BUTTON'], array($this, 'urubutopay_show_buy_button'));
        add_shortcode(URUBUTOPAY_SHORTCODE['SHOW_PRODUCT'], array($this, 'urubutopay_show_single_product'));
    }


    public function urubutopay_get_all_product()
    {
        return wp_kses_post(urubutopay_show_all_products());
    }

    public function urubutopay_show_buy_button()
    {
        global $post;
        return wp_kses_post(urubutopay_buy_button($post));
    }

    public function urubutopay_add_buy_button_shortcode($content)
    {
        global $post;

        if ($post && $post->post_type === URUBUTOPAY_POST_TYPE['PRODUCT']) {
            $price = get_post_meta($post->ID, URUBUTOPAY_META_BOX['PRICE'], true);
            $output = '<div>';
            $output .= $content;
            $output .= 'RWF ' . $price;
            $output .= "[" . URUBUTOPAY_SHORTCODE["SHOW_BUY_BUTTON"] . "]";
            $output .= '</div>';
            return  wp_kses_post($output);
        }

        return wp_kses_post($content);
    }

    public function urubutopay_show_single_product($args)
    {
        if (null === $args) {
            return '';
        }

        if (empty($args['id'])) {
            return '';
        }

        $post = get_post(intval($args['id']));
        if ($post->post_type !== URUBUTOPAY_POST_TYPE['PRODUCT']) {
            return '';
        }

        return urubutopay_shortcode_get_product($post);
    }
}
