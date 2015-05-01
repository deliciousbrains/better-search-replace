<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Registers styles and scripts, adds the custom administration page,
 * and processes user input on the "search/replace" form.
 *
 * @link       http://expandedfronts.com/better-search-replace
 * @since      1.0.0
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/includes
 * @author     Expanded Fronts, LLC
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

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
	 * Register any CSS and JS used by the plugin.
	 * @since    1.0.0
	 * @access 	 public
	 * @param    string $hook Used for determining which page(s) to load our scripts.
	 */
	public function enqueue_scripts( $hook ) {
		if ( $hook === 'tools_page_better-search-replace' ) {
			wp_enqueue_style( 'better-search-replace', BSR_URL . 'assets/css/better-search-replace.css', array(), '2015', 'all' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'thickbox' );
		}
	}

	/**
	 * Register any menu pages used by the plugin.
	 * @since  1.0.0
	 * @access public
	 */
	public function bsr_menu_pages() {
		add_submenu_page( 'tools.php', __( 'Better Search Replace', 'better-search-replace' ), __( 'Better Search Replace', 'better-search-replace' ), 'manage_options', 'better-search-replace', array( $this, 'bsr_menu_pages_callback' ) );
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

			// Clear the results of the last run.
			delete_transient( 'bsr_results' );

			// Run the actual search/replace.
			if ( isset( $_POST['select_tables'] ) && is_array( $_POST['select_tables'] ) ) {

				// Initialize the settings for this run.
				$db 				= new Better_Search_Replace_DB();
				$case_insensitive 	= isset( $_POST['case_insensitive'] ) ? true : false;
				$replace_guids 		= isset( $_POST['replace_guids'] ) ? true : false;
				$dry_run 			= isset( $_POST['dry_run'] ) ? true : false;

				// Remove slashes from search and replace strings.
				$search_for 	= stripslashes( $_POST['search_for'] );
				$replace_with 	= stripslashes( $_POST['replace_with'] );

				$result = $db->run( $_POST['select_tables'], $search_for, $replace_with, $replace_guids, $dry_run, $case_insensitive );
				set_transient( 'bsr_results', $result, HOUR_IN_SECONDS );
				wp_redirect( get_admin_url() . 'tools.php?page=better-search-replace&result=true&dry_run=' . $dry_run );
				exit();
			} else {
				wp_redirect( get_admin_url() . 'tools.php?page=better-search-replace&error=no_tables' );
				exit();
			}
		} else {
			wp_die( 'Cheatin&#8217; uh?', 'better-search-replace' );
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

			if ( isset( $result['dry_run'] ) && $result['dry_run'] === true ) {
				$msg = sprintf( __( '<p><strong>DRY RUN:</strong> <strong>%d</strong> tables were searched, <strong>%d</strong> cells were found that need to be updated, and <strong>%d</strong> changes were made.</p><p><a href="%s" class="thickbox" title="Dry Run Details">Click here</a> for more details, or use the form below to run the search/replace.</p>', 'better-search-replace' ),
					$result['tables'],
					$result['change'],
					$result['updates'],
					get_admin_url() . 'admin-post.php?action=bsr_view_details&TB_iframe=true&width=600&height=500'
				);
			} else {
				$msg = sprintf( __( '<p>During the search/replace, <strong>%d</strong> tables were searched, with <strong>%d</strong> cells changed in <strong>%d</strong> updates.</p><p><a href="%s" class="thickbox" title="Search/Replace Details">Click here</a> for more details.</p>', 'better-search-replace' ),
					$result['tables'],
					$result['change'],
					$result['updates'],
					get_admin_url() . 'admin-post.php?action=bsr_view_details&TB_iframe=true&width=600&height=500'
				);
			}

			echo $msg . '</div>';
		} else {
			// There is nothing to do here.
		}
	}

	/**
	 * Prefills the given value if on a results page (dry run or live run).
	 * @access public
	 * @param  string $value The value to check for.
	 * @param  string $type  The type of value we're filling.
	 */
	public static function prefill_value( $value, $type = 'text' ) {
		if ( isset( $_GET['result'] ) && get_transient( 'bsr_results' ) ) {
			$report = get_transient( 'bsr_results' );
			if ( isset( $report[$value] ) ) {
				if ( $type === 'checkbox' && $report[$value] !== false ) {
					echo 'checked';
				} else {
					echo esc_attr( $report[$value] );
				}
			}
		}
	}

	/**
	 * Loads the tables available to run a search replace, prefilling if already
	 * selected the tables.
	 * @access public
	 */
	public static function load_tables() {
		$tables = Better_Search_Replace_DB::get_tables();

		echo '<select id="select_tables" name="select_tables[]" multiple="multiple" style="width:25em;">';
		foreach ( $tables as $table ) {
			if ( isset( $_GET['result'] ) && get_transient( 'bsr_results' ) ) {
				$result = get_transient( 'bsr_results' );
				if ( isset( $result['table_reports'][$table] ) ) {
					echo "<option value='$table' selected>$table</option>";
				} else {
					echo "<option value='$table'>$table</option>";
				}
			} else {
				echo "<option value='$table'>$table</option>";
			}
		}
		echo '</select>';
	}

	/**
	 * Loads the result details (via Thickbox).
	 * @access public
	 */
	public function load_details() {
		if ( get_transient( 'bsr_results' ) ) {
			$results 		= get_transient( 'bsr_results' );
			$styles_url 	= get_admin_url() . "load-styles.php?c=0&dir=ltr&load=dashicons,admin-bar,wp-admin,buttons,wp-auth-check";
			$bsr_styles 	= BSR_URL . 'assets/css/better-search-replace.css';
			?>
			<link href="<?php echo $styles_url; ?>" rel="stylesheet" type="text/css">
			<link href="<?php echo $bsr_styles; ?>" rel="stylesheet" type="text/css">

			<div class="container" style="padding:10px;">
			<table id="bsr-results-table" class="widefat">
				<thead>
					<tr><th class="bsr-first">Table</th><th class="bsr-second">Changes Found</th><th class="bsr-third">Rows Updated</th><th class="bsr-fourth">Time</th></tr>
				</thead>
				<tbody>
				<?php
					foreach ( $results['table_reports'] as $table_name => $report ) {
						$time = $report['end'] - $report['start'];

						if ( $report['change'] != 0 ) {
							$report['change'] = '<strong>' . $report['change'] . '</strong>';
						}

						if ( $report['updates'] != 0 ) {
							$report['updates'] = '<strong>' . $report['updates'] . '</strong>';
						}

						echo '<tr><td class="bsr-first">' . $table_name . '</td><td class="bsr-second">' . $report['change'] . '</td><td class="bsr-third">' . $report['updates'] . '</td><td class="bsr-fourth">' . round( $time, 3 ) . ' seconds</td></tr>';
					}
				?>
				</tbody>
			</table>
			</div>

			<?php
		}
	}

}
