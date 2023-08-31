<?php
/**
* Utility functionality for the plugin
*
* @since      1.4.3
*
* @package    Better_Search_Replace
* @subpackage Better_Search_Replace/includes
*/
// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

class BSR_Utils {

    const BSR_URL = 'https://bettersearchreplace.com';
    const WPE_URL = 'https://wpengine.com';

    /**
     * Create an external link for given URL.
     *
     * @param string $url
     * @param string $text
     *
     * @return string
     */
    public static function external_link( $url, $text ) {
        return sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $url ), esc_html( $text ) );
    }

     /**
	 * Generate Better Search Replace site URL with correct UTM tags.
	 *
	 * @param string $path
	 * @param array  $args
	 * @param string $hash
	 *
	 * @return string
	 */
	public static function bsr_url( $path, $args = array(), $hash = '' ) {
		$args = wp_parse_args( $args, array(
			'utm_medium' => 'insideplugin'
		) );
		$args = array_map( 'urlencode', $args );
		$url  = trailingslashit( self::BSR_URL ) . ltrim( $path, '/' );
		$url  = add_query_arg( $args, $url );
		if ( $hash ) {
			$url .= '#' . $hash;
		}
		return $url;
	}

    /**
     * Generate WP Engine site URL with correct UTM tags.
     *
     * @param string $path
     * @param array  $args
     * @param string $hash
     *
     * @return string
     */
    public static function wpe_url( $path = '', $args = array(), $hash = '' )
    {
        $args = wp_parse_args( $args, [
            'utm_medium'   => 'referral',
            'utm_campaign' => 'bx_prod_referral'
        ] );
        $args = array_map( 'urlencode', $args );
        $url  = trailingslashit( self::WPE_URL ) . ltrim( $path, '/' );
        $url  = add_query_arg( $args, $url ) ;

        if ( $hash ) {
            $url .= '#' . $hash;
        }

        return $url;
    }

     /**
     * Get the plugin page url
     *
     * @return string
     **/
    public static function plugin_page_url()
    {
        return menu_page_url( 'better-search-replace', false );
    }

     /**
     * Is current admin screen for bsr.
     *
     * @return bool
     */
    public static function is_bsr_screen() {
        $screen = get_current_screen();
        return $screen->base === 'tools_page_better-search-replace';
    }
}