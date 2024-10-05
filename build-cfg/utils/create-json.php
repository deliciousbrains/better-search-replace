<?php

class Plugin_Meta
{
    /**
     * Pluck the plugin meta data and return as json.
     *
     * @param string $readme_file      Path to readme.txt file.
     * @param string $main_plugin_file Path to main plugin file.
     *
     * @return string
     */
    public function get_plugin_meta( $readme_file, $main_plugin_file ) {
        $readme = file_get_contents( $readme_file );
        $plugin = file_get_contents( $main_plugin_file );

        $plugin_info = array_merge(
            $this->get_readme_meta( $readme ),
            $this->get_php_file_meta( $plugin )
        );

        return json_encode( $plugin_info, JSON_PRETTY_PRINT );
    }

    /**
     * Get the meta data from the readme.txt file.
     *
     * @param string $readme The contents of the readme.txt file.
     *
     * @return array<string, string>
     */
    public function get_readme_meta( $readme ) {
        $readme_headers = array(
            'contributors'      => 'contributors',
            'tags'              => 'Tags',
            'requires_at_least' => 'Requires at least',
            'tested_up_to'      => 'Tested up to',
            'stable_tag'        => 'Stable tag',
        );

        $meta = $this->get_file_meta( $readme, $readme_headers );

        $section_names = array(
            'description' => 'Description',
            'changelog'   => 'Changelog',
            'faq'         => 'Frequently Asked Questions',
        );

        $sections = $this->get_readme_sections( $readme, $section_names );
        if ( ! empty( $sections ) ) {
            $meta['sections'] = $sections;
        }

        return $meta;
    }

    /**
     * Get the meta data from the main plugin file.
     *
     * @param string $plugin The contents of the main plugin file.
     *
     * @return array<string, string>
     */
    public function get_php_file_meta( $plugin ) {
        $plugin_headers = array(
            'name'        => 'Plugin Name',
            'pluginURI'   => 'Plugin URI',
            'version'     => 'Version',
            'description' => 'Description',
            'author'      => 'Author',
            'authorURI'   => 'Author URI',
            'textDomain'  => 'Text Domain',
            'domainPath'  => 'Domain Path',
            'network'     => 'Network',
        );

        return $this->get_file_meta( $plugin, $plugin_headers );
    }

    /**
     * Retrieve metadata from a file.
     *
     * Searches for metadata in the first 8kiB of a file, such as a plugin or theme.
     * Each piece of metadata must be on its own line. Fields can not span multiple
     * lines, the value will get cut at the end of the first line.
     *
     *
     * @param string $content File contents.
     * @param array  $headers List of headers, in the format array('HeaderKey' => 'Header Name').
     *
     * @return array Array of file headers in `HeaderKey => Header Value` format.
     */
    protected function get_file_meta( $content, $headers ) {
        // Make sure we catch CR-only line endings.
        $content     = str_replace( "\r", "\n", $content );
        $all_headers = array();

        foreach ( $headers as $field => $regex ) {
            $pattern = '/^[ \t\/*#@]*' . preg_quote($regex, '/' ) . ':(.*)$/mi';

            if ( preg_match( $pattern , $content, $match ) && $match[1] ) {
                $all_headers[ $field ] = $this->clean_header_comment( $match[1] );
            } else {
                $all_headers[ $field ] = '';
            }
        }

        return $all_headers;
    }

    protected function get_readme_sections( $str, $defined_sections ) {
        $parts = preg_split(
            '/^\s*==\s*(.+?)\s*==/m',
            $str,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );

		$sections = array();
		for ( $i=1; $i <= count( $parts ); $i +=2 ) {
            $title = $this->sanitize_text( $parts[$i-1] );
            if ( ! in_array( $title, $defined_sections ) ) {
                continue;
            }

            $content          = $this->sanitize_text( $parts[ $i ] );
            $key              = array_flip( $defined_sections )[ $title ];
            $sections[ $key ] = $content;
		}

        return $sections;
    }

    /**
     * Strip close comment and close php tags from file headers used by WP.
     *
     * @see https://core.trac.wordpress.org/ticket/8497
     *
     * @param string $str Header comment to clean up.
     *
     * @return string
     */
    protected function clean_header_comment( $str ) {
        return trim( preg_replace( "/\s*(?:\*\/|\?>).*/", '', $str ) );
    }

    /**
     * Non fancy text sanitization.
     *
     * @param string $text
     *
     * @return string
     */
    private function sanitize_text( $text ) {
		$text = trim($text);

		return $text;
	}

}

$plugin_meta = new Plugin_Meta();
echo $plugin_meta->get_plugin_meta( $argv[1], $argv[2] );
