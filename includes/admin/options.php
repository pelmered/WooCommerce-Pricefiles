<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class WC_Pricefiles_Admin_Options extends WC_Pricefiles_Admin
{

    function __construct($plugin_slug)
    {
        $this->plugin_slug = $plugin_slug;

        //add_filter($this->plugin_slug . '_option_tabs', array($this, 'add_options_tab'), 1);

        $this->plugin_options = WC_Pricefiles()->get_options();

        add_action('admin_menu', array($this, 'add_plugin_menu'));

        add_action('admin_enqueue_scripts', array($this, 'admin_options_styles'));

        add_action('admin_init', array($this, 'initialize_pricefile_options'));
        
        require_once dirname(WC_PLUGIN_FILE).'/includes/wc-template-functions.php';

        parent::__construct($plugin_slug);
    }

    function admin_options_styles()
    {
        wp_enqueue_style('pricefiles-admin-options-styles', WP_PRICEFILES_PLUGIN_URL . 'assets/css/admin-options.css', '', '');
    }

    /**
     * 
     */
    function add_plugin_menu()
    {
        //manage_woocommerce
        add_submenu_page(
                'woocommerce', __('Pricefiles', $this->plugin_slug), __('Pricefiles', $this->plugin_slug), 'manage_woocommerce', $this->plugin_slug, array($this, 'display_settings_page')
        );
    }

    /**
     * Renders a simple page to display for the theme menu defined above.
     */
    function display_settings_page()
    {
        $tabs = array(
            'pricelist_options' => array(
                'name' => __('Pricefiles options', $this->plugin_slug),
                'callback' => array($this, 'pricelist_options_page_settings')
            ),
        );

        $tabs = apply_filters($this->plugin_slug . '_option_tabs', $tabs);

        //Get key of first tab (the default) 
        $first_key = key($tabs);

        if (!empty($_GET['tab']) && in_array($_GET['tab'], array_keys($tabs)))
        {
            $active_tab = $_GET['tab'];
        } else
        {
            $active_tab = $first_key;
        }
        ?>
        <!-- Create a header in the default WordPress 'wrap' container -->
        <div class="wrap woocommerce">

            <div id="icon-themes" class="icon32"></div>
            <h2><?php echo $tabs[$first_key]['name'] ?></h2>
            <?php settings_errors(); ?>

            <h2 class="nav-tab-wrapper">
        <?php foreach ($tabs AS $slug => $name) : ?>
                    <a href="?page=<?php echo $this->plugin_slug; ?>&tab=<?php echo $slug; ?>" class="nav-tab <?php echo $active_tab == $slug ? 'nav-tab-active' : ''; ?>"><?php echo $name['name']; ?></a>
                <?php endforeach; ?>
            </h2>
            
            <?php $this->donation_button(); ?>

            <form method="post" action="options.php">
        <?php
        if (is_callable($tabs[$active_tab]['callback']))
        {
            call_user_func($tabs[$active_tab]['callback']);
        } else
        {
            
        }
        ?>
            </form>

        </div><!-- /.wrap -->
        <?php
    }

    /* ------------------------------------------------------------------------ *
     * Setting Registration
     * ------------------------------------------------------------------------ */

    /**
     * Initializes the theme's display options page by registering the Sections,
     * Fields, and Settings.
     *
     * This function is registered with the 'admin_init' hook.
     */
    function pricelist_options_page_settings()
    {
        settings_fields($this->plugin_slug . '_urls');
        do_settings_sections($this->plugin_slug . '_urls_section');
        
        settings_fields($this->plugin_slug . '_donate');
        do_settings_sections($this->plugin_slug . '_donate_section');
            
        settings_fields($this->plugin_slug . '_options');
        do_settings_sections($this->plugin_slug . '_options_section');

        $this->submit_button();
        
        settings_fields($this->plugin_slug . '_advanced_options');
        do_settings_sections($this->plugin_slug . '_advanced_options_section');

        $this->submit_button();
    }
    
    function initialize_pricefile_options()
    {
        register_setting(
                $this->plugin_slug . '_options', $this->plugin_slug . '_options', array($this, 'validate_input')
        );
        register_setting(
                $this->plugin_slug . '_advanced_options', $this->plugin_slug . '_options', array($this, 'validate_input')
        );

        /*
         * Options page header (Pricefile URLs)
         */
        // First, we register a section. This is necessary since all future options must belong to a 
        add_settings_section(
            $this->plugin_slug . '_urls', // ID used to identify this section and with which to register options
            __('Pricefile URLs', $this->plugin_slug), // Title to be displayed on the administration page
            array($this, 'pricefile_display_callback'), // Callback used to render the description of the section
            $this->plugin_slug . '_urls_section' // Page on which to add this section of options
        );
        /*
        add_settings_section(
            $this->plugin_slug . '_donate', 
            __('Donate', $this->plugin_slug), 
            array($this, 'donation_button'), 
            $this->plugin_slug . '_donate_section'
        );
        */
        
        /*
         * Pricefiles options
         */
        add_settings_section(
            $this->plugin_slug . '_options', 
            __('Pricefile options', $this->plugin_slug), 
            array($this, 'pricefile_settings_callback'), 
            $this->plugin_slug . '_options_section'
        );

        // Next, we'll introduce the fields for toggling the visibility of content elements.
        add_settings_field(
            'output_prices', // ID used to identify the field
            __('Output prices', $this->plugin_slug), // The label to the left of the option
            array($this, 'output_prices_callback'), // The name of the function responsible for rendering the option fields
            $this->plugin_slug . '_options_section', // The page on which this option will be displayed
            $this->plugin_slug . '_options', // The name of the section to which this field belongs
            array(// The array of arguments to pass to the callback. In this case, just a description.
                __('.', $this->plugin_slug),
            )
        );
        add_settings_field(
            'exclude_ids', __('Exclude products from pricefile', $this->plugin_slug), 
            array($this, 'exclude_products_callback'), 
            $this->plugin_slug . '_options_section', 
            $this->plugin_slug . '_options', 
            array(
                'description' => __('These products will not show up in the pricefile.', $this->plugin_slug),
            )
        );
        add_settings_field(
            'stock_status_type', __('Stock status variant', $this->plugin_slug), 
            array($this, 'select_option_callback'), 
            $this->plugin_slug . '_options_section', 
            $this->plugin_slug . '_options', 
            array(
                'key'           => 'stock_status_type',
                'label'         => __('Stock status type', $this->plugin_slug),
                'options'       => array(
                    array(
                        'value' => 'yes_no',
                        'label' => __('Yes / No', $this->plugin_slug),
                    ),
                    array(
                        'value' => 'stock_qty',
                        'label' => __('Stock Qty', $this->plugin_slug),
                    )
                ),
                'description'   => __('', $this->plugin_slug),
            )
        );
        add_settings_field(
            'show_variations', __('Show variations', $this->plugin_slug), 
            array($this, 'checkbox_option_callback'), 
            $this->plugin_slug . '_options_section', 
            $this->plugin_slug . '_options', 
            array(
                'key'           => 'show_variations',
                'label'         => __('Show variations', $this->plugin_slug),
                'description'   => __('If checked, all variations will be listed in the pricefile. If it\'s not checked, only the main variable product will be shown.', $this->plugin_slug),
            )
        );
        
        add_settings_field(
            'show_variation_format', __('Variation title format', $this->plugin_slug), 
            array($this, 'text_option_callback'), 
            $this->plugin_slug . '_options_section', 
            $this->plugin_slug . '_options', 
            array(
                'key'           => 'show_variation_format',
                'description' => __(
                        'Define format for product variation titles in the pricefile. <br />' .
                        '<strong>%title%</strong> ville be replaced by product title. <br />' .
                        '<strong>%var%</strong> ville be replaced by variable atribute. <br />' .
                        'Default: <strong>%title% - %var%</strong>'
                    , $this->plugin_slug),
            )
        );
        add_settings_field(
            'variation_glue', __('Variation Glue', $this->plugin_slug), 
            array($this, 'text_option_callback'), 
            $this->plugin_slug . '_options_section', 
            $this->plugin_slug . '_options', 
            array(
                'key'           => 'variation_glue',
                'description' => __(
                        'Sting that will be used to concatinate variable attributes if there are more than one for a product variation.<br />' .
                        'If you leave it blank the default is " "(a space) witch gives you for example this: <strong>"T-shirt - Blue XL"</strong> (depending on the variation format)' 
                    , $this->plugin_slug),
            )
        );
        add_settings_field(
            'shipping_methods', 
            __('Select shipping methods', $this->plugin_slug), 
            array($this, 'shipping_methods_callback'), 
            $this->plugin_slug . '_options_section', 
            $this->plugin_slug . '_options', 
            array(
                __('Select the shipping methods that will be available in the pricefile. The plugin will automatically select the cheapest option.', $this->plugin_slug),
            )
        );
        add_settings_field(
            'product_id_as_sku', __('Use product ID as SKU', $this->plugin_slug), 
            array($this, 'checkbox_option_callback'), 
            $this->plugin_slug . '_options_section', 
            $this->plugin_slug . '_options', 
            array(
                'key'           => 'product_id_as_sku',
                'label'         => __('Use product ID as SKU', $this->plugin_slug),
                'description'   => __('If checked, product ID will be used as SKU if the SKU-field is empty, otherwise it will be empty in the pricefile if no SKU is set.', $this->plugin_slug),
            )
        );
        add_settings_field(
            'shipping_destination', 
            __('Shipping destination', $this->plugin_slug), 
            array($this, 'shipping_destination_callback'), 
            $this->plugin_slug . '_options_section', 
            $this->plugin_slug . '_options', 
            array(
                'description' => sprintf(__('The shipping price will be calculated to this address. %sReset address to default%s', $this->plugin_slug), '<a href="' . admin_url('admin.php?page=wc-pricefiles&reset_address=1') . '" id="wc_pricefiles_reset_address" >', '</a>'),
            )
        );
        
        /*
         * Advanced options
         */
        add_settings_section(
            $this->plugin_slug . '_advanced_options', 
            __('Advanced options', $this->plugin_slug), 
            array($this, 'pricefile_settings_callback'), 
            $this->plugin_slug . '_advanced_options_section'
        );
        add_settings_field(
            'use_cache', 
            __('Use cache for pricefile', $this->plugin_slug), 
            array($this, 'use_cache_callback'), 
            $this->plugin_slug . '_advanced_options_section', 
            $this->plugin_slug . '_advanced_options', 
            array(
                'key'           => 'use_cache',
                'label'         => __('Use cache', $this->plugin_slug),
                'description' => __('Use cache for pricefile. Usefull if you have many products. Needs cron to refresh cache.<br />' . 
                        WP_CONTENT_DIR . '/cache/' . WC_PRICEFILES_PLUGIN_SLUG . '/' . ' needs to be writable by PHP', $this->plugin_slug).
                        ' ('.(is_writable(WP_CONTENT_DIR . '/cache/' . WC_PRICEFILES_PLUGIN_SLUG . '/') ? '<span style="color: green">'.__('Is writable', $this->plugin_slug).'</span>' : '<span style="color: red">'.__('NOT WRITABLE', $this->plugin_slug).'</span>' ).').'       
            )
        );
        add_settings_field(
            'use_debug', 
            __('Debug mode', $this->plugin_slug), 
            array($this, 'checkbox_option_callback'), 
            $this->plugin_slug . '_advanced_options_section', 
            $this->plugin_slug . '_advanced_options', 
            array(
                'key'           => 'use_debug',
                'label'         => __('Use debug mode', $this->plugin_slug),
                'description'   => __('Output debug messages. Only check this when debugging.', $this->plugin_slug),
            )
        );
        add_settings_field(
            'set_memory_limit', 
            __('Memory limit', $this->plugin_slug), 
            array($this, 'checkbox_option_callback'), 
            $this->plugin_slug . '_advanced_options_section', 
            $this->plugin_slug . '_advanced_options', 
            array(
                'key'           => 'set_memory_limit',
                'label'         => __('Set PHP memory limit', $this->plugin_slug),
                'description'   => __('Try to set the PHP memory limit when generating the pricefiles. Try using this when you get 500 Internal server error. ', $this->plugin_slug),
            )
        );
        add_settings_field(
            'disable_timeout', 
            __('Disable timeout', $this->plugin_slug), 
            array($this, 'disable_timeout_callback'), 
            $this->plugin_slug . '_advanced_options_section', 
            $this->plugin_slug . '_advanced_options', 
            array(
                'key'           => 'disable_timeout',
                'label'         => __('Disable timeout', $this->plugin_slug),
                'description'   => __('Try to disable the PHP timeout when generating the pricefiles. Try using this when you get timeouts or 500 Internal server error. ', $this->plugin_slug),
            )
        );
        
        
        add_settings_field(
            'deactivate_ean_validation', 
            __('Deactivate EAN validation', $this->plugin_slug), 
            array($this, 'checkbox_option_callback'), 
            $this->plugin_slug . '_advanced_options_section', 
            $this->plugin_slug . '_advanced_options', 
            array(
                'key'           => 'deactivate_ean_validation',
                'label'         => __('Deactivate EAN validation', $this->plugin_slug),
                'description'   => __('Inactive the automatic EAN code validation when editing products.', $this->plugin_slug),
            )
        );
        
    }


    /* ------------------------------------------------------------------------ *
     * Section Callbacks
     * ------------------------------------------------------------------------ */

    function pricefile_settings_callback()
    {
        
    }

    function pricefile_display_callback()
    {
        global $wc_pricefiles_list;

        $pricefile_base_url = get_bloginfo('url') . '/?pricefile=';

        echo '<p>' . __('Copy and send to the respective service.', $this->plugin_slug) . '</p>';
        
        $available_pricefiles = WC_Pricefiles::get_instance()->get_available_pricefiles();

        foreach ($available_pricefiles AS $slug => $data) {
            echo '<h4>' . $data['name'] . '</h4>';

            echo '<p>';
            echo '<input class="wide" type="text" size="110" value="' . $pricefile_base_url . $slug . '" disabled /><br />';
            echo '<span class="description"></span>';
            echo '</p>';
        }

        echo '<p>';
        echo _e('More information:', $this->plugin_slug) . '<br />';
        
        
        foreach ($available_pricefiles AS $slug => $data) {
            if(!empty($data['info_link']))
            {
                echo '<a href="'.$data['info_link'].'">'.$data['name'].'</a><br />'; 
            }
        }
        echo '</p>';
    }
    
    

    /* ------------------------------------------------------------------------ *
     * Field Callbacks
     * ------------------------------------------------------------------------ */
    
    function output_prices_callback($args)
    {

        //$options = get_option( $this->plugin_slug.'_options', array('output_prices' => '') );
        $options = $this->plugin_options;

        echo '<select id="output_prices" name="' . $this->plugin_slug . '_options[output_prices]">';
        echo '<option value="shop"' . selected($options['output_prices'], 'shop', false) . '>' . __('Same as shop', $this->plugin_slug) . '</option>';
        echo '<option value="including"' . selected($options['output_prices'], 'including', false) . '>' . __('Including VAT', $this->plugin_slug) . '</option>';
        echo '<option value="excluding"' . selected($options['output_prices'], 'excluding', false) . '>' . __('Excluding VAT', $this->plugin_slug) . '</option>';
        echo '</select>';
    }

    function exclude_products_callback($args)
    {
        global $woocommerce;

        $product_ids = (empty($this->plugin_options['exclude_ids']) ? array() : $this->plugin_options['exclude_ids'] );

        echo '<select id="woocommerce_pricefiles_exclude_ids" name="' . $this->plugin_slug . '_options[exclude_ids][]" class="ajax_chosen_select_products" multiple="multiple" data-placeholder="' . __('Search for a product&hellip;', 'woocommerce') . '">';

        if ($product_ids)
        {
            foreach ($product_ids as $product_id) 
            {

                $product = get_product($product_id);
                $product_name = $product->get_formatted_name();

                echo '<option value="' . esc_attr($product_id) . '" selected="selected">' . esc_html($product_name) . '</option>';
            }
        }

        echo '</select>';
        echo '<img class="help_tip" data-tip="' . __('Add any products you want to exlude from the price list here.', 'woocommerce') . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" height="16" width="16" />';

        echo '<p>' . $args['description'] . '</p>';
    }
    function show_variations_callback()
    {
        
    }
    function show_variation_format_callback()
    {
        
    }
    
    function shipping_methods_callback()
    {
        global $woocommerce;

        //$shipping_methods_ids = get_option($this->plugin_slug.'_options', FALSE);
        //$shipping_methods_ids = $shipping_methods_ids['shipping_methods'];

        $shipping_methods_ids = (empty($this->plugin_options['shipping_methods']) ? array() : $this->plugin_options['shipping_methods'] );

        $shipping_methods = $woocommerce->shipping->load_shipping_methods();

        if ($shipping_methods)
        {
            foreach ($shipping_methods as $shipping_method) {
                echo '<label class="shipping-method"> ';
                echo '<span>' . esc_html($shipping_method->method_title) . '</span>';
                echo '<input type="checkbox" name="' . $this->plugin_slug . '_options[shipping_methods][]" value="' . esc_attr($shipping_method->id) . '"' . (in_array($shipping_method->id, $shipping_methods_ids) ? 'checked="checked"' : '') . '/>';
                echo '</label>';
            }
        }
    }

    function shipping_destination_callback($args)
    {
        echo '<p>' . $args['description'] . '</p>';

        echo '<div id="shipping-destination">';

        $shipping_destination_values = $this->plugin_options['shipping_destination'];

        if (!$shipping_destination_values)
        {
            global $wc_pricefiles_globals;
            $shipping_destination_values = $wc_pricefiles_globals['default_shipping_destination'];
        }
        $shipping_fields = WC_Pricefiles()->get_shipping_destination_fields();
        
        foreach ($shipping_fields as $key => $field) {
            $field['required'] = 0;
            woocommerce_form_field($this->plugin_slug . '_options[shipping_destination][' . $key . ']', $field, $shipping_destination_values[$key]);
        }

        echo '</div>';
    }

    function use_cache_callback($args)
    {
        $use_cache = (empty($this->plugin_options['use_cache']) ? array() : $this->plugin_options['use_cache'] );

        echo '<label for="' . $this->plugin_slug . '_options_use_cache" class=""> ';
        echo '<input type="checkbox" name="' . $this->plugin_slug . '_options[use_cache]" id="' . $this->plugin_slug . '_options_use_cache" value="1" ' . ($use_cache == 1 ? 'checked="checked"' : '') . '/>';
        echo '<span>' . __('Use cache') . '</span>';
        echo '</label>';

        echo '<p style="clear: both">' . $args['description'] . '</p>';
        
        $pricefile_base_url = get_bloginfo('url') . '/?pricefile=all&refresh=1&output=json';
        ?>
        
        <div id="<?php echo $this->plugin_slug; ?>'_cache_additional" style="<?php echo ($use_cache == 1 ? '' : 'display: none'); ?>">
            <input class="wide" type="text" size="60" value="<?php echo $pricefile_base_url; ?>" disabled />

            <br /><button id="<?php echo $this->plugin_slug; ?>'_refresh_cache_button" data-url="<?php echo $pricefile_base_url; ?>"><?php _e('Refresh cache'); ?></button>
            <span id="<?php echo $this->plugin_slug; ?>'_cache_refresh_status"></span>

            
            
            <p>
                It's recommanded to use crontab toether with cache. Click <a href="#" id="<?php echo $this->plugin_slug; ?>_expand_disable_timeout_info">here</a> for setup instructions.
            </p>
        </div>
        
        <div id="<?php echo $this->plugin_slug; ?>_disable_timeout_info" style="display: none;">
        
            <p  style="margin-top: 40px">
                
            </p>
            
            <p>Copy this file:</p>
            <pre><?php echo WP_PRICEFILES_PLUGIN_PATH.'cron/pricefiles-cron.php'; ?></pre>
            <p>To:</p>
            <pre><?php echo ABSPATH.'pricefiles-cron.php'; ?></pre>
            
            <p>Open your crontabs file, usually /etc/contabs. It's recommanded to use (use "sudo" on Ubuntu): </p>
            <pre>crontabs -e</pre>
            
            <p>Add this:</p>
            <pre>55 23 * * * <?php echo ABSPATH.'pricefiles-cron.php'; ?></pre>
            
            <p>Test the script with this command:</p>
            <pre>php -f <?php echo ABSPATH.'pricefiles-cron.php'; ?></pre>
            
            <p>It should output something similar to this:</p>
            <pre>{<strong>"status":"ok"</strong>,"time":"11.211"}</pre>
            
            <p>You might have to change the permissions on the cache folder like this:</p>
            <pre>chmod -R 777 <?php echo WP_CONTENT_DIR . '/cache/' . WC_PRICEFILES_PLUGIN_SLUG; ?></pre>
            
            <p  style="margin-bottom: 40px">
                
                
            </p>
        </div>
        <?php
    }
    
    function set_memory_limit_callback($args)
    {
        $option_value = (empty($this->plugin_options[$args['key']]) ? array() : $this->plugin_options[$args['key']] );

        echo '<label class="shipping-method"> ';
        echo '<input type="checkbox" name="' . $this->plugin_slug . '_options['.$args['key'].']" id="' . $this->plugin_slug . '_options_'.$args['key'].'" value="1" ' . ($option_value == 1 ? 'checked="checked"' : '') . '/>';
        echo '<span>' . $args['label'] . '</span>';
        echo '</label>';

        echo '<p style="clear: both">' . $args['description'] . '</p>';
    }
    function disable_timeout_callback($args)
    {
        $option_value = (empty($this->plugin_options[$args['key']]) ? array() : $this->plugin_options[$args['key']] );

        echo '<label class="shipping-method"> ';
        echo '<input type="checkbox" name="' . $this->plugin_slug . '_options['.$args['key'].']" id="' . $this->plugin_slug . '_options_'.$args['key'].'" value="1" ' . ($option_value == 1 ? 'checked="checked"' : '') . '/>';
        echo '<span>' . $args['label'] . '</span>';
        echo '</label>';

        echo '<p style="clear: both">' . $args['description'] . '</p>';
        ?>
        <p>
            If you get timeouts, consider using cache and cron to pre-generate the pricefiles. <br />
            For more information click <a href="#" id="<?php echo $this->plugin_slug; ?>_expand_disable_timeout_info">here</a>.
        </p>
        
        
        <?php /*
        <p style="clear: both">
            This might not work if you are using Nginx and PHP-FPM. To fix this you need to make changes in the config files on your server. 
            For more information click <a href="#" id="<?php echo $this->plugin_slug; ?>_expand_disable_timeout_info">here</a>.
        </p>
        
        <div id="<?php echo $this->plugin_slug; ?>_disable_timeout_info" style="display: none;">
        <h4>Changes in php.ini</h4>
        <p>Edit:</p>
        <pre>/etc/php5/fpm/php.ini</pre>
        <p>Set:</p>
        <pre>max_execution_time = 300</pre>
        
        <h4>Changes in PHP-FPM</h4>
        <p>Edit:</p>
        <pre>/etc/php5/fpm/pool.d/www.conf</pre>
        <p>Set:</p>
        <pre>request_terminate_timeout = 300</pre>
        
        <h4>Changes in Nginx Config</h4>
        <p>Edit:</p>
        <pre>/etc/nginx/sites-available/example.com</pre>
        <p>Set:</p>
        <pre>
    location ~ \.php$ {
        ...
        fastcgi_pass  unix:/var/run/php5-fpm.sock;
        ...
	<strong>fastcgi_read_timeout 300;</strong>
    }
        </pre>
        
        <p>
            For more information see <a target="_blank" href="https://rtcamp.com/tutorials/php/increase-script-execution-time/">here</a>.
        </p>
           
            
        </div>
        */?>
        
        <?php
    }
    function use_debug_callback($args)
    {
        $option_value = (empty($this->plugin_options[$args['key']]) ? array() : $this->plugin_options[$args['key']] );

        echo '<label class="shipping-method"> ';
        echo '<input type="checkbox" name="' . $this->plugin_slug . '_options['.$args['key'].']" id="' . $this->plugin_slug . '_options_'.$args['key'].'" value="1" ' . ($option_value == 1 ? 'checked="checked"' : '') . '/>';
        echo '<span>' . $args['label'] . '</span>';
        echo '</label>';

        echo '<p style="clear: both">' . $args['description'] . '</p>';
    }
    
    function deactivate_ean_validation_callback($args)
    {
        $deactivate_ean_validation = (empty($this->plugin_options['deactivate_ean_validation']) ? array() : $this->plugin_options['deactivate_ean_validation'] );

        echo '<label class="shipping-method"> ';
        echo '<input type="checkbox" name="' . $this->plugin_slug . '_options[deactivate_ean_validation]" id="' . $this->plugin_slug . '_options_deactivate_ean_validation" value="1" ' . ($deactivate_ean_validation == 1 ? 'checked="checked"' : '') . '/>';
        echo '<span>' . __('Deactivate EAN validation') . '</span>';
        echo '</label>';

        echo '<p style="clear: both">' . $args['description'] . '</p>';
    }
    
    
    
    function validate_input($input)
    {
        if (!is_array($input))
            return false;

        if (empty($input['exclude_ids']))
        {
            $input['exclude_ids'] = array();
        }

        return parent::validate_input($input);
    }

}
