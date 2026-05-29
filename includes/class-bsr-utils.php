<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility functionality for the plugin
 *
 * @since      1.4.3
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/includes
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) {
	exit;
}

class BSR_Utils {
	const BSR_URL = 'https://bettersearchreplace.com';
	const WPE_URL = 'https://wpengine.com';

	/** Nonce action for tools.php?page=better-search-replace screen query args (redirects, tab links). */
	const TOOLS_SCREEN_NONCE_ACTION = 'bsr_tools_screen';

	/**
	 * Create an external link for given URL.
	 *
	 * @param string $url
	 * @param string $text
	 *
	 * @return string
	 */
	public static function external_link( $url, $text ) {
		return sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $url ), esc_html( $text ) );
	}

	/**
	 * Generate Better Search Replace site URL with correct UTM tags.
	 *
	 * @param string $path
	 * @param array  $args
	 * @param string $hash
	 *
	 * @return string
	 */
	public static function bsr_url( $path, $args = array(), $hash = '' ) {
		$args = wp_parse_args(
			$args,
			array( 'utm_medium' => 'insideplugin' )
		);
		$args = array_map( 'urlencode', $args );
		$url  = trailingslashit( self::BSR_URL ) . ltrim( $path, '/' );
		$url  = add_query_arg( $args, $url );
		if ( $hash ) {
			$url .= '#' . $hash;
		}

		return $url;
	}

	/**
	 * Generate WP Engine site URL with correct UTM tags.
	 *
	 * @param string $path
	 * @param array  $args
	 * @param string $hash
	 *
	 * @return string
	 */
	public static function wpe_url( $path = '', $args = array(), $hash = '' ) {
		$args = wp_parse_args(
			$args,
			[
				'utm_medium'   => 'referral',
				'utm_campaign' => 'bx_prod_referral',
			]
		);
		$args = array_map( 'urlencode', $args );
		$url  = trailingslashit( self::WPE_URL ) . ltrim( $path, '/' );
		$url  = add_query_arg( $args, $url );

		if ( $hash ) {
			$url .= '#' . $hash;
		}

		return $url;
	}

	/**
	 * Get the plugin page url
	 *
	 * @return string
	 */
	public static function plugin_page_url() {
		return menu_page_url( 'better-search-replace', false );
	}

	/**
	 * Tools → Better Search Replace URL with a nonce for GET query args on that screen.
	 *
	 * @param array $args Query arguments (merged with page=better-search-replace).
	 * @return string Unescaped URL; pass through esc_url() for HTML or esc_url_raw() for redirects.
	 */
	public static function tools_page_url( $args = array() ) {
		$url = add_query_arg(
			array_merge(
				array( 'page' => 'better-search-replace' ),
				$args
			),
			admin_url( 'tools.php' )
		);

		return add_query_arg( '_wpnonce', wp_create_nonce( self::TOOLS_SCREEN_NONCE_ACTION ), $url );
	}

	/**
	 * Whether the current tools screen GET request may use BSR-specific query args (result, import, profile, etc.).
	 * Missing nonce is allowed for backward compatibility when the user is already authorized.
	 *
	 * @return bool
	 */
	public static function validate_tools_screen_get() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended -- Nonce is verified below when present; capability is always required.
		if ( ! bsr_enabled_for_user() ) {
			return false;
		}
		if ( ! isset( $_GET['_wpnonce'] ) ) {
			// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
			return true;
		}

		$valid = (bool) wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_GET['_wpnonce'] ) ), self::TOOLS_SCREEN_NONCE_ACTION );
		// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended

		return $valid;
	}

	/**
	 * Is current admin screen for bsr.
	 *
	 * @return bool
	 */
	public static function is_bsr_screen() {
		$screen = get_current_screen();

		return $screen->base === 'tools_page_better-search-replace';
	}

	/**
	 * Ensures intent by verifying that a user was referred from another admin
	 * page with the correct security nonce, and that user has the capability
	 * level to use the plugin.
	 *
	 * @param int|string $action    The nonce action.
	 * @param string     $query_arg Key to check for nonce in `$_REQUEST`.
	 * @param bool       $die       Whether to die on failure (default false so callers can return JSON/redirects).
	 *
	 * @return bool
	 */
	public static function check_admin_referer( $action, $query_arg, $die = false ) {
		return check_admin_referer( $action, $query_arg, $die ) && bsr_enabled_for_user();
	}
}
