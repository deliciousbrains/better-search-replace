# Better Search Replace

Free WordPress plugin for safe, serialization-aware database search and replace operations.

## Overview

When moving your WordPress site to a new domain or server, you will likely run into a need to run a search/replace on the database for everything to work correctly. Fortunately, there are several plugins available for this task, however, all have a different approach to a few key features. 

Better Search Replace  consolidates the best features from these plugins, incorporating them in a simple, ad-free plugin with extended premium functionality.

The search/replace functionality is heavily based on interconnect/it's great and open-source Search Replace DB script, modified to use WordPress native database functions to ensure compatibility.

## Features

### Core Features

- **Serialization support for all tables** - Safely handles PHP serialized data without breaking it
- **Select specific tables** - Choose which database tables to search
- **Dry run mode** - Preview exactly how many fields will be updated before making changes
- **Case-insensitive search option** - Find matches regardless of case
- **No server requirements** - Works with any host running WordPress
- **WordPress Multisite support** - Compatible with multisite installations

## Installation

### Dashboard Method

1. Login to your WordPress admin and go to **Plugins → Add New**
2. Type "Better Search Replace" in the search bar and select this plugin
3. Click **Install**, and then **Activate Plugin**

### Upload Method

1. Unzip the plugin and upload the `better-search-replace` folder to your `wp-content/plugins` directory
2. Activate the plugin through the **Plugins** menu in WordPress

## Usage

Once activated, Better Search Replace will add a page under the **Tools** menu in your WordPress admin.

### Basic Search and Replace

1. Navigate to **Tools → Better Search Replace**
2. Enter your search term in the "Search for" field
3. Enter your replacement text in the "Replace with" field
4. Select the tables you want to search
5. Check **Run as dry run** to preview changes without modifying the database
6. Click **Run Search/Replace**

### Viewing Results

After running a dry run, the plugin will display:

- Number of tables searched
- Number of cells containing the search term
- Number of rows that would be updated
- Preview of changes that will be made

## Migration Guide

### Changing URLs When Moving Sites

If you're moving your site from one server to another and changing the URL of your WordPress installation, follow this approach to migrate safely without affecting the old site:

1. **Backup the database** on your current site
2. **Install the database** on your new host
3. On the new host, **define the new site URL** in the `wp-config.php` file:
   ```php
   define('WP_HOME','http://new-url.com');
   define('WP_SITEURL','http://new-url.com');
   ```
   See [WordPress documentation](http://codex.wordpress.org/Changing_The_Site_URL#Edit_wp-config.php) for details
4. **Log in** at your new admin URL and run Better Search Replace on the old site URL for the new site URL
5. **Delete the site_url constants** you added to `wp-config.php`. You may also need to regenerate your `.htaccess` by going to **Settings → Permalinks** and saving the settings

More information on moving WordPress can be found in the [WordPress Codex](http://codex.wordpress.org/Moving_WordPress).

## FAQ

### Is my host supported?

Yes! This plugin should be compatible with any host running WordPress.

### Can I damage my site with this plugin?

Yes! Entering a wrong search or replace string could damage your database. Because of this, it is always advisable to have a backup of your database before using this plugin. 

The Pro version includes built-in backup functionality to help protect your data.

### How does this work on WordPress Multisite?

When running this plugin on a WordPress Multisite installation, it will only be loaded and visible for Network admins. 

Network admins can:
- Go to the dashboard of any subsite to run a search/replace on just the tables for that subsite
- Go to the dashboard of the main/base site to run a search/replace on all tables

### I get a white screen when using this plugin

This is likely an issue with your PHP memory limit. Try temporarily increasing it by defining the memory limit in your `wp-config.php` file:

```php
define('WP_MEMORY_LIMIT', '256M');
```

See [WordPress documentation](http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP) for more details.

Alternatively, if you were searching across multiple tables, try searching on fewer tables at a time to load less into memory.

## Requirements

- **WordPress**: 6.2 or higher
- **PHP**: 8.1 or higher
- **Browser**: Modern browser with JavaScript enabled

## Support & Resources

- [Product Website](https://bettersearchreplace.com/) - Documentation and product information
- [GitHub Repository](https://github.com/deliciousbrains/better-search-replace) - Source code and issue tracking
- [Pro Version](https://bettersearchreplace.com/) - Premium version of the plugin

## Contributing

Want to contribute? Feel free to open an issue or submit a pull request on [GitHub](https://github.com/deliciousbrains/better-search-replace-pro/).

## License

Better Search Replace is licensed under the General Public License v3 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
>
> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

See `LICENSE.txt` in the plugin root directory for the full license text.
