<?php

if ( ! $publish ) {
	return;
}

echo 'Publish to WP.org? (Y/n) ';
if ( 'Y' == strtoupper( trim( fgets( STDIN ) ) ) ) {
	system( 'rm -fR svn' ); // Cleanup before checkout to prevent errors
	system( 'svn co -q --depth immediates https://plugins.svn.wordpress.org/better-search-replace/ svn' );
	system( 'svn update -q --set-depth infinity svn/trunk' );
	system( 'svn update -q --set-depth immediates svn/tags' );
	system( 'rm -R svn/trunk' );
	system( 'mkdir svn/trunk' );
	system( "rsync -r --filter '- ext/' $plugin_slug/* svn/trunk/" );
	system( "cat svn/trunk/better-search-replace.php | grep -v '* Update URI:' > svn/trunk/better-search-replace.php.tmp" );
	system( 'mv svn/trunk/better-search-replace.php.tmp svn/trunk/better-search-replace.php' );
	system( 'svn stat svn/ | grep \'^\?\' | awk \'{print $2}\' | xargs -I x svn add x@' );
	system( 'svn stat svn/ | grep \'^\!\' | awk \'{print $2}\' | xargs -I x svn rm --force x@' );
	system( "svn cp svn/trunk svn/tags/$version" );
	system( 'svn stat svn/' );

	echo 'Commit to WP.org? (Y/n)? ';
	if ( 'Y' == strtoupper( trim( fgets( STDIN ) ) ) ) {
		system( "svn ci --username deliciousbrains svn/ -m 'Deploy version $version'" );
	}

	system( 'rm -fR svn' ); // All done
}
