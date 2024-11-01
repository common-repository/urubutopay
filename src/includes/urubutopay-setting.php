<?php
if (!defined('ABSPATH')) {
    exit;
}

class UrubutoPay_Setting
{
    private $setting_page = 'urubutopay_setting_page';

    private $merchant_code_field = URUBUTOPAY_OPTION_FIELD['MERCHANT_CODE'];

    private $api_key_field = URUBUTOPAY_OPTION_FIELD['API_KEY'];

    private $buy_button_field = URUBUTOPAY_OPTION_FIELD['BUTTON_NAME'];

    private $service_code_field = URUBUTOPAY_OPTION_FIELD['SERVICE_CODE'];

    private $username_field = URUBUTOPAY_OPTION_FIELD['USERNAME'];

    private $password_field = URUBUTOPAY_OPTION_FIELD['PASSWORD'];

    private $secret_key = URUBUTOPAY_OPTION_FIELD['SECRET_KEY'];

    private $upg_base_url_field = URUBUTOPAY_OPTION_FIELD['BASE_URL'];

    public function __construct()
    {
        add_action('admin_init', array($this, 'urubutopay_handle_register_setting'));
    }

    public function urubutopay_add_setting_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // check if the user has submitted the setting
        if (isset($_GET['setting_updated'])) {
            add_settings_error('urubutopay_setting_messages', 'urubutopay_setting_message', __('Saved successfully', 'urubutopay_setting_page'), 'updated');
        }
        settings_errors('urubutopay_setting_messages');
?>

        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields($this->setting_page);
                do_settings_sections($this->setting_page);
                submit_button('Save changes');
                ?>
            </form>
        </div>
    <?php
    }

    public function urubutopay_handle_register_setting()
    {
        $section_name = 'urubutopay_setting_section';
        $auth_section = 'urubutopay_setting_athentication';
        $upg_base_url_section = 'urubutopay_base_url_section';

        register_setting($this->setting_page, URUBUTOPAY_OPTIONS["SETTING"]);

        add_settings_section($upg_base_url_section, '', function () {
            $this->urubutopay_section_header('API Information');
        }, $this->setting_page);

        add_settings_section($section_name, '', function () {
            $this->urubutopay_section_header('Merchant Details');
        }, $this->setting_page);

        add_settings_section($auth_section, '', function () {
            $this->urubutopay_section_header(('Authentication Details'));
        }, $this->setting_page);


        // add base url
        add_settings_field(
            $this->upg_base_url_field,
            'API Base URL',
            array($this, 'urubutopay_add_upg_base_url'),
            $this->setting_page,
            $upg_base_url_section,
            array('label_for' => $this->upg_base_url_field)
        );

        // api key
        add_settings_field(
            $this->api_key_field,
            'API Key',
            array($this, 'urubutopay_add_api_key_field'),
            $this->setting_page,
            $upg_base_url_section,
            array('label_for' => $this->api_key_field)
        );

        // merchant code
        add_settings_field(
            $this->merchant_code_field,
            'Merchant Code',
            array($this, 'urubutopay_add_merchant_code_field'),
            $this->setting_page,
            $section_name,
            array('label_for' => $this->merchant_code_field)
        );

        // service code
        add_settings_field(
            $this->service_code_field,
            'Service Code',
            array(
                $this, 'urubutopay_add_service_code_field'
            ),
            $this->setting_page,
            $section_name,
            array('label_for' => $this->service_code_field)
        );

        // buy button name
        add_settings_field(
            $this->buy_button_field,
            'Buy Button Name',
            array($this, 'urubutopay_add_buy_button_field'),
            $this->setting_page,
            $section_name,
            array('label_for' => $this->buy_button_field)
        );

        // username
        add_settings_field(
            $this->username_field,
            'Username',
            array(
                $this, 'urubutopay_add_username'
            ),
            $this->setting_page,
            $auth_section,
            array('label_for' => $this->username_field)
        );

        // password
        add_settings_field(
            $this->password_field,
            'Password',
            array(
                $this, 'urubutopay_add_password'
            ),
            $this->setting_page,
            $auth_section,
            array('label_for' => $this->password_field)
        );

        // secret key
        add_settings_field(
            $this->secret_key,
            'Secret Key',
            array(
                $this, 'urubutopay_add_secret_key'
            ),
            $this->setting_page,
            $auth_section,
            array('label_for' => $this->secret_key)
        );
    }

    public function urubutopay_section_header($title)
    {
    ?>
        <div style="font-weight: bold; font-size: 20px; margin-top: 40px;">
            <?php echo esc_html($title); ?>
        </div>
    <?php
    }

    public function urubutopay_add_merchant_code_field($args)
    {
        $option = get_option(URUBUTOPAY_OPTIONS['SETTING']);
        $label = $args["label_for"];
        $value = isset($option[$args["label_for"]]) ? $option[$args["label_for"]] : "";
        $name = URUBUTOPAY_OPTIONS["SETTING"] . '[' . $label . ']';
    ?>
        <input type="text" id="<?php echo esc_attr($label); ?>" placeholder="Enter merchant code" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" />
    <?php
    }

    public function urubutopay_add_api_key_field($args)
    {

        $option = get_option(URUBUTOPAY_OPTIONS['SETTING']);
        $value = isset($option[$args["label_for"]]) ? esc_attr($option[$args["label_for"]]) : "";
        $label = esc_attr($args["label_for"]);
        $name = URUBUTOPAY_OPTIONS["SETTING"] . '[' . $label . ']';
    ?>
        <input type="text" placeholder="Enter api key" id="<?php echo esc_attr($label); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value) ?>" />
    <?php
    }

    public function urubutopay_add_buy_button_field($args)
    {
        $option = get_option(URUBUTOPAY_OPTIONS['SETTING']);
        $value = isset($option[$args["label_for"]]) ? $option[$args["label_for"]] : "";
        $label = $args["label_for"];
        $name = URUBUTOPAY_OPTIONS["SETTING"] . '[' . $label . ']';
    ?>
        <input type="text" value="<?php echo esc_attr($value); ?>" placeholder="Enter button name" id="<?php echo esc_attr($label); ?>" name="<?php echo esc_attr($name); ?>" />
    <?php
    }

    public function urubutopay_add_service_code_field($args)
    {
        $option = get_option(URUBUTOPAY_OPTIONS['SETTING']);
        $value = isset($option[$args['label_for']]) ? esc_attr($option[$args['label_for']]) : "";
        $label = esc_attr($args['label_for']);
        $name = URUBUTOPAY_OPTIONS["SETTING"] . '[' . $label . ']';
    ?>
        <input type='text' placeholder="Enter service code" id="<?php echo esc_attr($label); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" />
    <?php
    }

    public function urubutopay_add_username($args)
    {
        $option = get_option(URUBUTOPAY_OPTIONS['SETTING']);
        $value = isset($option[$args["label_for"]]) ? esc_attr($option[$args["label_for"]]) : '';
        $label = esc_attr($args['label_for']);
        $name = URUBUTOPAY_OPTIONS["SETTING"] . '[' . $label . ']';
    ?>
        <input type="text" placeholder="Enter username" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" />
    <?php
    }

    public function urubutopay_add_password($args)
    {
        $option = get_option(URUBUTOPAY_OPTIONS['SETTING']);
        $value = isset($option[$args["label_for"]]) ? esc_attr($option[$args["label_for"]]) : '';
        $label = esc_attr($args['label_for']);
        $name = URUBUTOPAY_OPTIONS["SETTING"] . '[' . $label . ']';
    ?>
        <input type="password" placeholder="Enter password" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" />
    <?php
    }

    public function urubutopay_add_secret_key($args)
    {
        $option = get_option(URUBUTOPAY_OPTIONS['SETTING']);
        $value = isset($option[$args["label_for"]]) ? $option[$args["label_for"]] :  rand(0, 10000000000);
        $label = esc_attr($args['label_for']);
        $name = URUBUTOPAY_OPTIONS["SETTING"] . '[' . $label . ']';
    ?>
        <input type="password" placeholder="Enter your secret key for authentication" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" />
    <?php
    }

    public function urubutopay_add_upg_base_url($args)
    {
        $option = get_option(URUBUTOPAY_OPTIONS['SETTING']);
        $value = isset($option[$args["label_for"]]) ? $option[$args["label_for"]] :  '';
        $label = esc_attr($args['label_for']);
        $name = URUBUTOPAY_OPTIONS["SETTING"] . '[' . $label . ']';
    ?>
        <input type="text" placeholder="Enter API URL" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" />
<?php
    }
}
