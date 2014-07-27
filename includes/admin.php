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
        echo '<input type="submit" name="save_attribute" id="submit" class="button-primary" value="'.__('Save Changes', 'woocommerce' ).'">';
        echo '</p>';
    }

    /* ------------------------------------------------------------------------ *
     * Setting Callbacks
     * ------------------------------------------------------------------------ */

    function validate_input($input) 
    {
        if (!is_array($input))
            return false;

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