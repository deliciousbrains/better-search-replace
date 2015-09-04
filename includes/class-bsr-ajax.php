<?php

/**
 * AJAX-specific functionality for the plugin.
 *
 * @link       http://expandedfronts.com/better-search-replace
 * @since      1.2
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/includes
 * @author     Expanded Fronts, LLC
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

class BSR_AJAX {

	/**
	 * Initiate our custom ajax handlers.
	 * @access public
	 */
	public function init() {
		add_action( 'init', array( $this, 'define_ajax' ), 0 );
		add_action( 'init', array( $this, 'do_bsr_ajax' ), 1 );
		$this->add_ajax_actions();
	}

	/**
	 * Gets our custom endpoint.
	 * @access public
	 * @return string
	 */
	public static function get_endpoint() {
		return esc_url_raw( get_admin_url() . 'tools.php?page=better-search-replace&bsr-ajax=' );
	}

	/**
	 * Set BSR AJAX constant and headers.
	 * @access public
	 */
	public function define_ajax() {

		if ( isset( $_GET['bsr-ajax'] ) && ! empty( $_GET['bsr-ajax'] ) ) {

			// Define the WordPress "DOING_AJAX" constant.
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}

			// Define a custom "BSR_DOING_AJAX" constant.
			if ( ! defined( 'BSR_DOING_AJAX' ) ) {
				define( 'BSR_DOING_AJAX', true );
			}

			// Prevent notices from breaking AJAX functionality.
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 );
			}

			// Send the headers.
			send_origin_headers();
			@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
			@header( 'X-Robots-Tag: noindex' );
			send_nosniff_header();
			nocache_headers();

		}

	}

	/**
	 * Check if we're doing AJAX and fire the related action.
	 * @access public
	 */
	public function do_bsr_ajax() {
		global $wp_query;

		if ( isset( $_GET['bsr-ajax'] ) && ! empty( $_GET['bsr-ajax'] ) ) {
			$wp_query->set( 'bsr-ajax', sanitize_text_field( $_GET['bsr-ajax'] ) );
		}

		if ( $action = $wp_query->get( 'bsr-ajax' ) ) {
			do_action( 'bsr_ajax_' . sanitize_text_field( $action ) );
			die();
		}
	}

	/**
	 * Adds any AJAX-related actions.
	 * @access public
	 */
	public function add_ajax_actions() {
		$actions = array(
			'process_search_replace',
		);

		foreach ( $actions as $action ) {
			add_action( 'bsr_ajax_' . $action, array( $this, $action ) );
		}
	}

	/**
	 * Processes the search/replace form submitted by the user.
	 * @access public
	 */
	public function process_search_replace() {

		// Bail if not authorized.
		if ( ! check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce' ) ) {
			return;
		}

		$args = array();
		parse_str( $_POST['bsr_data'], $args );

		// Initialize the DB class.
		$db 				= new BSR_DB();
		$tables 			= $db->get_tables();
		$step 				= absint( $_REQUEST['bsr_step'] );
		$page 				= isset( $_REQUEST['bsr_page'] ) ? absint( $_REQUEST['bsr_page'] ) : 0;

		// Build the arguements for this run.
		$tables 			= isset( $args['select_tables'] ) ? $args['select_tables'] : array();
		$case_insensitive 	= isset( $args['case_insensitive'] ) ? $args['case_insensitive'] : 'off';
		$replace_guids 		= isset( $args['replace_guids'] ) ? $args['replace_guids'] : 'off';
		$dry_run 			= isset( $args['dry_run'] ) ? $args['dry_run'] : 'off';
		$save_profile		= isset( $args['profile_name'] ) ? $args['profile_name'] : '';
		$completed_pages 	= isset( $args['completed_pages'] ) ? absint( $args['completed_pages'] ) : 0;
		$table_reports 		= isset( $args['table_reports'] ) ? $args['table_reports'] : array();
		$total_pages 		= isset( $args['total_pages'] ) ? absint( $args['total_pages'] ) : $db->get_total_pages( $tables );

		// Remove slashes from search and replace strings.
		$search_for 		= stripslashes( $args['search_for'] );
		$replace_with 		= stripslashes( $args['replace_with'] );

		$args = array(
			'select_tables' 	=> $tables,
			'case_insensitive' 	=> $case_insensitive,
			'replace_guids' 	=> $replace_guids,
			'dry_run' 			=> $dry_run,
			'search_for' 		=> $search_for,
			'replace_with' 		=> $replace_with,
			'total_pages' 		=> $total_pages,
			'completed_pages' 	=> $completed_pages,
		);

		// Any operations that should only be performed at the beginning.
		if ( $step === 0 && $page === 0 ) {
			// Clear the results of the last run.
			delete_transient( 'bsr_results' );
		}

		// Start processing data.
		if ( isset( $tables[$step] ) ) {

			$result = $db->srdb( $tables[$step], $page, $args );
			$this->append_report( $tables[$step], $result['table_report'], $args );

			if ( false == $result['table_complete'] ) {
				$page++;
			} else {
				$step++;
				$page = 0;
			}

			$args['completed_pages']++;
			$percentage = $completed_pages / $total_pages * 100 . '%';

		} else {
			$db->maybe_update_site_url();
			$step 		= 'done';
			$percentage = '100%';
		}

		// Store results in an array.
		$result = array(
			'step' 				=> $step,
			'page' 				=> $page,
			'completed_pages' 	=> $completed_pages,
			'percentage'		=> $percentage,
			'url' 				=> get_admin_url() . 'tools.php?page=better-search-replace&tab=bsr_search_replace&result=true',
			'bsr_data' 			=> http_build_query( $args )
		);

		// Send output as JSON for processing via AJAX.
		echo json_encode( $result );
		exit;

	}

	/**
	 * Helper function for assembling the BSR Results.
	 * @access public
	 * @param  string 	$table 	The name of the table to append to.
	 * @param  array  	$report The report for that table.
	 * @param  array 	$args 	An array of arguements used for this run.
	 * @return boolean
	 */
	public function append_report( $table, $report, $args ) {

		// Bail if not authorized.
		if ( ! check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce' ) ) {
			return;
		}

		// Retrieve the existing transient.
		$results = get_transient( 'bsr_results' ) ? get_transient( 'bsr_results') : array();

		// Grab any values from the run args.
		$results['search_for'] 			= isset( $args['search_for'] ) ? $args['search_for'] : '';
		$results['replace_with'] 		= isset( $args['replace_with'] ) ? $args['replace_with'] : '';
		$results['dry_run'] 			= isset( $args['dry_run'] ) ? $args['dry_run'] : 'off';
		$results['case_insensitive'] 	= isset( $args['case_insensitive'] ) ? $args['case_insensitive'] : 'off';
		$results['replace_guids'] 		= isset( $args['replace_guids'] ) ? $args['replace_guids'] : 'off';

		// Sum the values of the new and existing reports.
		$results['change'] 	= isset( $results['change'] ) ? $results['change'] + $report['change'] : $report['change'];
		$results['errors'] 	= isset( $results['errors'] ) ? $results['errors'] + $report['errors'] : $report['errors'];
		$results['updates'] = isset( $results['updates'] ) ? $results['updates'] + $report['updates'] : $report['updates'];

		// Append the table report, or create a new one if necessary.
		if ( isset( $results['table_reports'] ) && isset( $results['table_reports'][$table] ) ) {

			$results['table_reports'][$table]['change'] 	= $results[$table]['change'] + $report['change'];
			$results['table_reports'][$table]['updates'] 	= $results[$table]['updates'] + $report['updates'];
			$results['table_reports'][$table]['end'] 		= $report['end'];

			if ( count( $results['table_reports'][$table]['changes'] ) < 20 ) {
				$results['table_reports'][$table]['changes'] = array_merge( $results[$table]['changes'], $report['changes'] );
			}

		} else {
			$results['table_reports'][$table] = $report;
		}

		// Count the number of tables.
		$results['tables'] = count( $results['table_reports'] );

		// Update the transient.
		if ( ! set_transient( 'bsr_results', $results, DAY_IN_SECONDS ) ) {
			return false;
		}

		return true;

	}

}

$bsr_ajax = new BSR_AJAX;
$bsr_ajax->init();
