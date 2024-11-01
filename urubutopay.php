<?php

/**
 * @package UrubutoPay
 * @author BKTechouse
 * @copyright: 2023
 */

/**
 * Plugin Name: UrubutoPay
 * Plugin URI: https://urubutopay.rw
 * Author: BKTechouse
 * Author URI: https://bktechouse.rw
 * Description: Accept online payments from your client's mobile wallets, credit card and debit card
 * Version: 1.0
 * License: GPLv2
 * Text Domain: UrubutoPay
 * Requires at least: PHP 7
 */

/*
UrubutoPay is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

UrubutoPay is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with UrubutoPay. If not, see https://urubutopay.rw.
*/

/**
 * plugin tasks
 * add pages with submenus[Products, Add New Product]
 * on add new product custom post. set amount field input
 *  
 */

use Firebase\JWT\JWT;

if (!defined('ABSPATH')) {
    exit;
}


require_once plugin_dir_path(__FILE__) . './vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . './src/constants/http-code.php';
require_once plugin_dir_path(__FILE__) . './src/constants/wp-constant.php';

require_once plugin_dir_path(__FILE__) . './src/includes/urubutopay-products.php';
require_once plugin_dir_path(__FILE__) . './src/includes/urubutopay-setting.php';
require_once plugin_dir_path(__FILE__) . './src/includes/urubutopay-shortcode.php';
require_once plugin_dir_path(__FILE__) . './src/includes/urubutopay-admin-menu.php';
require_once plugin_dir_path(__FILE__) . '/src/includes/urubutopay-scripts.php';
require_once plugin_dir_path(__FILE__) . './src/includes/urubutopay-payment.php';
require_once plugin_dir_path(__FILE__) . './src/includes/urubutopay-payment-route.php';
require_once plugin_dir_path(__FILE__) . './src/includes/urubutopay-payment-helper.php';
require_once plugin_dir_path(__FILE__) . './src/includes/urubutopay-payment-service.php';
require_once plugin_dir_path(__FILE__) . './src/includes/urubutopay-payment-validation.php';

//templates
require_once plugin_dir_path(__FILE__) . './src/templates/urubutopay-show-all-products.php';
require_once plugin_dir_path(__FILE__) . './src/templates/urubutopay-checkout.php';
require_once plugin_dir_path(__FILE__) . './src/templates/urubutopay-check-transaction-loader.php';
require_once plugin_dir_path(__FILE__) . './src/templates/urubutopay-get-product-shortcode.php';
require_once plugin_dir_path(__FILE__) . './src/templates/urubutopay-buy-button-shortcode.php';


class UrubutoPay
{
    private $product;
    public function __construct()
    {
        new UrubutoPay_AdminMenu();
        $this->product = new UrubutoPay_Product();
        new UrubutoPay_Setting();
        new UrubutoPay_Script();
        new UrubutoPay_ShortCode();
        new UrubutoPay_Payment();
        new UrubutoPay_PaymentRoute();
    }


    public function activate()
    {
        $this->product->urubutopay_register_product_page();
        flush_rewrite_rules();
    }

    public function deactivate()
    {
        $this->product->urubutopay_remove_product_page();
        flush_rewrite_rules();
    }
}

$upg = new UrubutoPay();

register_activation_hook(__FILE__, array($upg, 'activate'));

register_deactivation_hook(__FILE__, array($upg, 'deactivate'));
