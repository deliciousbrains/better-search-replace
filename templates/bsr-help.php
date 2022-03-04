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

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

$bsr_docs_url    = 'https://bettersearchreplace.com/docs/';
$bsr_support_url = 'https://wordpress.org/support/plugin/better-search-replace';
$bsr_license_key = get_option( 'bsr_license_key' );

if ( false !== $bsr_license_key ) {
	$bsr_support_url .= '?key=' . esc_attr( $bsr_license_key );
}

?>

<div class="ui-sidebar-wrapper">

	<div class="inside">

		<div class="panel">

			<div class="panel-header">
				<h3><?php _e( 'Help & Troubleshooting', 'better-search-replace' ); ?></h3>
			</div>

			<div class="panel-content">

				<div>
					<p>
	                    <?php
	                    printf(
	                            __( 'Free support is available on the <a href="%s">plugin support forums</a>.', 'better-search-replace' ),
	                        $bsr_support_url
	                    )
	                    ?>
	                </p>
	                <p>
	                    <?php
	                    printf(
	                            __( '<a href="%s" style="font-weight:bold;">Upgrade</a> to gain access to premium features and priority email support.', 'better-search-replace' ),
	                            'https://bettersearchreplace.com/?utm_source=insideplugin&utm_medium=web&utm_content=help-tab&utm_campaign=pro-upsell'
	                    );
	                    ?>
	                </p>
	                <p>
	                    <?php
	                    printf(
	                            __( 'Found a bug or have a feature request? Please submit an issue on <a href="%s">GitHub</a>!', 'better-search-replace' ),
	                            'https://github.com/deliciousbrains/better-search-replace'
	                    );
	                    ?>
	                </p>
				</div>

				<!--System Info-->
				<div class="row">
					<div class="input-text full-width">
						<label><strong><?php _e( 'System Info', 'better-search-replace' ); ?></strong></label>
						<textarea readonly="readonly" onclick="this.focus(); this.select()" name='bsr-sysinfo'><?php echo BSR_Compatibility::get_sysinfo(); ?></textarea>
					</div>
				</div>

				<!--Submit Button-->
				<div class="row">
					<p class="submit">
						<input type="hidden" name="action" value="bsr_download_sysinfo" />
						<?php wp_nonce_field( 'bsr_download_sysinfo', 'bsr_sysinfo_nonce' ); ?>
						<input type="submit" name="bsr-download-sysinfo" id="bsr-download-sysinfo" class="button button-secondary button-sm" value="Download System Info">
					</p>
				</div>

		   </div>
		</div>
	</div>

	<div class="upgrade-sidebar">
		<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/mdb-birds.svg'; ?>">
		<div class="content">
			<h3>Upgrade</h3>
			<p>Gain access to more database and migration features</p>

			<ul>
				<li>
					<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/icon-checkmark.svg'; ?>">
					<p>Preview database changes before they are saved</p>
				</li>
				<li>
					<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/icon-checkmark.svg'; ?>">
					<p>Use regular expressions for complex string replacements</p>
				</li>
				<li>
					<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/icon-checkmark.svg'; ?>">
					<p>Migrate full sites including themes, plugins, media, and database.</p>
				</li>
				<li>
					<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/icon-checkmark.svg'; ?>">
					<p>Export and import WordPress databases</p>
				</li>
				<li>
					<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/icon-checkmark.svg'; ?>">
					<p>Email support</p>
				</li>
			</ul>

			<p class="upgrade-offer-text">Get up to <span>50% off</span> your first year!</p>

			<div class="button-row">
				<button href="#" class="button button-primary button-sm">Upgrade Now</button>
			</div>
		</div>
	</div>

</div>
