<?php
$version_checks = array(
	"better-search-replace.php" => array(
		'@Version:\s+(.*)\n@'                           => 'header',
		"/define\(\s*'BSR_VERSION',\s*'([^']+)'\s*\);/" => 'constant',
	),
);