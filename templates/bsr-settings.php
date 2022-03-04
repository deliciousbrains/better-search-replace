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
