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
 * Version:     0.1.11
 * Author:      Peter Elmered
 * Author URI:  http://elmered.com
 * Text Domain: woocommerce-pricefiles
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

/*
//For debuging
error_reporting(E_ALL);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require('define.php');
define('WP_PRICEFILES_PLUGIN_NAME', untrailingslashit(plugin_basename(__FILE__)));

if ( ! class_exists( 'WC_Pricefiles' ) )
{
    require_once( WP_PRICEFILES_PLUGIN_PATH .'includes/pricefiles.php' );
}

add_action( 'plugins_loaded', 'WC_Pricefiles' );    

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook(__FILE__, array(WC_Pricefiles(), 'activate'));
//Deletes all data if plugin deactivated
register_deactivation_hook(__FILE__, array(WC_Pricefiles(), 'deactivate'));

function WC_Pricefiles()
{
    require_once( WP_PRICEFILES_PLUGIN_PATH .'includes/pricefiles.php' );

    return WC_Pricefiles::get_instance();
}

