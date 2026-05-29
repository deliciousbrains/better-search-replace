<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Registers styles and scripts, adds the custom administration page,
 * and processes user input on the "search/replace" form.
 *
 * @link       https://bettersearchreplace.com
 * @since      1.0.0
 *
 * @package    Better_Search_Replace
 * @subpackage Better_Search_Replace/includes
 */

// Prevent direct access.
if ( ! defined( 'BSR_PATH' ) ) exit;

class BSR_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $better_search_replace    The ID of this plugin.
	 */
	private $better_search_replace;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $better_search_replace       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $better_search_replace, $version ) {
		$this->better_search_replace = $better_search_replace;
		$this->version = $version;
	}

	/**
	 * Register any CSS and JS used by the plugin.
	 * @since    1.0.0
	 * @access 	 public
	 * @param    string $hook Used for determining which page(s) to load our scripts.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'tools_page_better-search-replace' === $hook ) {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'better-search-replace', BSR_URL . "assets/css/better-search-replace$min.css", array(), $this->version, 'all' );
			wp_enqueue_style( 'jquery-style', BSR_URL . 'assets/css/jquery-ui.min.css', array(), $this->version, 'all' );
			wp_enqueue_script( 'jquery-ui-slider' );
			wp_enqueue_script( 'better-search-replace', BSR_URL . "assets/js/better-search-replace$min.js", array( 'jquery' ), $this->version, true );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'thickbox' );

			wp_localize_script( 'better-search-replace', 'bsr_object_vars', array(
				'page_size' 	=> get_option( 'bsr_page_size' ) ? absint( get_option( 'bsr_page_size' ) ) : 20000,
				'endpoint' 		=> BSR_AJAX::get_endpoint(),
				'ajax_nonce' 	=> wp_create_nonce( 'bsr_ajax_nonce' ),
				'no_search' 	=> __( 'No search string was defined, please enter a URL or string to search for.', 'better-search-replace' ),
				'no_tables' 	=> __( 'Please select the tables that you want to update.', 'better-search-replace' ),
				'unknown' 		=> __( 'An error occurred processing your request. Try decreasing the "Max Page Size", or contact support.', 'better-search-replace' ),
				'processing'	=> __( 'Processing...', 'better-search-replace' )
			) );
		}
	}

	/**
	 * Register any menu pages used by the plugin.
	 * @since  1.0.0
	 * @access public
	 */
	public function bsr_menu_pages() {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Legacy filter name; public API for capability override.
		$cap = apply_filters( 'bsr_capability', 'manage_options' );
		add_submenu_page( 'tools.php', __( 'Better Search Replace', 'better-search-replace' ), __( 'Better Search Replace', 'better-search-replace' ), $cap, 'better-search-replace', array( $this, 'bsr_menu_pages_callback' ) );
	}

	/**
	 * The callback for creating a new submenu page under the "Tools" menu.
	 * @access public
	 */
	public function bsr_menu_pages_callback() {
		require_once BSR_PATH . 'includes/class-bsr-templates-helper.php';
		require_once BSR_PATH . 'templates/bsr-dashboard.php';
	}

	/**
	 * Renders the result or error onto the better-search-replace admin page.
	 */
	public static function render_result() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended -- Each GET branch calls BSR_Utils::validate_tools_screen_get() before use.
		if ( ! filter_has_var( INPUT_GET, 'result' ) || ! BSR_Utils::validate_tools_screen_get() ) {
			// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
			return;
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
		$result = get_transient( 'bsr_results' );

		// Have results with required fields set with correctly typed data?
		if (
			empty( $result ) ||
			! isset( $result['tables'] ) ||
			! is_int( $result['tables'] ) ||
			! isset( $result['change'] ) ||
			! is_int( $result['change'] ) ||
			! isset( $result['updates'] ) ||
			! is_int( $result['updates'] )
		) {
			return;
		}

		// TB_iframe is required for Thickbox iframe mode. Core thickbox truncates src at "TB_"; bsr_thickbox_fix() in assets/js/better-search-replace.js restores the full URL for action=bsr_view_details only.
		$details_url = self::get_view_details_url(
			array(
				'TB_iframe' => 'true',
				'width'     => '800',
				'height'    => '500',
			)
		);

		$result_message_allowed_html = array(
			'p'      => array(),
			'strong' => array(),
			'a'      => array(
				'href'  => true,
				'class' => true,
				'title' => true,
			),
		);

		echo '<div class="updated bsr-updated" style="display: none;">';

		if ( isset( $result['dry_run'] ) && $result['dry_run'] === 'on' ) {
			$msg = sprintf(
				/* translators: 1: number of tables, 2: cells found, 3: changes made, 4: details URL, 5: Thickbox title attribute. */
				__(
					'<p><strong>DRY RUN:</strong> <strong>%1$d</strong> tables were searched, <strong>%2$d</strong> cells were found that need to be updated, and <strong>%3$d</strong> changes were made.</p><p><a href="%4$s" class="thickbox" title="%5$s">Click here</a> for more details, or use the form below to run the search/replace.</p>',
					'better-search-replace'
				),
				(int) $result['tables'],
				(int) $result['change'],
				(int) $result['updates'],
				esc_url( $details_url ),
				esc_attr__( 'Dry Run Details', 'better-search-replace' )
			);
			echo wp_kses( $msg, $result_message_allowed_html );
		} else {
			$msg = sprintf(
				/* translators: 1: tables searched, 2: cells changed, 3: updates, 4: details URL, 5: Thickbox title attribute. */
				__(
					'<p>During the search/replace, <strong>%1$d</strong> tables were searched, with <strong>%2$d</strong> cells changed in <strong>%3$d</strong> updates.</p><p><a href="%4$s" class="thickbox" title="%5$s">Click here</a> for more details.</p>',
					'better-search-replace'
				),
				(int) $result['tables'],
				(int) $result['change'],
				(int) $result['updates'],
				esc_url( $details_url ),
				esc_attr__( 'Search/Replace Details', 'better-search-replace' )
			);
			echo wp_kses( $msg, $result_message_allowed_html );
		}

		echo '</div>';
	}

	/**
	 * Prefills the given value on the search/replace page (dry run, live run, from profile).
	 * @access public
	 * @param  string $value The value to check for.
	 * @param  string $type  The type of the value we're filling.
	 */
	public static function prefill_value( $value, $type = 'text' ) {

		if ( ! BSR_Utils::validate_tools_screen_get() ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended -- Tools GET gated by BSR_Utils::validate_tools_screen_get().
		if ( filter_has_var( INPUT_GET, 'result' ) && get_transient( 'bsr_results' ) ) {
			$values = get_transient( 'bsr_results' );
		} else {
			$values = array();
		}

		// Prefill the value.
		if ( isset( $values[ $value ] ) ) {

			if ( 'checkbox' === $type && 'on' === $values[ $value ] ) {
				echo 'checked';
			} else {
				echo esc_attr( str_replace( '#BSR_BACKSLASH#', '\\', $values[ $value ] ) );
			}
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Loads the tables available to run a search replace, prefilling if already
	 * selected the tables.
	 * @access public
	 */
	public static function load_tables() {

		if ( ! BSR_Utils::validate_tools_screen_get() ) {
			echo '<select id="bsr-table-select" name="select_tables[]" multiple="multiple" style=""></select>';
			return;
		}

		$tables = BSR_DB::get_tables();
		$sizes  = BSR_DB::get_sizes();

		echo '<select id="bsr-table-select" name="select_tables[]" multiple="multiple" style="">';

		$result           = null;
		$transient_result = get_transient( 'bsr_results' );

		// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended -- Tools GET gated by BSR_Utils::validate_tools_screen_get().
		if ( filter_has_var( INPUT_GET, 'result' ) && $transient_result ) {
			$result = $transient_result;
		}

		foreach ( $tables as $table ) {

			$table_size = isset( $sizes[ $table ] ) ? $sizes[ $table ] : '';
			$selected   = false;

			if ( is_array( $result ) && isset( $result['table_reports'][ $table ] ) ) {
				$selected = true;
			}

			printf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $table ),
				$selected ? ' selected="selected"' : '',
				esc_html( $table . ' ' . $table_size )
			);
		}

		echo '</select>';

		// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * admin-post.php URL for bsr_view_details with nonce.
	 *
	 * For Thickbox iframe links you may pass TB_iframe=true (and width/height). Core thickbox.js
	 * truncates iframe src at the substring "TB_"; bsr_thickbox_fix() in assets/js/better-search-replace.js
	 * restores the full URL when action=bsr_view_details. Do not add other query keys whose names
	 * contain "TB_".
	 *
	 * @param array $extra Query arguments (action is set automatically; _wpnonce is always appended last).
	 * @return string Raw URL; use esc_url() when printing in HTML.
	 */
	public static function get_view_details_url( $extra = array() ) {
		$args = array_merge( array( 'action' => 'bsr_view_details' ), (array) $extra );
		$url  = add_query_arg( $args, admin_url( 'admin-post.php' ) );

		return add_query_arg( '_wpnonce', wp_create_nonce( 'bsr_view_details' ), $url );
	}

	/**
	 * Loads the result details (via Thickbox).
	 * @access public
	 */
	public function load_details() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended -- Nonce verified below; Thickbox GET must not rely on referer.
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_REQUEST['_wpnonce'] ) ), 'bsr_view_details' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'better-search-replace' ), '', array( 'response' => 403 ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended

		if ( ! bsr_enabled_for_user() ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to view this content.', 'better-search-replace' ), '', array( 'response' => 403 ) );
		}

		if ( ! get_transient( 'bsr_results' ) ) {
			return;
		}

		$results = get_transient( 'bsr_results' );
		$min     = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'common' );
		wp_enqueue_style(
			'better-search-replace',
			BSR_URL . 'assets/css/better-search-replace' . $min . '.css',
			array( 'common' ),
			BSR_VERSION,
			'all'
		);
		wp_print_styles( array( 'common', 'better-search-replace' ) );

		$upgrade_url = 'https://deliciousbrains.com/better-search-replace/upgrade/?utm_source=insideplugin&utm_medium=web&utm_content=tooltip&utm_campaign=bsr-to-migrate';
		?>
		<div style="padding: 32px; background-color: var(--color-white); min-height: 100%;">
			<table id="bsr-results-table" class="widefat">
				<thead>
					<tr>
						<th class="bsr-first"><?php esc_html_e( 'Table', 'better-search-replace' ); ?></th>
						<th class="bsr-second"><?php esc_html_e( 'Changes Found', 'better-search-replace' ); ?></th>
						<th class="bsr-third"><?php esc_html_e( 'Rows Updated', 'better-search-replace' ); ?></th>
						<th class="bsr-fourth"><?php esc_html_e( 'Time', 'better-search-replace' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $results['table_reports'] as $table_name => $report ) {
					$time = $report['end'] - $report['start'];

					$change_cell = '';
					if ( 0 !== (int) $report['change'] ) {
						$change_cell  = '<a class="tooltip">' . esc_html( (string) $report['change'] ) . '</a>';
						$change_cell .= '<span class="helper-message right">';
						$change_cell .= wp_kses_post(
							sprintf(
								/* translators: %s: URL to the upgrade page. */
								__( '<a href="%s" target="_blank">UPGRADE</a> to view details on the exact changes that will be made.', 'better-search-replace' ),
								esc_url( $upgrade_url )
							)
						);
						$change_cell .= '</span>';
					}

					$updates_cell = '0';
					if ( 0 !== (int) $report['updates'] ) {
						$updates_cell = '<strong>' . esc_html( (string) $report['updates'] ) . '</strong>';
					}

					printf(
						'<tr><td class="bsr-first">%1$s</td><td class="bsr-second">%2$s</td><td class="bsr-third">%3$s</td><td class="bsr-fourth">%4$s%5$s</td></tr>',
						esc_html( $table_name ),
						wp_kses_post( $change_cell ),
						wp_kses_post( $updates_cell ),
						esc_html( (string) round( $time, 3 ) ),
						esc_html__( ' seconds', 'better-search-replace' )
					);
				}
				?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Registers our settings in the options table.
	 * @access public
	 */
	public function register_option() {
		register_setting( 'bsr_settings_fields', 'bsr_page_size', 'absint' );
	}

	/**
	 * Downloads the system info file for support.
	 * @access public
	 */
	public function download_sysinfo() {
		if ( ! BSR_Utils::check_admin_referer( 'bsr_download_sysinfo', 'bsr_sysinfo_nonce', false ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended -- Nonce verified via BSR_Utils::check_admin_referer() above; PHPCS does not recognize the wrapper.
		if ( ! isset( $_POST['bsr-sysinfo'] ) ) {
			// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
			return;
		}

		nocache_headers();

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="bsr-system-info.txt"' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified via BSR_Utils::check_admin_referer(); isset() above; plain-text system info for download; tags stripped below.
		$sysinfo = wp_unslash( (string) $_POST['bsr-sysinfo'] );

		echo esc_html( wp_strip_all_tags( $sysinfo ) );
		// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
		die();
	}

	/**
	 * Displays the link to upgrade to BSR Pro
	 * @access public
	 * @param array $links The links assigned to the plugin.
	 */
	public function meta_upgrade_link( $links, $file ) {
		$plugin = plugin_basename( BSR_FILE );

		if ( $file == $plugin ) {
			return array_merge(
				$links,
				array(
					'<a href="' . esc_url( 'https://bettersearchreplace.com/?utm_source=insideplugin&utm_medium=web&utm_content=plugins-page&utm_campaign=pro-upsell' ) . '">' . esc_html__( 'Upgrade to Pro', 'better-search-replace' ) . '</a>',
				)
			);
		}

		return $links;
	}

}
