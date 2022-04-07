<?php
/**
 * Displays the main "Settings" tab.
 *
 * @link       https://bettersearchreplace.com
 * @since      1.1
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/templates
 */

// Prevent direct/unauthorized access.
if ( ! defined( 'BSR_PATH' ) ) exit;

// Other settings.
$page_size 	= get_option( 'bsr_page_size' ) ? absint( get_option( 'bsr_page_size' ) ) : 20000;

 ?>

<?php settings_fields( 'bsr_settings_fields' ); ?>

<div class="ui-sidebar-wrapper">

  <div class="inside">

	<!--Settings Panel-->
	<div class="panel">

		<div class="panel-header">
			 <h3><?php _e( 'Settings', 'better-search-replace' ); ?></h3>
		</div>

		<div class="panel-content settings">

			<!--Max Page Size-->
			<div class="row last-row">
				<div class="input-text">
					<div class="settings-header">
						<label><strong><?php _e( 'Max Page Size', 'better-search-replace' ); ?></strong></label>
						<span id="bsr-page-size-value"><?php echo absint( $page_size ); ?></span>
					</div>
					<input id="bsr_page_size" type="hidden" name="bsr_page_size" value="<?php echo $page_size; ?>" />
					<p class="description"><?php _e( 'If you notice timeouts or are unable to backup/import the database, try decreasing this value.', 'better-search-replace' ); ?></p>
					<div class="slider-wrapper">
						<div id="bsr-page-size-slider" class="bsr-slider"></div>
					</div>
				</div>
			</div>

			<!--Submit Button-->
			<div class="row panel-footer">
				<?php submit_button(); ?>
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
