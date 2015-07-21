<?php

/**
 * Displays the main Better Search Replace page under Tools -> Better Search Replace.
 *
 * @link       http://expandedfronts.com/better-search-replace/
 * @since      1.0.0
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/templates
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

// Determines which tab to display.
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'bsr_search_replace';

if ( 'bsr_settings' === $active_tab ) {
	$action = get_admin_url() . 'options.php';
} else {
	$action = get_admin_url() . 'admin-post.php';
}

?>

<div class="wrap">

	<h2><?php _e( 'Better Search Replace', 'better-search-replace' ); ?></h2>
	<?php Better_Search_Replace_Admin::render_result(); ?>

	<h2 class="nav-tab-wrapper">
	    <a href="?page=better-search-replace&tab=bsr_search_replace" class="nav-tab <?php echo $active_tab == 'bsr_search_replace' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Search/Replace', 'better-search-replace' ); ?></a>
	    <a href="?page=better-search-replace&tab=bsr_settings" class="nav-tab <?php echo $active_tab == 'bsr_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Settings', 'better-search-replace' ); ?></a>
	</h2>

	<form action="<?php echo $action; ?>" method="POST">

	<?php
		// Include the correct tab template.
		$bsr_template = str_replace( '_', '-', $active_tab ) . '.php';
		if ( file_exists( BSR_PATH . 'templates/' . $bsr_template ) ) {
			include BSR_PATH . 'templates/' . $bsr_template;
		} else {
			include BSR_PATH . 'templates/' . 'bsr-search-replace.php';
		}
	?>

	</form>

</div><!-- /.wrap -->
