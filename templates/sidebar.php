<?php
/**
 * Upgrade sidebar partial.
 *
 * @package Better_Search_Replace
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BSR_PATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template partial loaded in BSR admin context; variables are file scope, not WordPress globals.
$birds = plugin_dir_url( __FILE__ ) . '../assets/svg/mdb-birds.svg';
$upgrade_href = 'https://deliciousbrains.com/better-search-replace/upgrade/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=bsr-to-migrate';
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

?>
<div class="upgrade-sidebar">
	<img src="<?php echo esc_url( $birds ); ?>" alt="">
	<div class="content">
		<h3><?php esc_html_e( 'Upgrade', 'better-search-replace' ); ?></h3>
		<p><?php esc_html_e( 'Gain access to more database and migration features', 'better-search-replace' ); ?></p>

		<ul>
			<li>
				<p><?php esc_html_e( 'Preview database changes before they are saved', 'better-search-replace' ); ?></p>
			</li>
			<li>
				<p><?php esc_html_e( 'Use regular expressions for complex string replacements', 'better-search-replace' ); ?></p>
			</li>
			<li>
				<p><?php esc_html_e( 'Migrate full sites including themes, plugins, media, and database', 'better-search-replace' ); ?></p>
			</li>
			<li>
				<p><?php esc_html_e( 'Export and import WordPress databases', 'better-search-replace' ); ?></p>
			</li>
			<li>
				<p><?php esc_html_e( 'Email support', 'better-search-replace' ); ?></p>
			</li>
		</ul>

		<p class="upgrade-offer-text"><?php echo wp_kses_post( __( 'Get up to <span>50% off</span> your first year!', 'better-search-replace' ) ); ?></p>

		<div class="button-row">
			<a href="<?php echo esc_url( $upgrade_href ); ?>" class="button button-primary button-sm" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Upgrade Now', 'better-search-replace' ); ?></a>
		</div>
	</div>
</div>
