=== WooCommerce Pricefiles ===
Contributors: pekz0r, Doxwork
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8L2PHLURJMC8Y
Tags: WooCommerce, Price comparison, Pricefiles, Prisjakt, Pricerunner, Pricespy, Google Shopping, Google Merchant Center, Kelkoo
Requires at least: 3.5.1
Tested up to: 4.2
Stable tag: 0.1.11

This plugins automates the generation of pricefiles and product feeds for price comparison and product listing services.

== Description ==

This plugins automates the generation of pricefiles for price comparison services. 

The plugin supports Prisjakt/Pricespy and Pricerunner. Support for Google Shopping/Google Merchant Center and Kelkoo was planned, but is on hold due to limited demand. If you want this, send me a message and if enough people do, I will reconsider.

This plugin required PHP version 5.3+.

= Development =

All development of this plugin occurs on [GitHub](https://github.com/pelmered/WooCommerce-Pricefiles "WooCommerce Pricefiles on GitHub"). Please help me develop this by forking and sending pull requests.

This plugin is developed by [Peter Elmered](https://github.com/pelmered/)

Contributors:

* Mattias Päivärinta at [Doxwork](http://doxwork.com/)

== Installation ==

1. Upload the plugin foler to the `/wp-content/plugins/` directory in your WordPress installation.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to 'WooCommerce' and then 'Pricefiles' in the sub menu to configure the plugin.

== Screenshots ==

1. Screenshot of the options page
2. Screenshot of the added fields on the product admin page

== Upgrade Notice ==

Please keep the plugin up to date to ensure that is functions properly.

== Changelog ==

= 0.1.11 =
Fix: Error in shipping tax calculation
Fix: Error in empty check for manufacturer and category fields in product settings
Tested with WordPress 4.2 alpha.

= 0.1.10 =
Fix: Data columns did't match the headers in some cases, for example if there was no product image.
Fix: List floating point zero as '0' insted of empty string.
Fix: Better tax calculation for shipping
Fix: Ean header missing in Prisjakt file
Various other small bugfixes.
Big thanks to Mattias Päivärinta at Doxwork for contributing to this version.

= 0.1.9 =
Fix: Removed deprication notices with WC 2.2+.
Fix: Removed PHP warnings when there are no product categories.

= 0.1.8 =
Fix: Fixed form error on settings page.

= 0.1.7 =
Fix: Handle case where set_time_limit() is not available more gracefully

= 0.1.6 =
Fix: Fixed issue with static loading of the plugin during actrivationa nd deactivation

= 0.1.5 =
Fix: Categories does not show in pricefile (again). Plugin initialized too early to access terms created by WooCommerce.

= 0.1.4 =
Fix: Errors in pricefile
Fix: Plugin loader issues

= 0.1.3 =
Fix: Categories does not show in pricefile

Fix: WooCommerce 2.2 compatibility

= 0.1.2 =
Fix: Do not convert 0 into "" (empty string) for prices in pricefile(i.e. free shipping)

= 0.1.1 =
Bugfixes

= 0.1.0 =
First public release


