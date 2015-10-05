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

?>

<div class="wrap">

	<h1 id="bsr-title"><?php _e( 'Better Search Replace', 'better-search-replace' ); ?></h1>
	<?php settings_errors(); ?>

	<div id="bsr-error-wrap"></div>

	<?php BSR_Admin::render_result(); ?>

	<div id="poststuff" class="bsr-poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<div id="post-body-content">

				<h2 id="bsr-nav-tab-wrapper" class="nav-tab-wrapper">
				    <a href="?page=better-search-replace&tab=bsr_search_replace" class="nav-tab <?php echo $active_tab == 'bsr_search_replace' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Search/Replace', 'better-search-replace' ); ?></a>
				    <a href="?page=better-search-replace&tab=bsr_settings" class="nav-tab <?php echo $active_tab == 'bsr_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Settings', 'better-search-replace' ); ?></a>
				    <a href="?page=better-search-replace&tab=bsr_help" class="nav-tab <?php echo $active_tab == 'bsr_help' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Help', 'better-search-replace' ); ?></a>
				</h2>

				<form class="bsr-action-form" <?php echo $action; ?> method="POST">

				<?php
					// Include the correct tab template.
					$bsr_template = str_replace( '_', '-', $active_tab ) . '.php';
					if ( file_exists( BSR_PATH . 'templates/' . $bsr_template ) ) {
						include BSR_PATH . 'templates/' . $bsr_template;
					} else {
						include BSR_PATH . 'templates/bsr-search-replace.php';
					}
				?>

				</form>

			</div><!-- /#post-body-content -->

			<div id="postbox-container-1" class="postbox-container">

					<div class="postbox">
						<h3><span><?php _e( 'Like this plugin?', 'better-search-replace' ); ?></span></h3>
						<div class="inside">
							<ul>
								<li><a href="https://wordpress.org/support/view/plugin-reviews/better-search-replace?filter=5" target="_blank"><?php _e( 'Rate it on WordPress.org', 'better-search-replace' ); ?></a></li>
								<li><a href="https://twitter.com/expandedfronts" target="_blank"><?php _e( 'Follow us on Twitter', 'better-search-replace' ); ?></a></li>
								<li><a href="https://expandedfronts.com/products/better-search-replace-pro/" target="_blank"><?php _e( 'Get the pro version', 'better-search-replace' ); ?></a></li>
							</ul>
						</div> <!-- .inside -->
					</div> <!-- .postbox -->

					<div class="postbox">
						<div class="inside">
							<a href="https://expandedfronts.com/products/better-search-replace-pro/"><img src="<?php echo BSR_URL; ?>assets/img/sidebar-upgrade.png" style="width:100%;margin-top:10px;" /></a>
						</div> <!-- .inside -->
					</div> <!-- .postbox -->

			</div>

		</div><!-- /#post-body -->

	</div><!-- /#poststuff -->

</div><!-- /.wrap -->
