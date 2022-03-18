<?php
/**
 * Displays the main "Search/Replace" tab.
 *
 * @link       https://bettersearchreplace.com
 * @since      1.1
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/templates
 */

// Prevent direct/unauthorized access.
if ( ! defined( 'BSR_PATH' ) ) exit;

?>

<div id="bsr-search-replace-wrap" class="postbox">

	<div class="ui-sidebar-wrapper">

		<div class="inside">

			<div id="bsr-search-replace-form" class="form-table">

			<!--Hidden and to trigger the dry run notice placement-->
			<h2 class="hidden">Dry Run Notice</h2>

			<!--Search/Replace Panel-->
			<div class="panel">

				<div class="panel-header">
					<h3><?php _e( 'Search/Replace', 'better-search-replace' ); ?></h3>
					<a href="#" class="tooltip">
						<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/icon-help.svg'; ?>">
					</a>
	                <span class="helper-message left">
	                    <?php _e( 'Search and replace text in the database, including serialized arrays and objects. Be sure to back up your database before running this process.', 'better-search-replace' ); ?>
	                </span>
				</div>

				<div class="panel-content">

					<!--Search/Replace Fields-->
					<div class="row search-replace">
						<div class="input-text full-width">
							<label for="search_for"><strong><?php _e( 'Search for', 'better-search-replace' ); ?></strong></label>
							<input id="search_for" class="regular-text" type="text" name="search_for" value="<?php BSR_Admin::prefill_value( 'search_for' ); ?>" />
						</div>

						<div class="input-text full-width">
							<label for="replace_with"><strong><?php _e( 'Replace with', 'better-search-replace' ); ?></strong></label>
							<input id="replace_with" class="regular-text" type="text" name="replace_with" value="<?php BSR_Admin::prefill_value( 'replace_with' ); ?>" />
						</div>
					</div>

					<!--Tables-->
					<div class="row">
						<div class="col full-width tables">
							<label for="select_tables"><strong><?php _e( 'Select tables', 'better-search-replace' ); ?></strong></label>
								<?php BSR_Admin::load_tables(); ?>
								<p class="description"><?php _e( 'Select multiple tables with Ctrl-Click for Windows or Cmd-Click for Mac.', 'better-search-replace' ); ?></p>
							</div>
					</div>

				</div>
			</div>


			<!--Additional Settings Panel-->
			<div class="panel">

				<div class="panel-header">
					<h3><?php _e( 'Additional Settings', 'better-search-replace' ); ?></h3>
				</div>

				<div class="panel-content settings additional-settings">

				<!--Case Sensitive-->
				<label for="case_insensitive" class="row">
					<div class="col">
						<input id="case_insensitive" type="checkbox" name="case_insensitive" <?php BSR_Admin::prefill_value( 'case_insensitive', 'checkbox' ); ?> />
					</div>
					<div class="col">
						<label for="case_insensitive"><strong><?php _e( 'Case-Insensitive', 'better-search-replace' ); ?></strong></label>
						<label for="case_insensitive"><span class="description"><?php _e( 'Searches are case-sensitive by default.', 'better-search-replace' ); ?></span></label>
					</div>
				</label>

	            <!--Replace GUIDs-->
				 <label for="replace_guids" class="row">
					<div class="col">
						<input id="replace_guids" type="checkbox" name="replace_guids" <?php BSR_Admin::prefill_value( 'replace_guids', 'checkbox' ); ?> />
					</div>
					<div class="col">
					  <label for="replace_guids" class="replace_guids"><strong><?php _e( 'Replace GUIDs', 'better-search-replace' ); ?></strong><a href="https://wordpress.org/support/article/changing-the-site-url/#important-guid-note" target="_blank"><img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/icon-help.svg'; ?>"></a></label>
						<label for="replace_guids"><span class="description"><?php _e( 'If left unchecked, all database columns titled \'guid\' will be skipped.', 'better-search-replace' ); ?></span></label>
					</div>
				</label>

				<!--Dry Run-->
				<label for="dry_run" class="row">
					<div class="col">
						<input id="dry_run" type="checkbox" name="dry_run" checked />
					</div>
					<div class="col">
						<label for="dry_run"><strong><?php _e( 'Run as dry run', 'better-search-replace' ); ?></strong></label></td>
						<label for="dry_run"><span class="description"><?php _e( 'If checked, no changes will be made to the database, allowing you to check the results beforehand.', 'better-search-replace' ); ?></span></label>
					</div>
				</label>

			</div>
		</div>
	        <div id="bsr-error-wrap"></div>
			<!--Submit Button-->
			<div id="bsr-submit-wrap">
				<?php wp_nonce_field( 'process_search_replace', 'bsr_nonce' ); ?>
				<input type="hidden" name="action" value="bsr_process_search_replace" />
				<button id="bsr-submit" type="submit" class="button button-primary button-lg"><?php _e( 'Run Search/Replace', 'better-search-replace' ); ?>
					<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/icon-arrow.svg'; ?>">
				</button>
			</div>
		</div>

	</div><!-- /.inside -->

	<?php
	if ( file_exists( BSR_PATH . 'templates/sidebar.php' ) ) {
		include_once BSR_PATH . 'templates/sidebar.php';
	}
	?>

	<!-- /.ui-sidebar-wrapper -->
	</div>

</div><!-- /#bsr-search-replace-wrap -->
