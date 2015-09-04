<?php
/**
 * Displays the "System Info" tab.
 *
 * @link       http://expandedfronts.com/better-search-replace/
 * @since      1.1
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/templates
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

?>

<textarea readonly="readonly" onclick="this.focus(); this.select()" style="width:750px;height:500px;font-family:Menlo,Monaco,monospace; margin-top: 15px;" name='bsr-sysinfo'><?php echo BSR_Compatibility::get_sysinfo(); ?></textarea>
<p class="submit">
	<input type="hidden" name="action" value="bsr_download_sysinfo" />
	<?php submit_button( 'Download System Info', 'primary', 'bsr-download-sysinfo', false ); ?>
</p>
