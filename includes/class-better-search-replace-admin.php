<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://expandedfronts.com/better-search-replace
 * @since      1.0.0
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/includes
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/admin
 * @author     Expanded Fronts <support@expandedfronts.com>
 */
class Better_Search_Replace_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $better_search_replace    The ID of this plugin.
	 */
	private $better_search_replace;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $better_search_replace       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $better_search_replace, $version ) {
		$this->better_search_replace = $better_search_replace;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->better_search_replace, plugin_dir_url( __FILE__ ) . 'css/better-search-replace.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->better_search_replace, plugin_dir_url( __FILE__ ) . 'js/better-search-replace.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Register any menu pages used by the plugin.
	 * @access public
	 */
	public function bsr_menu_pages() {
		add_submenu_page( 'tools.php', 'Better Search Replace', 'Better Search Replace', 'manage_options', 'better-search-replace', array( $this, 'bsr_menu_pages_callback' ) );
	}

	/**
	 * The callback for creating a new submenu page under the "Tools" menu.
	 * @access public
	 */
	public function bsr_menu_pages_callback() {
		require_once BSR_PATH . 'templates/bsr-dashboard.php';
	}

	/**
	 * Processes the form submitted by the user.
	 * @access public
	 */
	public function process_search_replace() {
		// Only process form data if properly nonced.
		if ( ! empty( $_POST ) && check_admin_referer( 'process_search_replace', 'bsr_nonce' ) ) {

			// Run the actual search/replace.
			if ( isset( $_POST['select_tables'] ) && is_array( $_POST['select_tables'] ) ) {

				$db = new Better_Search_Replace_DB();

				// Check if we are skipping the 'guid' column.
				if ( isset( $_POST['skip_guids'] ) ) {
					$skip_guids = true;
				} else {
					$skip_guids = false;
				}

				// Check if this is a dry run.
				if ( isset( $_POST['dry_run'] ) ) {
					$dry_run = true;
				} else {
					$dry_run = false;
				}

				$result = $db->run( $_POST['select_tables'], $_POST['search_for'], $_POST['replace_with'], $skip_guids, $dry_run );
				var_dump( $result['table_reports']['wp_posts'] );
			} else {
				// Do something here.
			}
		}
	}

}
