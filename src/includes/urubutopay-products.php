<?php
if (!defined('ABSPATH')) {
    exit;
}

class UrubutoPay_Product
{
    private $product_post_type = URUBUTOPAY_POST_TYPE['PRODUCT'];

    public function __construct()
    {
        add_action('init', array($this, 'urubutopay_register_product_post_type'));
        add_filter(
            "manage_{$this->product_post_type}_posts_columns",
            array($this, 'urubutopay_add_columns_to_post_type')
        );
        add_action(
            "manage_{$this->product_post_type}_posts_custom_column",
            array($this, 'urubutopay_diplay_post_type_columns'),
            10,
            2
        );
        add_action('save_post', array($this, 'urubutopay_save_price_meta_box'));
    }

    public function urubutopay_register_product_post_type()
    {
        // products
        register_post_type($this->product_post_type, array(
            'labels' => array(
                'name' => 'Products', 'singular_name' => 'Product',
                'add_new' => 'Add New Product',
                'add_new_item' => 'Add Product',
                'edit_item' => 'Edit Product'
            ),
            'public' => true, 'archive' => true,
            'show_in_menu' => 'edit.php?post_type=' . URUBUTOPAY_POST_TYPE["PRODUCT"],
            'supports' => array('title', 'editor', 'thumbnail'),
            'register_meta_box_cb' => array($this, 'urubutopay_register_meta_box'),
            'rewrite'     => array('slug' => 'upgproducts'), // my custom slug
        ));
    }

    public function urubutopay_register_meta_box()
    {
        add_meta_box(
            URUBUTOPAY_META_BOX['PRICE'],
            'Price',
            array($this, 'urubutopay_add_price_meta_box'),
            null,
            'advanced',
            'default'
        );
    }

    public function urubutopay_add_price_meta_box($post)
    {

        $meta_key = URUBUTOPAY_META_BOX['PRICE'];

        wp_nonce_field(basename(__FILE__), $meta_key . '_class_nonce');

        $price_value = get_post_meta($post->ID, $meta_key, true);

        echo '<label for="smashing-post-class">Price</label><br /><input type="number" name="' . esc_attr($meta_key) . '" 
        class="widefat"value="' . esc_attr($price_value) . '" size="30" required />';
    }

    public function urubutopay_add_columns_to_post_type($columns)
    {
        $columns = array(
            'cb' => $columns['cb'],
            'image' => __('Image'),
            'title' => __('Title'),
            'price' => __('Price'),
            'date' => __('Date')
        );
        return $columns;
    }

    public function urubutopay_diplay_post_type_columns($column, $post_id)
    {

        if ($column === 'image') {
            echo esc_html(get_the_post_thumbnail($post_id, array(80, 80)));
        }

        if ($column === 'price') {
            echo 'RWF ' . esc_html(get_post_meta($post_id, URUBUTOPAY_META_BOX['PRICE'], true));
        }
    }

    public function urubutopay_save_price_meta_box($post_id)
    {
        $meta_key = URUBUTOPAY_META_BOX['PRICE'];

        $nonce = sanitize_text_field($_POST[$meta_key . '_class_nonce']);

        if (!isset($nonce) || !wp_verify_nonce($nonce, basename(__FILE__))) {
            return $post_id;
        }

        update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$meta_key]));
    }

    public function urubutopay_register_product_page()
    {
        $post = array(
            'post_title' => URUBUTOPAY_PRODUCT_PAGE_NAME['PRODUCTS'],
            'post_content' => "[" . URUBUTOPAY_SHORTCODE['SHOW_ALL_PRODUCTS'] . "]",
            'post_type' => URUBUTOPAY_POST_TYPE['PAGE'],
            'post_status' => 'publish',
            'post_name' => URUBUTOPAY_PRODUCT_PAGE_SLUG['PRODUCTS']
        );
        wp_insert_post($post);
    }

    public function urubutopay_remove_product_page()
    {
        $posts = get_posts([
            'post_name' => URUBUTOPAY_PRODUCT_PAGE_SLUG['PRODUCTS'],
            'post_type' => URUBUTOPAY_POST_TYPE['PAGE'],
            'post_title' => URUBUTOPAY_PRODUCT_PAGE_NAME['PRODUCTS']
        ]);

        if (empty($posts) > 0) {
            wp_delete_post($posts[0]->ID);
        }
    }
}
