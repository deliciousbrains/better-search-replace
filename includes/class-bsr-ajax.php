<?php

/**
 * AJAX-specific functionality for the plugin.
 *
 * @link       https://bettersearchreplace.com
 * @since      1.2
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/includes
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

class BSR_AJAX {

	/**
	 * Initiate our custom ajax handlers.
	 * @access public
	 */
	public function init() {
		add_action( 'init', array( $this, 'define_ajax' ), 1 );
		add_action( 'init', array( $this, 'do_bsr_ajax' ), 2 );
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
			'upload_csv',
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
		if ( ! BSR_Utils::check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce' ) ) {
			return;
		}

		// Initialize the DB class.
		$db   = new BSR_DB();
		$step = isset( $_REQUEST['bsr_step' ] ) ? absint( $_REQUEST['bsr_step'] ) : 0;
		$page = isset( $_REQUEST['bsr_page'] ) ? absint( $_REQUEST['bsr_page'] ) : 0;

		// Any operations that should only be performed at the beginning.
		if ( $step === 0 && $page === 0 ) {
			$args = array();
			parse_str( $_POST['bsr_data'], $args );

			// Build the arguments for this run.
			if ( ! isset( $args['select_tables'] ) || ! is_array( $args['select_tables'] ) ) {
				$args['select_tables'] = array();
			}

			// Check if we're processing CSV data
			$csv_key = isset( $args['bsr_csv_key'] ) ? sanitize_text_field( $args['bsr_csv_key'] ) : '';
			$csv_pairs = array();
			$csv_pair_index = 0;

			if ( ! empty( $csv_key ) ) {
				$csv_pairs = BSR_CSV::get_pairs( $csv_key );
				if ( ! empty( $csv_pairs ) ) {
					// Use the first pair for initial run
					$csv_pair_index = 0;
					$args['search_for'] = stripslashes( $csv_pairs[0]['search_for'] );
					$args['replace_with'] = stripslashes( $csv_pairs[0]['replace_with'] );
				}
			}

			$args = array(
				'select_tables'    => array_map( 'trim', $args['select_tables'] ),
				'case_insensitive' => isset( $args['case_insensitive'] ) ? $args['case_insensitive'] : 'off',
				'replace_guids'    => isset( $args['replace_guids'] ) ? $args['replace_guids'] : 'off',
				'dry_run'          => isset( $args['dry_run'] ) ? $args['dry_run'] : 'off',
				'search_for'       => isset( $args['search_for'] ) ? stripslashes( $args['search_for'] ) : '',
				'replace_with'     => isset( $args['replace_with'] ) ? stripslashes( $args['replace_with'] ) : '',
				'completed_pages'  => isset( $args['completed_pages'] ) ? absint( $args['completed_pages'] ) : 0,
				'bsr_csv_key'      => $csv_key,
				'csv_pair_index'   => $csv_pair_index,
				'csv_total_pairs'  => ! empty( $csv_pairs ) ? count( $csv_pairs ) : 0,
			);

			$args['total_pages'] = isset( $args['total_pages'] ) ? absint( $args['total_pages'] ) : $db->get_total_pages( $args['select_tables'] );

			// Clear the results of the last run.
			delete_transient( 'bsr_results' );
			delete_option( 'bsr_data' );
		} else {
			$args = get_option( 'bsr_data' );
		}

		// Start processing data.
		if ( isset( $args['select_tables'][$step] ) ) {

			$result = $db->srdb( $args['select_tables'][$step], $page, $args );
			$this->append_report( $args['select_tables'][$step], $result['table_report'], $args );

			if ( false === $result['table_complete'] ) {
				$page++;
			} else {
				$step++;
				$page = 0;
			}

			// Check if isset() again as the step may have changed since last check.
			if ( isset( $args['select_tables'][$step] ) ) {
				$msg_tbl = esc_html( $args['select_tables'][$step] );

				$message = sprintf(
					__( 'Processing table %d of %d: %s', 'better-search-replace' ),
					$step + 1,
					count( $args['select_tables'] ),
					$msg_tbl
				);
			}

			$args['completed_pages']++;
			$percentage = $args['completed_pages'] / $args['total_pages'] * 100 . '%';

		} else {
			// All tables for current search/replace pair are done
			$csv_key = isset( $args['bsr_csv_key'] ) ? $args['bsr_csv_key'] : '';
			$csv_pair_index = isset( $args['csv_pair_index'] ) ? absint( $args['csv_pair_index'] ) : 0;
			$csv_total_pairs = isset( $args['csv_total_pairs'] ) ? absint( $args['csv_total_pairs'] ) : 0;

			// Check if we need to process another CSV pair
			if ( ! empty( $csv_key ) && $csv_total_pairs > 0 && ( $csv_pair_index + 1 ) < $csv_total_pairs ) {
				// Move to next CSV pair
				$csv_pairs = BSR_CSV::get_pairs( $csv_key );
				$csv_pair_index++;

				if ( ! empty( $csv_pairs ) && isset( $csv_pairs[$csv_pair_index] ) ) {
					// Update search/replace values for next pair
					$args['search_for'] = stripslashes( $csv_pairs[$csv_pair_index]['search_for'] );
					$args['replace_with'] = stripslashes( $csv_pairs[$csv_pair_index]['replace_with'] );
					$args['csv_pair_index'] = $csv_pair_index;

					// Reset for next pair
					$step = 0;
					$page = 0;
					$args['completed_pages'] = 0;

					$message = sprintf(
						__( 'Processing CSV pair %d of %d: "%s" → "%s"', 'better-search-replace' ),
						$csv_pair_index + 1,
						$csv_total_pairs,
						esc_html( substr( $args['search_for'], 0, 50 ) ),
						esc_html( substr( $args['replace_with'], 0, 50 ) )
					);

					// Calculate overall percentage across all CSV pairs
					$overall_progress = ( $csv_pair_index / $csv_total_pairs ) * 100;
					$percentage = $overall_progress . '%';
				}
			} else {
				// All done
				$db->maybe_update_site_url();
				$step = 'done';
				$percentage = '100%';

				// Clean up CSV data if exists
				if ( ! empty( $csv_key ) ) {
					BSR_CSV::delete_pairs( $csv_key );
				}
			}
		}

		update_option( 'bsr_data', $args );

		// Store results in an array.
		$result = array(
			'step' 				=> $step,
			'page' 				=> $page,
			'percentage'		=> $percentage,
			'url' 				=> get_admin_url() . 'tools.php?page=better-search-replace&tab=bsr_search_replace&result=true',
			'bsr_data' 			=> build_query( $args )
		);

		if ( isset( $message ) ) {
			$result['message'] = $message;
		}

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
		if ( ! BSR_Utils::check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce' ) ) {
			return false;
		}

		// Retrieve the existing transient.
		$results = get_transient( 'bsr_results' ) ? get_transient( 'bsr_results') : array();

		// Check if we're processing CSV
		$csv_key = isset( $args['bsr_csv_key'] ) ? $args['bsr_csv_key'] : '';
		$csv_pair_index = isset( $args['csv_pair_index'] ) ? absint( $args['csv_pair_index'] ) : 0;
		$is_csv = ! empty( $csv_key );

		// Grab any values from the run args.
		$results['search_for'] 			= isset( $args['search_for'] ) ? $args['search_for'] : '';
		$results['replace_with'] 		= isset( $args['replace_with'] ) ? $args['replace_with'] : '';
		$results['dry_run'] 			= isset( $args['dry_run'] ) ? $args['dry_run'] : 'off';
		$results['case_insensitive'] 	= isset( $args['case_insensitive'] ) ? $args['case_insensitive'] : 'off';
		$results['replace_guids'] 		= isset( $args['replace_guids'] ) ? $args['replace_guids'] : 'off';
		$results['is_csv']              = $is_csv;

		if ( $is_csv ) {
			// Initialize CSV pair reports if not exists
			if ( ! isset( $results['csv_pair_reports'] ) ) {
				$results['csv_pair_reports'] = array();
			}

			// Track report for this specific CSV pair
			if ( ! isset( $results['csv_pair_reports'][$csv_pair_index] ) ) {
				$results['csv_pair_reports'][$csv_pair_index] = array(
					'search_for'    => $args['search_for'],
					'replace_with'  => $args['replace_with'],
					'change'        => 0,
					'updates'       => 0,
					'table_reports' => array(),
				);
			}

			// Update CSV pair totals
			$results['csv_pair_reports'][$csv_pair_index]['change'] += $report['change'];
			$results['csv_pair_reports'][$csv_pair_index]['updates'] += $report['updates'];

			// Append table report for this CSV pair
			if ( isset( $results['csv_pair_reports'][$csv_pair_index]['table_reports'][$table] ) ) {
				$results['csv_pair_reports'][$csv_pair_index]['table_reports'][$table]['change'] += $report['change'];
				$results['csv_pair_reports'][$csv_pair_index]['table_reports'][$table]['updates'] += $report['updates'];
				$results['csv_pair_reports'][$csv_pair_index]['table_reports'][$table]['end'] = $report['end'];
			} else {
				$results['csv_pair_reports'][$csv_pair_index]['table_reports'][$table] = $report;
			}
		}

		// Sum the values of the new and existing reports.
		$results['change'] 	= isset( $results['change'] ) ? $results['change'] + $report['change'] : $report['change'];
		$results['updates'] = isset( $results['updates'] ) ? $results['updates'] + $report['updates'] : $report['updates'];

		// Append the table report, or create a new one if necessary.
		if ( isset( $results['table_reports'] ) && isset( $results['table_reports'][$table] ) ) {
			$results['table_reports'][$table]['change'] 	= $results['table_reports'][$table]['change'] + $report['change'];
			$results['table_reports'][$table]['updates'] 	= $results['table_reports'][$table]['updates'] + $report['updates'];
			$results['table_reports'][$table]['end'] 		= $report['end'];
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

	/**
	 * Handles CSV file upload via AJAX.
	 * @access public
	 */
	public function upload_csv() {
		// Bail if not authorized.
		if ( ! BSR_Utils::check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'better-search-replace' ) ) );
			return;
		}

		if ( ! isset( $_FILES['bsr_csv_file'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'better-search-replace' ) ) );
			return;
		}

		// Validate and read the CSV file
		$content = BSR_CSV::validate_upload( $_FILES['bsr_csv_file'] );

		if ( is_wp_error( $content ) ) {
			wp_send_json_error( array( 'message' => $content->get_error_message() ) );
			return;
		}

		// Parse the CSV content
		$pairs = BSR_CSV::parse_csv( $content );

		if ( is_wp_error( $pairs ) ) {
			wp_send_json_error( array( 'message' => $pairs->get_error_message() ) );
			return;
		}

		// Store the pairs and return the key
		$key = BSR_CSV::store_pairs( $pairs );

		wp_send_json_success( array(
			'key'   => $key,
			'count' => count( $pairs ),
			'pairs' => $pairs,
		) );
	}

}

$bsr_ajax = new BSR_AJAX;
$bsr_ajax->init();
