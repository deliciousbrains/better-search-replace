<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * Sanitized `bsr-ajax` action when `is_authenticated_bsr_ajax_request()` passes.
	 *
	 * @var string
	 */
	private $authenticated_ajax_action = '';

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
		return esc_url_raw(
			add_query_arg(
				'page',
				'better-search-replace',
				admin_url( 'tools.php' )
			)
		);
	}

	/**
	 * Custom admin AJAX requests must include a valid nonce and capability.
	 *
	 * @return bool
	 */
	private function is_authenticated_bsr_ajax_request() {
		$this->authenticated_ajax_action = '';

		if ( ! isset( $_REQUEST['bsr_ajax_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_REQUEST['bsr_ajax_nonce'] ) ), 'bsr_ajax_nonce' ) ) {
			return false;
		}
		if ( ! isset( $_REQUEST['bsr-ajax'] ) ) {
			return false;
		}

		$ajax_action = sanitize_text_field( wp_unslash( (string) $_REQUEST['bsr-ajax'] ) );
		if ( empty( $ajax_action ) ) {
			return false;
		}

		if ( ! bsr_enabled_for_user() ) {
			return false;
		}

		$this->authenticated_ajax_action = $ajax_action;

		return true;
	}

	/**
	 * Set BSR AJAX constant and headers.
	 * @access public
	 */
	public function define_ajax() {

		if ( ! $this->is_authenticated_bsr_ajax_request() ) {
			return;
		}

		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
			// phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
			@ini_set( 'display_errors', 0 );
		}

		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
	}

	/**
	 * Check if we're doing AJAX and fire the related action.
	 * @access public
	 */
	public function do_bsr_ajax() {
		global $wp_query;

		if ( ! $this->is_authenticated_bsr_ajax_request() ) {
			return;
		}

		$wp_query->set( 'bsr-ajax', $this->authenticated_ajax_action );

		if ( $action = $wp_query->get( 'bsr-ajax' ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Dynamic bsr_ajax_{$action} hooks; literal prefix bsr_ajax_.
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
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Action name built from allowlisted values; prefix bsr_ajax_.
			add_action( 'bsr_ajax_' . $action, array( $this, $action ) );
		}
	}

	/**
	 * Processes the search/replace form submitted by the user.
	 * @access public
	 */
	public function process_search_replace() {
		if ( ! BSR_Utils::check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce', false ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended -- Nonce and capability verified via BSR_Utils::check_admin_referer(); PHPCS does not recognize the wrapper.

		$db   = new BSR_DB();
		$step = isset( $_REQUEST['bsr_step'] ) ? absint( $_REQUEST['bsr_step'] ) : 0;
		$page = isset( $_REQUEST['bsr_page'] ) ? absint( $_REQUEST['bsr_page'] ) : 0;

		if ( 0 === $step && 0 === $page ) {
			$args = array();
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- bsr_data is URL-encoded form body; parsed after nonce; fields validated when applied.
			$bsr_data_raw = isset( $_REQUEST['bsr_data'] ) ? wp_unslash( (string) $_REQUEST['bsr_data'] ) : '';
			parse_str( $bsr_data_raw, $args );

			if ( ! isset( $args['select_tables'] ) || ! is_array( $args['select_tables'] ) ) {
				$args['select_tables'] = array();
			}

			$args = array(
				'select_tables'    => array_map( 'trim', $args['select_tables'] ),
				'case_insensitive' => isset( $args['case_insensitive'] ) ? $args['case_insensitive'] : 'off',
				'replace_guids'    => isset( $args['replace_guids'] ) ? $args['replace_guids'] : 'off',
				'dry_run'          => isset( $args['dry_run'] ) ? $args['dry_run'] : 'off',
				'search_for'       => isset( $args['search_for'] ) ? stripslashes( $args['search_for'] ) : '',
				'replace_with'     => isset( $args['replace_with'] ) ? stripslashes( $args['replace_with'] ) : '',
				'completed_pages'  => isset( $args['completed_pages'] ) ? absint( $args['completed_pages'] ) : 0,
			);

			$args['total_pages'] = isset( $args['total_pages'] ) ? absint( $args['total_pages'] ) : $db->get_total_pages( $args['select_tables'] );

			delete_transient( 'bsr_results' );
			delete_option( 'bsr_data' );
		} else {
			$args = get_option( 'bsr_data' );
			if ( ! is_array( $args ) ) {
				$args = array(
					'select_tables'   => array(),
					'total_pages'     => 1,
					'completed_pages' => 0,
				);
			}
		}

		if ( isset( $args['select_tables'][ $step ] ) ) {

			$result = $db->srdb( $args['select_tables'][ $step ], $page, $args );
			$this->append_report( $args['select_tables'][ $step ], $result['table_report'], $args );

			if ( false === $result['table_complete'] ) {
				++$page;
			} else {
				++$step;
				$page = 0;
			}

			if ( isset( $args['select_tables'][ $step ] ) ) {
				$msg_tbl = esc_html( $args['select_tables'][ $step ] );

				$message = sprintf(
					/* translators: 1: current table number, 2: total number of tables, 3: table name. */
					__( 'Processing table %1$d of %2$d: %3$s', 'better-search-replace' ),
					$step + 1,
					count( $args['select_tables'] ),
					$msg_tbl
				);
			}

			++$args['completed_pages'];
			$percentage = $args['completed_pages'] / $args['total_pages'] * 100 . '%';

		} else {
			$db->maybe_update_site_url();
			$step       = 'done';
			$percentage = '100%';
		}

		update_option( 'bsr_data', $args );

		$result = array(
			'step'         => $step,
			'page'         => $page,
			'percentage'   => $percentage,
			'url'          => esc_url_raw( BSR_Utils::tools_page_url( array( 'tab' => 'bsr_search_replace', 'result' => 'true' ) ) ),
			'bsr_data'     => build_query( $args ),
		);

		if ( isset( $message ) ) {
			$result['message'] = $message;
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
		wp_send_json( $result );
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

		if ( ! BSR_Utils::check_admin_referer( 'bsr_ajax_nonce', 'bsr_ajax_nonce', false ) ) {
			return false;
		}

		$results = get_transient( 'bsr_results' ) ? get_transient( 'bsr_results' ) : array();

		$results['search_for']       = isset( $args['search_for'] ) ? $args['search_for'] : '';
		$results['replace_with']    = isset( $args['replace_with'] ) ? $args['replace_with'] : '';
		$results['dry_run']         = isset( $args['dry_run'] ) ? $args['dry_run'] : 'off';
		$results['case_insensitive'] = isset( $args['case_insensitive'] ) ? $args['case_insensitive'] : 'off';
		$results['replace_guids']   = isset( $args['replace_guids'] ) ? $args['replace_guids'] : 'off';

		$results['change']  = isset( $results['change'] ) ? $results['change'] + $report['change'] : $report['change'];
		$results['updates'] = isset( $results['updates'] ) ? $results['updates'] + $report['updates'] : $report['updates'];

		if ( isset( $results['table_reports'] ) && isset( $results['table_reports'][ $table ] ) ) {
			$results['table_reports'][ $table ]['change']  = $results['table_reports'][ $table ]['change'] + $report['change'];
			$results['table_reports'][ $table ]['updates'] = $results['table_reports'][ $table ]['updates'] + $report['updates'];
			$results['table_reports'][ $table ]['end']     = $report['end'];
		} else {
			$results['table_reports'][ $table ] = $report;
		}

		$results['tables'] = count( $results['table_reports'] );

		if ( ! set_transient( 'bsr_results', $results, DAY_IN_SECONDS ) ) {
			return false;
		}

		return true;
	}
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$bsr_ajax = new BSR_AJAX();
$bsr_ajax->init();
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound