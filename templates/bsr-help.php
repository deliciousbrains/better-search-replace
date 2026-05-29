<?php
/**
 * Displays the "System Info" tab.
 *
 * @link       https://bettersearchreplace.com
 * @since      1.1
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$bsr_upgrade_url     = 'https://deliciousbrains.com/better-search-replace/upgrade/?utm_source=insideplugin&utm_medium=web&utm_content=help-tab&utm_campaign=bsr-to-migrate';
$bsr_github_url      = 'https://github.com/deliciousbrains/better-search-replace';
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>

<div class="ui-sidebar-wrapper">

	<div class="inside">

		<div class="panel">

			<div class="panel-header">
				<h3><?php esc_html_e( 'Help & Troubleshooting', 'better-search-replace' ); ?></h3>
			</div>

			<div class="panel-content">

				<div>
					<p>
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: URL to the upgrade page. */
								__( '<a href="%s" style="font-weight:bold;" target="_blank" rel="noopener noreferrer">Upgrade</a> to gain access to premium features and priority email support.', 'better-search-replace' ),
								esc_url( $bsr_upgrade_url )
							)
						);
						?>
					</p>
					<p>
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: URL to the GitHub repository. */
								__( 'Found a bug or have a feature request? Please submit an issue on <a href="%s">GitHub</a>!', 'better-search-replace' ),
								esc_url( $bsr_github_url )
							)
						);
						?>
					</p>
				</div>

				<!--System Info-->
				<div class="row">
					<div class="input-text full-width">
						<label><strong><?php esc_html_e( 'System Info', 'better-search-replace' ); ?></strong></label>
						<textarea readonly="readonly" onclick="this.focus(); this.select()" name="bsr-sysinfo"><?php echo esc_textarea( BSR_Compatibility::get_sysinfo() ); ?></textarea>
					</div>
				</div>

				<div class="row">
					<p class="submit">
						<input type="hidden" name="action" value="bsr_download_sysinfo" />
						<?php wp_nonce_field( 'bsr_download_sysinfo', 'bsr_sysinfo_nonce' ); ?>
						<input type="submit" name="bsr-download-sysinfo" id="bsr-download-sysinfo" class="button button-secondary button-sm" value="<?php echo esc_attr__( 'Download System Info', 'better-search-replace' ); ?>">
					</p>
				</div>

		   </div>
		</div>
	</div>

	<?php
	if ( file_exists( BSR_PATH . 'templates/sidebar.php' ) ) {
		include_once BSR_PATH . 'templates/sidebar.php';
	}
	?>

</div>
