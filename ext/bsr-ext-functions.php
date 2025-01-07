<?php

if ( ! function_exists( 'bsr_check_for_upgrades' ) ) {
	/**
	 * Initialize the checking for plugin updates.
	 */
	function bsr_check_for_upgrades() {
		$properties = array(
			'plugin_slug'     => 'better-search-replace',
			'plugin_basename' => plugin_basename( BSR_FILE ),
		);

		require_once BSR_PATH . 'ext/class-bsr-plugin-updater.php';
		new DeliciousBrains\Better_Search_Replace\BSR_Plugin_Updater( $properties );
	}

	add_action( 'admin_init', 'bsr_check_for_upgrades' );
}
