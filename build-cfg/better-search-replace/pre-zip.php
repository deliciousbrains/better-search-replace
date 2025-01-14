<?php

if ( ! $publish ) {
	return;
}

echo 'Publish to WP.org? (Y/n) ';
if ( 'Y' == strtoupper( trim( fgets( STDIN ) ) ) ) {
	system( 'rm -fR svn' ); // Cleanup before checkout to prevent errors
	system( 'svn co -q http://svn.wp-plugins.org/better-search-replace svn' );
	system( 'rm -R svn/trunk' );
	system( 'mkdir svn/trunk' );
	system( "rsync -r --filter '- ext/' $plugin_slug/* svn/trunk/" );
	system( "cat svn/trunk/better-search-replace.php | grep -v '* Update URI:' > svn/trunk/better-search-replace.php.tmp" );
	system( 'mv svn/trunk/better-search-replace.php.tmp svn/trunk/better-search-replace.php' );
	system( 'svn stat svn/ | grep \'^\?\' | awk \'{print $2}\' | xargs -I x svn add x@' );
	system( 'svn stat svn/ | grep \'^\!\' | awk \'{print $2}\' | xargs -I x svn rm --force x@' );
	system( 'svn stat svn/' );

	echo 'Commit to WP.org? (Y/n)? ';
	if ( 'Y' == strtoupper( trim( fgets( STDIN ) ) ) ) {
		system( "svn ci --username deliciousbrains svn/ -m 'Deploy version $version'" );
		system( "svn cp --username deliciousbrains http://svn.wp-plugins.org/better-search-replace/trunk http://svn.wp-plugins.org/better-search-replace/tags/$version  -m 'Deploy version $version'" );
	}

	system( 'rm -fR svn' ); // All done
}
