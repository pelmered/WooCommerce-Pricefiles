<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of misc
 *
 * @author peter
 */
class WC_Pricefiles_Misc
{
    

    function get_woocomemrce_path()
    {
        return dirname(dirname(plugin_dir_path( __FILE__ ))).'/woocommerce/woocommerce.php';
    }
    
    function woocommerce_not_active_notice()
    {
        ?>
        <div class="updated fade">
            <p><?php 
            printf(__('The Pricefiles plugin requires the plugin %sWooCommerce%s to work. Please install WooCommerce or %sdeactive%s this plugin.', WC_PRICEFILES_PLUGIN_SLUG), 
                '<a href="http://wordpress.org/plugins/woocommerce/">', '</a>',
                '<a href="?deactivate-woocommerce-pricefiles=1">', '</a>'
            ); ?></p>
            <?php /*if( file_exists( $this->get_woocomemrce_path() ) ) : ?>
            <p><?php printf(__('WooCommerce seams to be installed but not activated. %sClick here to activate%s.', WC_PRICEFILES_PLUGIN_SLUG),
                '<a href="?wcpf-activate-woocommerce=1">','</a>'
            ); ?></p>
            <?php endif; */ ?>
            
        </div>
        <?php
    }
    
    function deactivate_plugin()
    {
        
        
        deactivate_plugins( plugin_basename(__FILE__) );
    }
    function deactivate_plugin_notice()
    {
        ?>
        <div class="updated fade">
            <p><?php _e('The Pricefiles plugin was deactivated.', WC_PRICEFILES_PLUGIN_SLUG); ?></p>
        </div>
        <?php
    }
    /*
    function activate_woocommerce()
    {
        activate_plugins( get_woocomemrce_path() );
    }
    function activate_woocommerce_notice()
    {
        ?>
        <div class="updated fade">
            <p><?php _e('WooCommerce was activated.', WC_PRICEFILES_PLUGIN_SLUG); ?></p>
        </div>
        <?php
    }
    */
}
