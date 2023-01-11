<?php

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

class BSR_Templates_Helper {
    /**
     * Returns a fully qualified path for the given active tab name
     * if the file name is not supported, the default template path is returned.
     *
     * @param string $active_tab
     * @return string
     */
    public static function get_tab_template($active_tab) {
        switch($active_tab) {
            case 'bsr_settings':
                return BSR_PATH . 'templates/bsr-settings.php';
            case 'bsr_help':
                return BSR_PATH . 'templates/bsr-help.php';
            default:
                return BSR_PATH . 'templates/bsr-search-replace.php';
        }
    }
}