<?php
/**
 * Fired when the plugin is uninstalled.
 *
* @package   wc_pricefiles
 * @author    Peter Elmered <peter@elmered.com>
 * @link      http://extendwp.com
 * @copyright 2013 Peter Elmered
  */

// If uninstall, not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

//Cleanup all registerd options
$plugin_slug = 'woocommerce-pricefiles';

delete_option($plugin_slug.'_options');
delete_option($plugin_slug.'_category_mappings');
