<?php
/**
 * Better Search Replace
 *
 * This plugin improves upon the database search/replace functionality offered
 * by some other plugins, offering serialization support, the ability to
 * select specific tables, and the ability to run a dry run.
 *
 * @since             1.0.0
 * @package           Better_Search_Replace
 *
 * @wordpress-plugin
 * Plugin Name:       Better Search Replace
 * Plugin URI:        https://bettersearchreplace.com
 * Description:       A small plugin for running a search/replace on your WordPress database.
 * Version:           1.4.11
 * Requires at least: 6.2
 * Requires PHP:      8.1
 * Author:            WP Engine
 * Author URI:        https://bettersearchreplace.com
 * Update URI:        false
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       better-search-replace
 * Domain Path:       /languages
 * Network:           true
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// If this file was called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'run_better_search_replace' ) ) {
	// Defines the path to the main plugin file.
	define( 'BSR_FILE', __FILE__ );

	// Defines the path to be used for includes.
	define( 'BSR_PATH', plugin_dir_path( BSR_FILE ) );

	// Defines the URL to the plugin.
	define( 'BSR_URL', plugin_dir_url( BSR_FILE ) );

	// Defines the current version of the plugin.
	define( 'BSR_VERSION', '1.4.11' );

	// Defines the name of the plugin.
	define( 'BSR_NAME', 'Better Search Replace' );

	/**
	 * Get plugin data from plugin header.
	 *
	 * @param string $header
	 *
	 * @return string
	 * @since 1.4.11
	 */
	function bsr_get_plugin_data( $header ) {
		$data = get_file_data( __FILE__, array(
			'Name'        => 'Plugin Name',
			'RequiresWP'  => 'Requires at least',
			'RequiresPHP' => 'Requires PHP',
		), 'plugin' );

		if ( empty( $data[ $header ] ) ) {
			return '';
		}

		return $data[ $header ];
	}

	/**
	 * Check if WordPress version meets minimum requirement.
	 *
	 * @return bool True if WordPress version is sufficient, false otherwise.
	 * @since 1.4.11
	 */
	function bsr_check_wp_version() {
		global $wp_version;

		if ( version_compare( $wp_version, bsr_get_plugin_data( 'RequiresWP' ) ) === -1 ) {
			add_action( 'admin_notices', 'bsr_wp_version_notice' );
			add_action( 'network_admin_notices', 'bsr_wp_version_notice' );

			return false;
		}

		return true;
	}

	/**
	 * Check if PHP version meets minimum requirement.
	 *
	 * @return bool True if PHP version is sufficient, false otherwise.
	 * @since 1.4.11
	 */
	function bsr_check_php_version() {
		if ( version_compare( PHP_VERSION, bsr_get_plugin_data( 'RequiresPHP' ) ) === -1 ) {
			add_action( 'admin_notices', 'bsr_php_version_notice' );
			add_action( 'network_admin_notices', 'bsr_php_version_notice' );

			return false;
		}

		return true;
	}

	/**
	 * Display admin notice for insufficient WordPress version.
	 *
	 * @since 1.4.11
	 */
	function bsr_wp_version_notice() {
		global $wp_version;
		?>
		<div class="notice notice-error">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
					/* translators: 1: Plugin name, 2: Required WordPress version, 3: Current WordPress version */
						__(
							'<strong>%1$s</strong> requires WordPress <strong>%2$s</strong> or higher. You are currently running WordPress <strong>%3$s</strong>. Please upgrade WordPress to activate this plugin.',
							'better-search-replace'
						),
						bsr_get_plugin_data( 'Name' ),
						bsr_get_plugin_data( 'RequiresWP' ),
						esc_html( $wp_version )
					)
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Display admin notice for insufficient PHP version.
	 *
	 * @since 1.4.11
	 */
	function bsr_php_version_notice() {
		?>
		<div class="notice notice-error">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
					/* translators: 1: Plugin name, 2: Required PHP version, 3: Current PHP version */
						__(
							'<strong>%1$s</strong> requires PHP <strong>%2$s</strong> or higher. You are currently running PHP <strong>%3$s</strong>. Please contact your web host to upgrade PHP to activate this plugin.',
							'better-search-replace'
						),
						bsr_get_plugin_data( 'Name' ),
						bsr_get_plugin_data( 'RequiresPHP' ),
						esc_html( PHP_VERSION )
					)
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since 1.0.0
	 */
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Bootstrap entry; guarded by function_exists in bootstrap.
	function run_better_search_replace() {
		// Check version requirements before loading the plugin.
		if ( ! bsr_check_wp_version() || ! bsr_check_php_version() ) {
			return;
		}

		if ( bsr_enabled_for_user() ) {
			/**
			 * The core plugin class that is used to define internationalization,
			 * dashboard-specific hooks, and public-facing site hooks.
			 */
			require BSR_PATH . 'includes/class-bsr-main.php';
			$plugin = new Better_Search_Replace();
			$plugin->run();
		}
	}

	add_action( 'after_setup_theme', 'run_better_search_replace' );
}

if ( ! function_exists( 'bsr_enabled_for_user' ) ) {
	/**
	 * Is the current user allowed to use BSR?
	 *
	 * @return bool
	 */
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Public helper; guarded by function_exists in bootstrap.
	function bsr_enabled_for_user() {
		// Allows for overriding the capability required to run the plugin.
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy filter name; public API for capability override.
		$cap = apply_filters( 'bsr_capability', 'manage_options' );

		return current_user_can( $cap );
	}
}

if ( file_exists( BSR_PATH . 'ext/bsr-ext-functions.php' ) ) {
	require_once BSR_PATH . 'ext/bsr-ext-functions.php';
}
