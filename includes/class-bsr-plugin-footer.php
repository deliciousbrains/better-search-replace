<?php
/**
* Plugin footer functionality for the plugin
*
* @since      1.4.3
*
* @package    Better_Search_Replace
* @subpackage Better_Search_Replace/includes
*/
// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

class BSR_Plugin_Footer {

    /**
     * Filter admin footer text for BSR pages
     *
     * @param string $text
     * @return string
     * @handles admin_footer_text
     **/
    public function admin_footer_text( $text ) {
        if ( ! BSR_Utils::is_bsr_screen() ) {
            return $text;
        }

        $product_link = BSR_Utils::external_link(
			BSR_Utils::bsr_url(
				'',
				[
                    'utm_source'   => 'bsr_free',
                    'utm_medium'   => 'insideplugin',
                    'utm_campaign' => 'plugin_footer',
                    'utm_content'  => 'footer_colophon'
                ]
			),
			BSR_NAME
		);
        $wpe_link = BSR_Utils::external_link(
            BSR_Utils::wpe_url(
                '',
                [
                    'utm_source'  => 'bsr_plugin',
                    'utm_content' => 'bsr_free_plugin_footer_text'
                ]
            ), 
            'WP Engine'
        );
        return sprintf(
            /* translators: %1$s is a link to BSR's website, and %2$s is a link to WP Engine's website. */
            __( '%1$s is developed and maintained by %2$s.', 'better-search-replace' ),
            $product_link,
            $wpe_link
        );
    }

    /**
     * Filter update footer text for BSR pages
     *
     * @param string $content
     * @return string
     * @handles update_footer
     **/
    public function update_footer( $content ) {
        if ( ! BSR_Utils::is_bsr_screen() ) {
            return $content;
        }
        $utm_params = [
            'utm_source'   => 'bsr_free',
            'utm_campaign' => 'plugin_footer',
            'utm_content'  => 'footer_navigation'
        ];

        $links[] = BSR_Utils::external_link(
			BSR_Utils::bsr_url(
				'/docs/',
				$utm_params
			),
			__( 'Documentation', 'better-search-replace' )
		);

		$links[] = '<a href="' . BSR_Utils::plugin_page_url() . '&tab=bsr_help">' . __( 'Support', 'better-search-replace' ) . '</a>';

		$links[] = BSR_Utils::external_link(
			BSR_Utils::bsr_url(
				'/feedback/',
				$utm_params
			),
			__( 'Feedback', 'better-search-replace' )
		);
        if ( defined( 'BSR_NAME' ) && defined( 'BSR_VERSION' ) ) {
            $links[] = BSR_NAME . ' ' . BSR_VERSION;
        }
		
        return join( ' &#8729; ', $links );
    }
}