<?php
echo get_zip_name( $argv[1], $argv[2] );

function get_zip_name( $project_dir, $plugin ) {
	$project_dir = trim( $project_dir );
	$parent_dir  = $project_dir . '/build-cfg/';
	$config      = $project_dir . '/build-cfg/' . $plugin . '/config.php';
	$zip_name    = false;
	include $config;
	if ( ! file_exists( $config ) ) {
		return $plugin;
	}

	if ( ! $zip_name ) {
		$zip_name = $plugin;
	}

	return $zip_name;
}