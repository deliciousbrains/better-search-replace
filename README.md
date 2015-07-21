# Better Search Replace #
**Contributors:** ExpandedFronts

**Tags:** search replace, update urls, database, search replace database, update database urls, update live url

**Requires at least:** 3.0.1

**Tested up to:** 4.2.2

**Stable tag:** trunk

**License:** GPLv3 or later

**License URI:** http://www.gnu.org/licenses/gpl-3.0.html


A simple plugin for updating URLs or other text in a database.

## Description ##

When moving your WordPress site to a new domain or server, you will likely run into a need to run a search/replace on the database for everything to work correctly. Fortunately, there are several plugins available for this task, however, all have a different approach to a few key features. This plugin is an attempt to consolidate the best features from these plugins, incorporating the following features in a simple, ad-free plugin:

* Serialization support for all tables
* The ability to select specific tables
* The ability to run a "dry run" to see how many fields will be updated
* No server requirements aside from a running installation of WordPress

The search/replace functionality is heavily based on interconnect/it's great and open-source Search Replace DB script, modified to use WordPress native database functions to ensure compatibility.

## Installation ##

Install Better Search Replace like you would install any other WordPress plugin.

Dashboard Method:

1. Login to your WordPress admin and go to Plugins -> Add New
2. Type "Better Search Replace" in the search bar and select this plugin
3. Click "Install", and then "Activate Plugin"


Upload Method:

1. Upload 'better-search-replace.php' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

## Changelog ##

### 1.1.1 ###
* Added ability to change max page size
* Decreased default page size to prevent white screen issue on some environments

### 1.1 ###
* Added ability to change capability required to use plugin
* Small bugfixes and translation fixes

### 1.0.6 ###
* Added table sizes to the database table listing
* Added French translation (props @Jean Philippe)

### 1.0.5 ###
* Added support for case-insensitive searches
* Added German translation (props @Linus Ziegenhagen)

### 1.0.4 ###
* Potential security fixes

### 1.0.3 ###
* Fixed issue with searching for special characters like '\'
* Fixed bug with replacing some objects

### 1.0.2 ###
* Fixed untranslateable strings on submit button and submenu page.

### 1.0.1 ###
* Fixed issue with loading translations and added Spanish translation (props Eduardo Larequi)
* Fixed bug with reporting timing
* Updated to use "Dry Run" as default
* Added support for WordPress Multisite (see FAQs for more info)

### 1.0 ###
* Initial release
