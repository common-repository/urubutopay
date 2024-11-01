<?php
if (!defined('ABSPATH')) {
    exit;
}

function urubutopay_shortcode_get_product($post)
{
    $price = get_post_meta($post->ID, URUBUTOPAY_META_BOX['PRICE'], true);

    if (null === $post) {
        return '';
    }

    $image_url = get_the_post_thumbnail_url($post->ID);
    $output = '<div class="upg-shortcode-product-item">';

    //product image
    $output .= '<div class="upg-shortcode-product-item--image">';
    $output .= "<img src=" . esc_url($image_url) . " alt=''>";
    $output .= '</div>';

    //description
    $output .= '<div class="upg-shortcode-product-item--description">';

    //title
    $output .= '<div class="upg-shortcode-product-item--description--title">';
    $output .= $post->post_title;
    $output .= '</div>';

    //price
    $output .= '<div class="upg-shortcode-product-item--description--price">';
    $output .= '<span class="uppercase">RWF</span>';
    $output .= '<span class="upg-price-converter" style="margin-left: 5px;">' . esc_html(get_post_meta($post->ID, URUBUTOPAY_META_BOX['PRICE'], true)) . '</span>';
    $output .= '</div>';

    //button
    $output .= '<div class="upg-shortcode-product-item--description--button">';
    $output .= '<a href="' . esc_url(get_permalink($post->ID)) . '" target="__blank">';
    $output .= '<button type="button">';
    $output .= 'View More';
    $output .= '</button>';
    $output .= '<a>';
    $output .= '</div>';

    //close description
    $output .= '</div>';

    //close upg-shortcode-product-item
    $output .= '</div>';

    // add checkout modal
    $output .= urubutopay_checkout($post->ID, $price);

    return wp_kses_post($output);
}
