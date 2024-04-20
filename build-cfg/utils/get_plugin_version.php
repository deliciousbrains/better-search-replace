<?php
echo get_version( $argv[1], $argv[2] );

function get_version( $project_dir, $plugin_slug ) {
	$project_dir = trim( $project_dir );
	$parent_dir  = $project_dir . '/build-cfg/';
	$config      = $project_dir . '/build-cfg/' . $plugin_slug . '/config.php';
	include $config;
	$version_check_hook = $project_dir . '/build-cfg/' . $plugin_slug . '/version-check.php';

	if ( file_exists( $version_check_hook ) ) {
		include $version_check_hook;

		//$messages = '';

		foreach ( $version_checks as $file => $regexes ) {
			$file = "$src_dir/$file";

			if ( ! file_exists( $file ) ) {
				$messages .= "Whoa! Couldn't find $file\n";
				continue;
			}

			$file_content = file_get_contents( $file );

			if ( ! $file_content ) {
				$messages .= "Whoa! Could not read contents of $file\n";
				continue;
			}

			foreach ( $regexes as $regex => $context ) {
				if ( ! preg_match( $regex, $file_content, $matches ) ) {
					$messages .= "Whoa! Couldn't find $context version number in $file\n";
					continue;
				}

				if ( isset( $matches[1] ) ) {
					return $matches[1];
				}
			}
		}
	}

	echo $messages;

	return false;
}