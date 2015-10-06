<?php

class WC_Pricefiles_Admin //extends EWP_Plugin_Settings   
{
    public $plugin_slug;
    public $plugin_options = array();
            
    function __construct($plugin_slug) 
    {
        $this->plugin_slug = $plugin_slug;
    }
    
    function submit_button()
    {
        echo '<p class="submit">';
        echo '<input type="submit" name="save_attribute" id="submit" class="button-primary" value="'.__('Save Changes', 'woocommerce').'">';
        echo '</p>';
    }
    
    function donation_button()
    {
        ?>
        <p><?php _e('If you like or find this plugin useful, please consider donating something.'); ?></p>

        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="hosted_button_id" value="8L2PHLURJMC8Y">
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        <img alt="" border="0" src="https://www.paypalobjects.com/sv_SE/i/scr/pixel.gif" width="1" height="1">
        </form>
        <?php
    }
    
    /* ------------------------------------------------------------------------ *
     * Generic Field Callbacks
     * ------------------------------------------------------------------------ */

    function parse_args($args)
    {
        $defaults = array(
            'key'           => '',
            'value'         => '',
            'default'       => '',
            'options'       => array(),
            'label'         => '',
            'description'   => '',
        );
        
        return wp_parse_args($args, $defaults);
    }
    
    function checkbox_option_callback($args)
    {
        $args = $this->parse_args($args);
        
        if(empty($args['value'])) {
            $args['value'] = (empty($this->plugin_options[$args['key']]) ? $args['default'] : $this->plugin_options[$args['key']] );
        }
        
        echo '<label class="shipping-method"> ';
        echo '<input type="checkbox" name="' . $this->plugin_slug . '_options['.$args['key'].']" id="' . $this->plugin_slug . '_options_'.$args['key'].'" value="1" ' . ($args['value'] == 1 ? 'checked="checked"' : '') . '/>';
        echo '<span>' . $args['label'] . '</span>';
        echo '</label>';

        if(!empty($args['description'])) {
            echo '<p style="clear: both">' . $args['description'] . '</p>';
        }
    }
    
    function text_option_callback($args)
    {
        $args = $this->parse_args($args);
        
        if(empty($args['value'])) {
            $args['value'] = (empty($this->plugin_options[$args['key']]) ? $args['default'] : $this->plugin_options[$args['key']] );
        }
        
        echo '<label class="shipping-method"> ';
        echo '<input type="text" size="50" name="' . $this->plugin_slug . '_options['.$args['key'].']" id="' . $this->plugin_slug . '_options_'.$args['key'].'" value="'.$args['value'].'" />';
        //echo '<span>' . $args['label'] . '</span>';
        echo '</label>';

        if(!empty($args['description'])) {
            echo '<p style="clear: both">' . $args['description'] . '</p>';
        }
    }
    
    function select_option_callback($args)
    {
        $args = $this->parse_args($args);
        
        if(empty($args['value'])) {
            $args['value'] = (empty($this->plugin_options[$args['key']]) ? $args['default'] : $this->plugin_options[$args['key']] );
        }

        echo '<label class="shipping-method"> ';
        
        echo '<select name="' . $this->plugin_slug . '_options['.$args['key'].']" id="' . $this->plugin_slug . '_options_'.$args['key'].'" />';
        
        foreach( $args['options'] AS $option )
        {
            echo '<option value="'.$option['value'].'" >'.$option['label'].'</option>';
        }
        
        echo '</label>';

        if(!empty($args['description'])) {
            echo '<p style="clear: both">' . $args['description'] . '</p>';
        }
    }
    
    
    
    /* ------------------------------------------------------------------------ *
     * Setting Callbacks
     * ------------------------------------------------------------------------ */
    
    function validate_input($input) 
    {
        if (!is_array($input)) {
            return false; 
        }

        if (empty($input['exclude_ids'])) {
            $input['exclude_ids'] = array();
        }

        $output = $input;

        //Apply filter_input on all values
        array_walk_recursive($output, array($this, 'filter_input'));

        // Return the array processing any additional functions filtered by this action
        return apply_filters($this->plugin_slug . '_validate_input', $output, $input);
    }

    function filter_input(&$input) 
    {

        $input = strip_tags(stripslashes($input));
    }

}

?>