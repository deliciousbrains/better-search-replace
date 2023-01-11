<?php

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

// Determines which tab to display.
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'bsr_search_replace';

switch( $active_tab ) {
	case 'bsr_settings':
		$action = 'action="' . get_admin_url() . 'options.php' . '"';
		break;
	case 'bsr_help':
		$action = 'action="' . get_admin_url() . 'admin-post.php' . '"';
		break;
	default:
		$action = '';
}

if ( 'bsr_settings' === $active_tab ) {
	$action = get_admin_url() . 'options.php';
} else {
	$action = get_admin_url() . 'admin-post.php';
}

?>

<div class="wrap" style="display: grid;">

    <div class="bsr-notice-container">
        <h2 class="hidden"></h2>
    </div>

	<div class="header">

		<div class="content">
			<a href="?page=better-search-replace&tab=bsr_search_replace">
				<img href="?page=better-search-replace&tab=bsr_search_replace" src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/logo-bsr.svg'; ?>" class="logo">
			</a>
			<a href="https://deliciousbrains.com/better-search-replace/upgrade/?utm_source=insideplugin&utm_medium=web&utm_content=header&utm_campaign=bsr-to-migrate
" target="_blank" class="upgrade-notice">
				<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/svg/icon-upgrade.svg'; ?>">
				<?php _e( 'Upgrade now and get 50% off', 'better-search-replace' ); ?>
			</a>
		</div>

	<?php settings_errors(); ?>

	<?php BSR_Admin::render_result(); ?>

	</div>

	<div class="nav-tab-wrapper">
		<ul>
			<li><a href="?page=better-search-replace&tab=bsr_search_replace" class="nav-tab <?php echo $active_tab == 'bsr_search_replace' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Search/Replace', 'better-search-replace' ); ?></a></li>
			<li><a href="?page=better-search-replace&tab=bsr_settings" class="nav-tab <?php echo $active_tab == 'bsr_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Settings', 'better-search-replace' ); ?></a></li>
			<li><a href="?page=better-search-replace&tab=bsr_help" class="nav-tab <?php echo $active_tab == 'bsr_help' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Help', 'better-search-replace' ); ?></a></li>
		</ul>
	</div>

	<form class="bsr-action-form" action="<?php echo $action; ?>" method="POST">

		<?php
		// Include the correct tab template.
		$bsr_template = BSR_Templates_Helper::get_tab_template($active_tab);
		include $bsr_template;
		?>

	</form>

</div><!-- /.wrap -->
