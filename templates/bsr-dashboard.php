<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays the main Better Search Replace page under Tools -> Better Search Replace.
 *
 * @link       https://bettersearchreplace.com
 * @since      1.0.0
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/templates
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template partial loaded in BSR admin context; variables are file scope, not WordPress globals.

// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended -- Tab is sanitized and matched against allowlist; optional tools-screen nonce verified when present.
$allowed_tabs = array( 'bsr_search_replace', 'bsr_settings', 'bsr_help' );
$active_tab   = 'bsr_search_replace';
$tab_candidate  = '';
if ( isset( $_GET['tab'] ) ) {
	$tab_candidate = sanitize_key( wp_unslash( (string) $_GET['tab'] ) );
}
if ( '' !== $tab_candidate ) {
	$nonce_ok = true;
	if ( isset( $_GET['_wpnonce'] ) ) {
		$nonce_ok = wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_GET['_wpnonce'] ) ), BSR_Utils::TOOLS_SCREEN_NONCE_ACTION );
	}
	if ( $nonce_ok && in_array( $tab_candidate, $allowed_tabs, true ) ) {
		$active_tab = $tab_candidate;
	}
}
// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended

if ( 'bsr_settings' === $active_tab ) {
	$action = get_admin_url() . 'options.php';
} else {
	$action = get_admin_url() . 'admin-post.php';
}

$tab_urls = array(
	'bsr_search_replace' => BSR_Utils::tools_page_url( array( 'tab' => 'bsr_search_replace' ) ),
	'bsr_settings'      => BSR_Utils::tools_page_url( array( 'tab' => 'bsr_settings' ) ),
	'bsr_help'          => BSR_Utils::tools_page_url( array( 'tab' => 'bsr_help' ) ),
);

$logo_src = plugin_dir_url( __FILE__ ) . '../assets/svg/logo-bsr.svg';
$icon_src = plugin_dir_url( __FILE__ ) . '../assets/svg/icon-upgrade.svg';
$upgrade  = 'https://deliciousbrains.com/better-search-replace/upgrade/?utm_source=insideplugin&utm_medium=web&utm_content=header&utm_campaign=bsr-to-migrate';

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

?>

<div class="wrap" style="display: grid;">

	<div class="bsr-notice-container">
		<h2 class="hidden"></h2>
	</div>

	<div class="header">

		<div class="content">
			<a href="<?php echo esc_url( $tab_urls['bsr_search_replace'] ); ?>">
				<img src="<?php echo esc_url( $logo_src ); ?>" class="logo" alt="">
			</a>
			<a href="<?php echo esc_url( $upgrade ); ?>" target="_blank" rel="noopener noreferrer" class="upgrade-notice">
				<img src="<?php echo esc_url( $icon_src ); ?>" alt="">
				<?php esc_html_e( 'Upgrade now and get 50% off', 'better-search-replace' ); ?>
			</a>
		</div>

	<?php settings_errors(); ?>

	<?php BSR_Admin::render_result(); ?>

	</div>

	<div class="nav-tab-wrapper">
		<ul>
			<li><a href="<?php echo esc_url( $tab_urls['bsr_search_replace'] ); ?>" class="nav-tab <?php echo esc_attr( 'bsr_search_replace' === $active_tab ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e( 'Search/Replace', 'better-search-replace' ); ?></a></li>
			<li><a href="<?php echo esc_url( $tab_urls['bsr_settings'] ); ?>" class="nav-tab <?php echo esc_attr( 'bsr_settings' === $active_tab ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e( 'Settings', 'better-search-replace' ); ?></a></li>
			<li><a href="<?php echo esc_url( $tab_urls['bsr_help'] ); ?>" class="nav-tab <?php echo esc_attr( 'bsr_help' === $active_tab ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e( 'Help', 'better-search-replace' ); ?></a></li>
		</ul>
	</div>

	<form class="bsr-action-form" action="<?php echo esc_url( $action ); ?>" method="POST">

		<?php
		// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template partial; file scope of this include.
		$bsr_template = BSR_Templates_Helper::get_tab_template( $active_tab );
		include $bsr_template;
		// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		?>

	</form>

</div><!-- /.wrap -->
