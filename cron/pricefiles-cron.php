<?php
/**
 * WordPress Cron Implementation for hosts, which do not offer CRON or for which
 * the user has not set up a CRON job pointing to this file.
 *
 * The HTTP request to this file will not slow down the visitor who happens to
 * visit when the cron job is needed to run.
 *
 * @package WordPress
 */

//Set GET paramenters so that the pricefile script runs
$_GET['pricefile'] = 'all';
$_GET['refresh'] = '1';
$_GET['output'] = 'json';

ignore_user_abort(true);

if ( !empty($_POST) || defined('DOING_AJAX') || defined('DOING_CRON') )
	die();



/**
 * Tell WordPress we are doing the CRON task.
 *
 * @var bool
 */
define('DOING_CRON', true);

if ( !defined('ABSPATH') ) {
    /** Set up WordPress environment */
    require_once( dirname( __FILE__ ) . '/wp-load.php' );
}



//WC_Pricefiles();

die();
