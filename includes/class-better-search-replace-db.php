<?php

/**
 * Processes database-related functionality.
 * @since      1.0
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/includes
 */
class Better_Search_Replace_DB {

	/**
	 * The WordPress database class.
	 * @var WPDB
	 */
	private $wpdb;

	/**
	 * Initializes the class and its properties.
	 * @access public
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * Returns an array of tables in the database.
	 * @access public
	 * @return array
	 */
	public function get_tables() {
		$tables = $this->wpdb->get_col( 'SHOW TABLES' );
		return $tables;
	}

	/**
	 * Runs the search replace.
	 * @access public
	 * @param  $tables 	The tables to run the search/replace on.
	 * @param  $search 	The string to search for.
	 * @param  $replace The string to replace with.
	 */
	public function run( $tables = array(), $search, $replace ) {
		if ( count( $tables ) !== 0 ) {
			foreach ( $tables as $table ) {
				$this->srdb( $table, $search, $replace );
			}
		}
	}

	/**
	 * Adapated from interconnect/it's search/replace script.
	 * Modified to use WordPress wpdb functions instead of PHP's native mysql/pdo functions.
	 * 
	 * @link https://interconnectit.com/products/search-and-replace-for-wordpress-databases/
	 * 
	 * @access public
	 * @param  string $table 	The table to run the replacement on.
	 * @param  string $search 	The string to replace.
	 * @param  string $replace 	The string to replace with.
	 * @return array   			Collection of information gathered during the run.
	 */
	public function srdb( $table, $search = '', $replace = '' ) {

		// Get a list of columns in this table.
		$columns = array();
		$fields  = $this->wpdb->get_results( 'DESCRIBE ' . $table );
		foreach ( $fields as $column ) {
			$columns[$column->Field] = $column->Key == 'PRI' ? true : false;
		}
		$this->wpdb->flush();

		// Count the number of rows we have in the table if large we'll split into blocks, This is a mod from Simon Wheatley
		$this->wpdb->get_results( 'SELECT COUNT(*) FROM ' . $table );
		$row_count = $this->wpdb->num_rows;
		if ( $row_count == 0 )
			continue;

		$page_size 	= 50000;
		$pages 		= ceil( $row_count / $page_size );

		for( $page = 0; $page < $pages; $page++ ) {

			$current_row 	= 0;
			$start 			= $page * $page_size;
			$end 			= $start + $page_size;
			
			// Grab the content of the table.
			$data = $this->wpdb->get_results( "SELECT * FROM $table LIMIT $start, $end", ARRAY_A );
			
			// Loop through the data.
			foreach ( $data as $row ) {
				$current_row++;
				$update_sql = array();
				$where_sql 	= array();
				$upd 		= false;

				foreach( $columns as $column => $primary_key ) {
					$edited_data = $data_to_fix = $row[ $column ];

					// Run a search replace on the data that'll respect the serialisation.
					$edited_data = $this->recursive_unserialize_replace( $search, $replace, $data_to_fix );

					// Something was changed
					if ( $edited_data != $data_to_fix ) {
						$update_sql[] = $column . ' = "' . $this->mysql_escape_mimic( $edited_data ) . '"';
						$upd = true;
					}

					if ( $primary_key )
						$where_sql[] = $column . ' = "' .  $this->mysql_escape_mimic( $data_to_fix ) . '"';
				}

				if ( $upd && ! empty( $where_sql ) ) {
					$sql = 'UPDATE ' . $table . ' SET ' . implode( ', ', $update_sql ) . ' WHERE ' . implode( ' AND ', array_filter( $where_sql ) );
					$result = $this->wpdb->query( $sql );
					if ( ! $result ) {
						$error_msg 	= sprintf( __( 'Error updating the table: %s.', 'better-search-replace' ), $table );
					}
					$success_msg = sprintf( __( 'Successfully updated %s fields on table: %s.', 'better-search-replace' ), count( $update_sql ), $table );
				} elseif ( $upd ) {
					$error_msg = sprintf( __( 'The table "%s" has no primary key. Manual change needed on row %s.', 'better-search-replace' ), $table, $current_row );
				}
			}
		}
		
		// Flush the results.
		$this->wpdb->flush();

		// Return the resulting message for this table.
		if ( isset( $error_msg ) ) {
			return $error_msg;
		} elseif ( isset( $success_msg ) ) {
			return $success_msg;
		}
	}

	/**
	 * Adapated from interconnect/it's search/replace script.
	 * 
	 * @link https://interconnectit.com/products/search-and-replace-for-wordpress-databases/
	 * 
	 * Take a serialised array and unserialise it replacing elements as needed and
	 * unserialising any subordinate arrays and performing the replace on those too.
	 * 
	 * @access private
	 * @param  string $from       String we're looking to replace.
	 * @param  string $to         What we want it to be replaced with
	 * @param  array  $data       Used to pass any subordinate arrays back to in.
	 * @param  bool   $serialised Does the array passed via $data need serialising.
	 *
	 * @return array	The original array with all elements replaced as needed.
	 */
	public function recursive_unserialize_replace( $from = '', $to = '', $data = '', $serialised = false ) {
		try {

			if ( is_string( $data ) && ( $unserialized = @unserialize( $data ) ) !== false ) {
				$data = $this->recursive_unserialize_replace( $from, $to, $unserialized, true );
			}

			elseif ( is_array( $data ) ) {
				$_tmp = array( );
				foreach ( $data as $key => $value ) {
					$_tmp[ $key ] = $this->recursive_unserialize_replace( $from, $to, $value, false );
				}

				$data = $_tmp;
				unset( $_tmp );
			}

			// Submitted by Tina Matter
			elseif ( is_object( $data ) ) {
				$dataClass 	= get_class( $data );
				$_tmp  		= new $dataClass();
				foreach ( $data as $key => $value ) {
					$_tmp->$key = $this->recursive_unserialize_replace( $from, $to, $value, false );
				}

				$data = $_tmp;
				unset( $_tmp );
			}
			
			else {
				if ( is_string( $data ) )
					$data = str_replace( $from, $to, $data );
			}

			if ( $serialised )
				return serialize( $data );

		} catch( Exception $error ) {

		}

		return $data;
	}

	/**
	 * Mimics the mysql_real_escape_string function. Adapted from a post by 'feedr' on php.net.
	 * @link   http://php.net/manual/en/function.mysql-real-escape-string.php#101248
	 * @access public
	 * @param  string $input The string to escape.
	 */
	public function mysql_escape_mimic( $input ) {

	    if( is_array( $input ) ) 
	        return array_map( __METHOD__, $input ); 

	    if( ! empty( $input ) && is_string( $input ) ) { 
	        return str_replace( array( '\\', "\0", "\n", "\r", "'", '"', "\x1a" ), array( '\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z' ), $input ); 
	    } 

	    return $input; 
	}

}