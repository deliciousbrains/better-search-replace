<?php

/**
 * CSV processing functionality for bulk search/replace operations.
 *
 * @link       https://bettersearchreplace.com
 * @since      1.5.0
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/includes
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) {
	exit;
}

class BSR_CSV {

	/**
	 * Parse and validate a CSV file containing search/replace pairs.
	 *
	 * @since  1.5.0
	 * @access public
	 * @param  string $csv_content The CSV file content as a string.
	 * @return array|WP_Error Array of search/replace pairs or WP_Error on failure.
	 */
	public static function parse_csv( $csv_content ) {
		if ( empty( $csv_content ) ) {
			return new WP_Error( 'empty_csv', __( 'CSV file is empty.', 'better-search-replace' ) );
		}

		$lines = str_getcsv( $csv_content, "\n" );
		$pairs = array();
		$has_header = false;

		foreach ( $lines as $index => $line ) {
			if ( empty( trim( $line ) ) ) {
				continue; // Skip empty lines
			}

			$row = str_getcsv( $line );

			// Check if first row is a header
			if ( 0 === $index ) {
				if ( isset( $row[0] ) && isset( $row[1] ) ) {
					$col1 = strtolower( trim( $row[0] ) );
					$col2 = strtolower( trim( $row[1] ) );
					if ( 'search_for' === $col1 || 'search' === $col1 ) {
						$has_header = true;
						continue; // Skip header row
					}
				}
			}

			// Validate row has exactly 2 columns
			if ( count( $row ) < 2 ) {
				return new WP_Error(
					'invalid_csv_format',
					sprintf(
						__( 'Invalid CSV format at line %d. Each row must have exactly 2 columns: search_for, replace_with.', 'better-search-replace' ),
						$index + 1
					)
				);
			}

			// Add the search/replace pair
			$pairs[] = array(
				'search_for'   => trim( $row[0] ),
				'replace_with' => trim( $row[1] ),
			);
		}

		if ( empty( $pairs ) ) {
			return new WP_Error( 'no_valid_pairs', __( 'No valid search/replace pairs found in CSV file.', 'better-search-replace' ) );
		}

		return $pairs;
	}

	/**
	 * Validate CSV file upload.
	 *
	 * @since  1.5.0
	 * @access public
	 * @param  array $file The $_FILES array for the uploaded file.
	 * @return string|WP_Error The file content or WP_Error on failure.
	 */
	public static function validate_upload( $file ) {
		if ( ! isset( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'no_file', __( 'No file was uploaded.', 'better-search-replace' ) );
		}

		// Check file extension
		$filename = isset( $file['name'] ) ? $file['name'] : '';
		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		if ( 'csv' !== $ext ) {
			return new WP_Error( 'invalid_file_type', __( 'Only CSV files are allowed.', 'better-search-replace' ) );
		}

		// Check file size (max 5MB)
		if ( isset( $file['size'] ) && $file['size'] > 5 * 1024 * 1024 ) {
			return new WP_Error( 'file_too_large', __( 'CSV file is too large. Maximum size is 5MB.', 'better-search-replace' ) );
		}

		// Read file content
		$content = file_get_contents( $file['tmp_name'] );

		if ( false === $content ) {
			return new WP_Error( 'read_error', __( 'Failed to read CSV file.', 'better-search-replace' ) );
		}

		return $content;
	}

	/**
	 * Store CSV pairs in a transient for processing.
	 *
	 * @since  1.5.0
	 * @access public
	 * @param  array $pairs Array of search/replace pairs.
	 * @return string Unique identifier for the stored pairs.
	 */
	public static function store_pairs( $pairs ) {
		$key = 'bsr_csv_pairs_' . wp_generate_password( 12, false );
		set_transient( $key, $pairs, HOUR_IN_SECONDS );
		return $key;
	}

	/**
	 * Retrieve CSV pairs from transient.
	 *
	 * @since  1.5.0
	 * @access public
	 * @param  string $key The transient key.
	 * @return array|false Array of pairs or false if not found.
	 */
	public static function get_pairs( $key ) {
		// Validate the key format for security
		if ( ! preg_match( '/^bsr_csv_pairs_[a-zA-Z0-9]{12}$/', $key ) ) {
			return false;
		}

		return get_transient( $key );
	}

	/**
	 * Delete stored CSV pairs.
	 *
	 * @since  1.5.0
	 * @access public
	 * @param  string $key The transient key.
	 * @return bool True on success, false on failure.
	 */
	public static function delete_pairs( $key ) {
		// Validate the key format for security
		if ( ! preg_match( '/^bsr_csv_pairs_[a-zA-Z0-9]{12}$/', $key ) ) {
			return false;
		}

		return delete_transient( $key );
	}

}
