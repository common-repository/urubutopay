<?php
if (!defined('ABSPATH')) {
    exit;
}

class UrubutoPay_AdminMenu
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'urubutopay_register_admin_menu'));
    }

    public function urubutopay_register_admin_menu()
    {

        $parent_slug = 'edit.php?post_type=' . URUBUTOPAY_POST_TYPE['PRODUCT'];
        add_menu_page(
            'UrubutoPay',
            'UrubutoPay',
            'manage_options',
            $parent_slug
        );
        add_submenu_page(
            $parent_slug,
            'Add New Product',
            'Add New Product',
            'manage_options',
            'post-new.php?post_type=' . URUBUTOPAY_POST_TYPE['PRODUCT']
        );
        add_submenu_page(
            $parent_slug,
            'Payments',
            'Payments',
            'manage_options',
            'edit.php?post_type=' . URUBUTOPAY_POST_TYPE['PAYMENT']
        );
        add_submenu_page(
            $parent_slug,
            'Settings',
            'Settings',
            'manage_options',
            'urubutopay_setting_page',
            function () {
                $setting = new UrubutoPay_Setting();
                $setting->urubutopay_add_setting_page();
            }
        );
    }
}
