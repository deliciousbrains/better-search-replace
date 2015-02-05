=== Better Search Replace ===
Contributors: ExpandedFronts
Tags: search replace, update urls, database, search replace database, update database urls, update live url
Requires at least: 3.0.1
Tested up to: 4.1
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A simple plugin for updating URLs or other text in a database.

== Description ==

When moving your WordPress site to a new domain or server, you will likely run into a need to run a search/replace on the database for everything to work correctly. Fortunately, there are several plugins available for this task, however, all have a different approach to a few key features. This plugin consolidates the best features from these plugins, incorporating the following features in a simple, ad-free plugin:

* Serialization support for all tables
* The ability to select specific tables
* The ability to run a "dry run" to see how many fields will be updated
* No server requirements aside from a running installation of WordPress
* WordPress Multisite support

The search/replace functionality is heavily based on interconnect/it's great and open-source Search Replace DB script, modified to use WordPress native database functions to ensure compatibility.

== Installation ==

Install Better Search Replace like you would install any other WordPress plugin.

Dashboard Method:

1. Login to your WordPress admin and go to Plugins -> Add New
2. Type "Better Search Replace" in the search bar and select this plugin
3. Click "Install", and then "Activate Plugin"


Upload Method:

1. Unzip the plugin and upload the "better-search-replace" folder to your 'wp-content/plugins' directory
2. Activate the plugin through the Plugins menu in WordPress

== Frequently Asked Questions ==

= Using Better Search Replace =

Once activated, Better Search Replace will add a page under the "Tools" menu page in your WordPress admin.

= Is my host supported? =

Yes! This plugin should be compatible with any host.

= Can I damage my site with this plugin? =

Yes! Entering a wrong search or replace string could damage your database. Because of this, it is always adviseable to have a backup of your database before using this plugin.

= How does this work on WordPress Multisite? =

When running this plugin on a WordPress Multisite installation, it will only be loaded and visible for Network admins. Network admins can go to the dashboard of any subsite to run a search/replace on just the tables for that subsite, or go to the dashboard of the main/base site to run a search/replace on all tables.

== Screenshots ==

1. The Better Search Replace page added to the "Tools" menu
2. After running a search/replace dry-run.

== Changelog ==

= 1.0.2 =
* Fixed untranslateable strings on submit button and submenu page.

= 1.0.1 =
* Fixed issue with loading translations and added Spanish translation (props Eduardo Larequi)
* Fixed bug with reporting timing
* Updated to use "Dry Run" as default
* Added support for WordPress Multisite (see FAQs for more info)

= 1.0 =
* Initial release
