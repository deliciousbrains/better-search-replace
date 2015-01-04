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

			// Don't run if there isn't a search string.
			if ( !isset( $_POST['search_for'] ) || $_POST['search_for'] == '' ) {
				wp_redirect( get_admin_url() . 'tools.php?page=better-search-replace&error=no_search_str' );
				exit();
			}

			// Run the actual search/replace.
			if ( isset( $_POST['select_tables'] ) && is_array( $_POST['select_tables'] ) ) {

				$db = new Better_Search_Replace_DB();

				// Check if we are skipping the 'guid' column.
				if ( isset( $_POST['replace'] ) ) {
					$replace_guids = true;
				} else {
					$replace_guids = false;
				}

				// Check if this is a dry run.
				if ( isset( $_POST['dry_run'] ) ) {
					$dry_run = true;
				} else {
					$dry_run = false;
				}

				$result = $db->run( $_POST['select_tables'], $_POST['search_for'], $_POST['replace_with'], $replace_guids, $dry_run );
				set_transient( 'bsr_results', $result, HOUR_IN_SECONDS );
				wp_redirect( get_admin_url() . 'tools.php?page=better-search-replace&result=true&dry_run=' . $dry_run );
			} else {
				wp_redirect( get_admin_url() . 'tools.php?page=better-search-replace&error=no_tables' );
			}
			exit();
		}
	}

	/**
	 * Renders the result or error onto the better-search-replace admin page.
	 * @access public
	 */
	public static function render_result() {
		if ( isset( $_GET['error'] ) ) {
			echo '<div class="error"><p>';
			switch ( $_GET['error'] ) {
				case 'no_search_str':
					_e( 'No search string was defined, please enter a URL or string to search for.', 'better-search-replace' );
					break;
				case 'no_tables':
					_e( 'Please select the tables that you want to update.', 'better-search-replace' );
					break;
			}
			echo '</p></div>';
		} elseif ( isset( $_GET['result'] ) && get_transient( 'bsr_results' ) ) {
			$result = get_transient( 'bsr_results' );
			echo '<div class="updated">';
			
			if ( isset( $_GET['dry_run'] ) && $_GET['dry_run'] == true ) {
				$msg = sprintf( __( '<p><strong>DRY RUN:</strong> <strong>%d</strong> tables were searched, <strong>%d</strong> cells were found that need to be updated, and <strong>%d</strong> changes were made.</p><p>Click here for more details, or click here to run the search/replace.</p>', 'better-search-replace' ),
					$result['tables'],
					$result['change'],
					$result['updates']
				);
			} else {
				$msg = sprintf( __( '<p>During the search/replace, <strong>%d</strong> tables were searched, with <strong>%d</strong> cells changed in <strong>%d</strong> updates.</p><p>Click here for more details.</p>', 'better-search-replace' ),
					$result['tables'],
					$result['change'],
					$result['updates']
				);
			}

			echo $msg;

			echo '</div>';
			delete_transient( 'bsr_results' );
		} else {
			// Do nothing.
		}
	}

}
