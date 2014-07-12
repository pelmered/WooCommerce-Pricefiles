<?php
/**
 * @package   woocommerce-pricefiles
 * @author    Peter Elmered <peter@elmered.com>
 * @license   GPL-2.0+
 * @link      http://elmered.com
 * @copyright 2014 Peter Elmered
 *
 * @wordpress-plugin
 * Plugin Name: WooCommerce Pricefiles
 * Plugin URI:  http://wordpress.org/plugins/woocommerce-pricefiles/
 * Description: Connect your WooCommerce shop to Price comparison sites with Pricefiles. Supports: Prisjakt / PriceSpy and Pricerunner
 * Version:     0.1.0
 * Author:      Peter Elmered
 * Author URI:  http://elmered.com
 * Text Domain: woocommerce-pricefiles
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require('define.php');
    
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
  
    define('WP_PRICEFILES_PLUGIN_NAME', untrailingslashit(plugin_basename(__FILE__)));

    if ( ! class_exists( 'WC_Pricefiles' ) )
    {
        require_once( WP_PRICEFILES_PLUGIN_PATH .'includes/pricefiles.php' );

        $wc_pricefiles = WC_Pricefiles::get_instance();

        // Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
        register_activation_hook(__FILE__, array($wc_pricefiles, 'activate'));
        //Deletes all data if plugin deactivated
        register_deactivation_hook(__FILE__, array($wc_pricefiles, 'deactivate'));

        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $wc_pricefiles, 'action_links' ) );
        
        
        
    }

} 
else
{
    //If WooCommerce is not install, hanldle errors and display notices

    //Deactivate plugin action
    if(!empty($_GET['wcpf-deactivate-woocommerce-pricefiles']) && $_GET['wcpf-deactivate-woocommerce-pricefiles'] == 1)
    {
        add_action('init', 'wpcf_deactivate_plugin');
        add_action('admin_notices', 'wpcf_deactivate_plugin_notice');
    }
    //Activate WooCommerce action
    if(!empty($_GET['wcpf-activate-woocommerce']) && $_GET['wcpf-activate-woocommerce'] == 1)
    {
        add_action('init', 'wpcf_activate_woocommerce');
        add_action('admin_notices', 'wpcf_activate_woocommerce_notice');
    }
    else
    {
        //Display normal "WooCommerce not installed" notice
        add_action('admin_notices', 'wcpf_woocommerce_not_active_notice');
    }
    
    function wcpf_woocommerce_not_active_notice()
    {
        ?>
        <div class="updated fade">
            <p><?php 
            printf(__('The Pricefiles plugin requires the plugin %sWooCommerce%s to work. Please install WooCommerce or %sdeactive%s this plugin.', WC_PRICEFILES_PLUGIN_SLUG), 
                '<a href="http://wordpress.org/plugins/woocommerce/">', '</a>',
                '<a href="?deactivate-woocommerce-pricefiles=1">', '</a>'
            ); ?></p>
            <?php 
            //WooCommerce is installed, but not actiated. Add adction for activating.
            if( file_exists(dirname(plugin_dir_path( __FILE__ )).'/woocommerce/woocommerce.php') ) : ?>
            <p><?php printf(__('WooCommerce seams to be installed but not activated. %sClick here to activate%s.', WC_PRICEFILES_PLUGIN_SLUG),
                '<a href="?wcpf-activate-woocommerce=1">','</a>'
            ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    function wpcf_deactivate_plugin()
    {
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }
    function wpcf_deactivate_plugin_notice()
    {
        ?>
        <div class="updated fade">
            <p><?php _e('The Pricefiles plugin was deactivated.', WC_PRICEFILES_PLUGIN_SLUG); ?></p>
        </div>
        <?php
    }
    function wpcf_activate_woocommerce()
    {
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }
    function wpcf_activate_woocommerce_notice()
    {
        ?>
        <div class="updated fade">
            <p><?php _e('WooCommerce was activated.', WC_PRICEFILES_PLUGIN_SLUG); ?></p>
        </div>
        <?php
    }
}

//Function for getting an sigleton instance of the plugin main class
function WC_Pricefiles()
{
    require_once( WP_PRICEFILES_PLUGIN_PATH .'includes/pricefiles.php' );

    return WC_Pricefiles::get_instance();
}

