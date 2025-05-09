<?php

/**
 * Core Class
 */
class Drive_Embedder_Core {

	protected function __construct() {

		$this->add_actions();
		register_activation_hook( $this->my_plugin_basename(), [ $this, 'gdm_activation_hook' ] );
	}

	// May be overridden in basic or premium
	public function gdm_activation_hook( $network_wide ) {

		global $gdm_core_already_exists;

		if ( $gdm_core_already_exists ) {
			deactivate_plugins( $this->my_plugin_basename() );
			esc_html_e( 'Please deactivate the version of Google Drive Embedder already in use before activating this version. Only one may be activated at a time.', 'google-drive-embedder' );
			exit;
		}
	}

	public function gdm_gather_scopes( $scopes ) {
		return array_merge( $scopes, [ 'https://www.googleapis.com/auth/drive.readonly' ] );
	}

	public function gdm_media_button() {
		?>
		<a class="thickbox button" href="#TB_inline?width=700&height=484&inlineId=gdm-choose-drivefile" id="gdm-thickbox-trigger" style="padding-left: .4em;"
		   title="<?php esc_attr_e( 'Add Google File', 'google-drive-embedder' ); ?>">
			<span class="wp-media-buttons-icon" id="gdm-media-button"></span>
			<?php esc_html_e( 'Add Google File', 'google-drive-embedder' ); ?>
		</a>
		<?php
	}

	public function gdm_register_scripts() {

		$extra_js_name = $this->get_extra_js_name();

		wp_register_script( 'gdm_simple_browser_js', $this->my_plugin_url() . 'js/gdm-simple-browser.js', [], $this->plugin_version );

		if ( $extra_js_name !== 'basic' ) {
			wp_register_script( 'gdm_premium_drive_browser_js', $this->my_plugin_url() . 'js/gdm-premium-drive-browser.js', [ 'gdm_simple_browser_js' ], $this->plugin_version );
		}

		wp_register_script( 'gdm_colorbox_js', $this->my_plugin_url() . 'js/jquery.colorbox.js', [ 'jquery' ], $this->plugin_version );
		wp_register_style( 'gdm_colorbox_css', $this->my_plugin_url() . 'css/gdm-colorbox.css', [], $this->plugin_version );

		wp_register_script( 'gdm_base_servicehandler_js', $this->my_plugin_url() . 'js/gdm-base-servicehandler.js', ( 'basic' !== $extra_js_name ? [ 'gdm_premium_drive_browser_js' ] : [] ), $this->plugin_version );
		wp_register_script( 'gdm_' . $extra_js_name . '_drivefile_js', $this->my_plugin_url() . 'js/gdm-' . $extra_js_name . '-drivefile.js', [ 'jquery' ], $this->plugin_version );
		wp_register_script(
			'gdm_choose_drivefile_js',
			$this->my_plugin_url() . 'js/gdm-choose-drivefile.js',
			[
				'jquery',
				'gdm_simple_browser_js',
				'gdm_base_servicehandler_js',
				'gdm_' . $extra_js_name . '_drivefile_js',
				'gdm_colorbox_js',
				'google-js-client',
				'google-js-api-update',
			],
			$this->plugin_version
		);
	}

	public function gdm_admin_load_scripts() {

		$this->gdm_register_scripts();

		wp_enqueue_script( 'google-js-client', 'https://accounts.google.com/gsi/client' );
		wp_enqueue_script( 'google-js-api-update', 'https://apis.google.com/js/api.js' );
		wp_localize_script( 'gdm_choose_drivefile_js', 'gdm_trans', $this->get_translation_array() );
		wp_enqueue_script( 'gdm_choose_drivefile_js' );
		wp_enqueue_style( 'gdm_choose_drivefile_css', $this->my_plugin_url() . 'css/gdm-choose-drivefile.css' );

		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );

		wp_enqueue_script( 'gdm_colorbox_js' );
		wp_enqueue_style( 'gdm_colorbox_css' );
	}

	protected function get_translation_array() {

		// Get Google Client ID.
		$clientid = apply_filters( 'gal_get_clientid', '' );

		// Get current user's email address.
		$current_user = wp_get_current_user();
		$email        = $current_user ? $current_user->user_email : '';

		return [
			'scopes'                   => implode( ' ', $this->gdm_gather_scopes( [] ) ),
			'clientid'                 => $clientid,
			'useremail'                => $email,
			'allow_non_iframe_folders' => $this->allow_non_iframe_folders(),
		];
	}

	protected function get_extra_js_name() {
		return '';
	}

	protected function extra_drive_tabs() {
		?>
		<a href="#allfiles" id="allfiles-tab" class="nav-tab nav-tab-active"><?php esc_html_e( 'All Files', 'google-drive-embedder' ); ?></a>
		<a href="#calendar" id="calendar-tab" class="nav-tab">+</a>
		<?php
	}

	public function gdm_admin_footer() {
		?>
		<div id="gdm-choose-drivefile" style="display: none;">
			<h3 id="gdm-tabs" class="nav-tab-wrapper">
				<?php $this->extra_drive_tabs(); ?>
			</h3>

			<div class="wrap gdm-wrap">

				<div id="gdm-search-area">
					<input type="text" id="gdm-search-box" placeholder="<?php esc_attr_e( 'Enter text to search (then press Enter)', 'google-drive-embedder' ); ?>" disabled="disabled"/>
					<a href="#" id="gdm-search-clear" style="display: none;"><?php esc_html_e( 'Clear Search', 'google-drive-embedder' ); ?></a>
				</div>

				<div id="gdm-file-browser-area"></div>

				<div id="gdm-linktypes-div" class="gdm-group">
					<div>
						<span class="gdm-linktypes-span">
							<input type="radio" name="gdm-linktypes" id="gdm-linktype-normal"/>
							<label for="gdm-linktype-normal"><?php esc_html_e( 'Viewer file link', 'google-drive-embedder' ); ?></label>
						</span>
						<span id="gdm-linktype-normal-options" class="gdm-linktype-options">
							<input type="checkbox" id="gdm-linktype-normal-plain" checked="checked"/>
							<label for="gdm-linktype-normal-plain"><?php esc_html_e( 'Show icon', 'google-drive-embedder' ); ?></label>

							&nbsp; &nbsp; &nbsp; &nbsp;
							<input type="checkbox" id="gdm-linktype-normal-window" checked="checked"/>
							<label for="gdm-linktype-normal-window"><?php esc_html_e( 'Open in new window', 'google-drive-embedder' ); ?></label>

							&nbsp; &nbsp;
							<a href="#" id="gdm-linktype-normal-more" style="display: none;" class="gdm-linktype-more"><?php esc_html_e( 'Options...', 'google-drive-embedder' ); ?></a>
						</span>
					</div>

					<div class="gdm-downloadable-only">
						<span class="gdm-linktypes-span">
							<input type="radio" name="gdm-linktypes" id="gdm-linktype-download"/>
							<label for="gdm-linktype-download">
								<?php esc_html_e( 'Download file link', 'google-drive-embedder' ); ?>
								<span id="gdm-linktype-download-reasons"></span>
							</label>
						</span>
						<span id="gdm-linktype-download-options" class="gdm-linktype-options">
							<select name="gdm-linktype-download-type" id="gdm-linktype-download-type"></select>
							&nbsp; &nbsp;
							<input type="checkbox" id="gdm-linktype-download-plain" checked="checked"/>
							<label for="gdm-linktype-download-plain"><?php esc_html_e( 'Show icon', 'google-drive-embedder' ); ?></label>
						</span>
					</div>

					<div class="gdm-embeddable-only">
						<span class="gdm-linktypes-span">
							<input type="radio" name="gdm-linktypes" id="gdm-linktype-embed" checked="checked"/>
							<label for="gdm-linktype-embed">
								<?php esc_html_e( 'Embed document', 'google-drive-embedder' ); ?>
								<span id="gdm-linktype-embed-reasons"></span>
							</label>
						</span>

						<span id="gdm-linktype-embed-options" class="gdm-linktype-options">
							<label for="gdm-linktype-embed-width">
								<?php esc_html_e( 'Width', 'google-drive-embedder' ); ?></label>
								<input type="text" id="gdm-linktype-embed-width" size="7" value="100%"/>
							&nbsp; &nbsp;
							<label for="gdm-linktype-embed-height"><?php esc_html_e( 'Height', 'google-drive-embedder' ); ?></label>
							<input type="text" id="gdm-linktype-embed-height" size="7" value="400"/>
							&nbsp; &nbsp;
							<a href="#" id="gdm-linktype-embed-more" style="display: none;" class="gdm-linktype-more"><?php esc_html_e( 'Options...', 'google-drive-embedder' ); ?></a>
						</span>
					</div>

					<!-- START Template for Simple Browser -->
					<div id="gdm-simple-browser-template-html" style="display: none;">
						<div class="gdm-thinking gdm-browsebox">
							<div class="gdm-thinking-text"><?php esc_html_e( 'Loading...', 'google-drive-embedder' ); ?></div>
						</div>

						<div class="gdm-authbtn gdm-browsebox" style="display: none;">
							<div>
								<a href="#" class="gdm-start-browse2"><?php esc_html_e( 'Click to authenticate via Google', 'google-drive-embedder' ); ?></a>
							</div>
						</div>

						<div class="gdm-filelist gdm-browsebox" style="display: none;"></div>
						<div class="gdm-nextprev-div gdm-group">
							<a href="#" class="gdm-prev-link" style="display: none;"><?php esc_html_e( 'Previous', 'google-drive-embedder' ); ?></a>
							<a href="#" class="gdm-next-link" style="display: none;"><?php esc_html_e( 'Next', 'google-drive-embedder' ); ?></a>
						</div>
					</div>
					<!-- END Template for Simple Browser -->

					<!-- START Template for Premium Browser -->
					<div id="gdm-premium-browser-template-html" style="display: none;">
						<div class="gdm-thinking gdm-browsebox">
							<div class="gdm-thinking-text"><?php esc_html_e( 'Loading...', 'google-drive-embedder' ); ?></div>
						</div>

						<div class="gdm-authbtn gdm-browsebox" style="display: none;">
							<div>
								<a href="#" class="gdm-start-browse2"><?php esc_html_e( 'Click to authenticate via Google', 'google-drive-embedder' ); ?></a>
							</div>
						</div>
					</div>
					<!-- END Template for Premium Browser -->
				</div>

				<?php $this->admin_footer_extra(); ?>

				<p class="submit">
					<script>
						if ( typeof enable_append_btn === 'function' ) {
							//alert('loaded');
						} else {
							//alert("not loaded");
							function enable_append_btn() {
							}

							function close_insert_popup() {
								tb_remove();
							}
						}
					</script>

					<input type="button" id="gdm-insert-drivefile" class="button-primary" onclick="enable_append_btn();" disabled="disabled"
						   value="<?php esc_attr_e( 'Insert File', 'google-drive-embedder' ); ?>" />
					<!-- <a id="gdm-cancel-drivefile-insert" class="button-secondary" onclick="tb_remove();" title="Cancel">Cancel</a> -->
					<a id="gdm-cancel-drivefile-insert" class="button-secondary" onclick="close_insert_popup();">
						<?php esc_html_e( 'Cancel', 'google-drive-embedder' ); ?>
					</a>
					<!-- hidden field for the new test -->
					<input type="hidden" id="btn_classname_gde_editor" value=""/>
					<span id="gdm-ack-owner-editor" style="display: none;">
						<input type="checkbox" id="gdm-ack-owner-editor-checkbox" class="gdm-ack-owner-editor"/>
						<label for="gdm-ack-owner-editor-checkbox"><?php esc_html_e( 'I acknowledge that I will be demoted from owner to editor', 'google-drive-embedder' ); ?></label>
					</span>
				</p>
			</div>
		</div>
		<?php
	}

	protected function allow_non_iframe_folders() {
		return false;
	}

	// Extended in premium
	protected function admin_footer_extra() {
	}

	public function gdm_admin_downloads_icon() {

		$images_url = $this->my_plugin_url() . 'images/';
		$icon_url   = $images_url . 'gdm-media.png';
		?>
		<style media="screen">
			#gdm-media-button {
				background: url('<?php echo esc_url( $icon_url ); ?>') no-repeat;
				background-size: 20px 20px;
			}
		</style>
		<?php
	}

	// SHORTCODES

	public function gdm_shortcode_display_drivefile( $atts, $content = null ) {

		if ( ! isset( $atts['url'] ) ) {
			return '<strong>' . esc_html__( 'Google Drive Embedder requires an URL attribute', 'google-drive-embedder' ) . '</strong>';
		}

		$allowed_tags           = wp_kses_allowed_html( 'post' );
		$allowed_tags['iframe'] = [
			'align'        => true,
			'width'        => true,
			'height'       => true,
			'frameborder'  => true,
			'name'         => true,
			'src'          => true,
			'id'           => true,
			'class'        => true,
			'style'        => true,
			'scrolling'    => true,
			'marginwidth'  => true,
			'marginheight' => true,
		];

		$url   = esc_url_raw( $atts['url'] );
		$title = isset( $atts['title'] ) ? esc_attr( $atts['title'] ) : esc_url( $url );

		$linkstyle = isset( $atts['style'] ) && in_array( $atts['style'], [ 'normal', 'plain', 'download', 'embed', ], true ) ? esc_attr( $atts['style'] ) : 'normal';
		$extra     = isset( $atts['extra'] ) ? esc_attr( $atts['extra'] ) : '';

		$returnhtml = '';
		switch ( $linkstyle ) {
			case 'normal':
			case 'download':
			case 'plain':
				$imghtml = '';
				if ( isset( $atts['icon'] ) ) {
					$imghtml = '<img src="' . esc_url( $atts['icon'] ) . '" width="16" height="16" />';
				}
				$newwindow = isset( $atts['newwindow'] ) && 'yes' === $atts['newwindow'] ? ' target="_blank"' : '';
				$ahref     = '<a href="' . esc_url( $url ) . '"' . esc_attr( $newwindow ) . '>' . esc_html( $title ) . '</a>';

				if ( ( isset( $atts['plain'] ) && 'yes' === $atts['plain'] ) || 'plan' === $linkstyle ) {
					$returnhtml = wp_kses_post( $ahref );
				} else {
					$returnhtml = '<p><span class="gdm-drivefile-embed">' . wp_kses_post( $imghtml ) . ' ' . wp_kses_post( $ahref ) . '</span></p>';
				}
				break;

			case 'embed':
				$width           = isset( $atts['width'] ) ? esc_attr( $atts['width'] ) : '100%';
				$height          = isset( $atts['height'] ) ? esc_attr( $atts['height'] ) : '400';
				$scrolling       = isset( $atts['scrolling'] ) && esc_attr( strtolower( $atts['scrolling'] ) ) === 'no' ? 'no' : 'yes';
				$allowfullscreen = isset( $atts['allowfullscreen'] ) && strtolower( $atts['allowfullscreen'] ) === 'no' ? '' : 'allowfullscreen';

				if ( $extra === 'image' ) {
					$get_id_from_url = static function( $url ) {
						$q = wp_parse_url( $url);

						if ( ! isset( $q['query'] ) ) {
							return '';
						}

						wp_parse_str( $q['query'], $result );

						return $result['id'] ?? '';
					};

					$file_id = $get_id_from_url( $url );

					$url = 'https://drive.google.com/file/d/'. esc_attr( $file_id ) .'/preview';
				}

				$returnhtml = '<iframe frameborder="0" ' . esc_attr( $allowfullscreen ) . '
					src="' . esc_url( $url ) . '"
					width="' . esc_attr( $width ) . '"
					height="' . esc_attr( $height ) . '"
					style="border:0;max-width:100%"
					scrolling="' . esc_attr( $scrolling ) . '"></iframe>';

				break;
		}

		if ( ! is_null( $content ) ) {
			$returnhtml .= do_shortcode( $content );
		}

		return wp_kses( $returnhtml, $allowed_tags );
	}

	// ADMIN OPTIONS
	// *************
	protected function get_options_menuname() {
		return 'gdm_list_options';
	}

	protected function get_options_pagename() {
		return 'gdm_options';
	}

	protected function get_settings_url() {

		return is_multisite()
			? network_admin_url( 'settings.php?page=' . $this->get_options_menuname() )
			: admin_url( 'options-general.php?page=' . $this->get_options_menuname() );
	}

	public function gdm_admin_menu() {

		if ( is_multisite() ) {
			add_submenu_page(
				'settings.php',
				esc_html__( 'Google Drive Embedder', 'google-drive-embedder' ),
				esc_html__( 'Google Drive Embedder', 'google-drive-embedder' ),
				'manage_network_options',
				$this->get_options_menuname(),
				[ $this, 'gdm_options_do_page' ]
			);
		} else {
			add_options_page(
				esc_html__( 'Google Drive Embedder', 'google-drive-embedder' ),
				esc_html__( 'Google Drive Embedder', 'google-drive-embedder' ),
				'manage_options',
				$this->get_options_menuname(),
				[ $this, 'gdm_options_do_page' ]
			);
		}
	}

	public function gdm_options_do_page() {

		$submit_page = is_multisite() ? 'edit.php?action=' . $this->get_options_menuname() : 'options.php';

		if ( is_multisite() ) {
			$this->gdm_options_do_network_errors();
		}
		?>

		<div>

			<h1><?php esc_html_e( 'Google Drive Embedder', 'google-drive-embedder' ); ?></h1>

			<?php $this->output_instructions_button(); ?>

			<div id="gdm-tablewrapper">

				<?php $this->draw_admin_settings_tabs(); ?>

				<form action="<?php echo esc_attr( $submit_page ); ?>" method="post" id="gdm_form">

					<?php
					settings_fields( $this->get_options_pagename() );

					$this->gdm_extrasection_text();

					$this->gdm_mainsection_text();

					$this->gdm_options_submit();
					?>

				</form>
			</div>

		</div>
		<?php
	}

	// Override in Enterprise
	protected function output_instructions_button() {
	}

	// Override in professional
	protected function draw_admin_settings_tabs() {
	}

	protected function gdm_options_submit() {
		?>
		<p class="submit">
			<input type="submit" value="<?php esc_html_e( 'Save Changes', 'google-drive-embedder' ); ?>" class="button button-primary" id="submit" name="submit">
		</p>
		<?php
	}

	// Extended in basic and premium
	protected function gdm_mainsection_text() {
	}

	protected function gdm_extrasection_text() {
	}

	public function gdm_options_validate( $input ) {

		$newinput                = [];
		$newinput['gdm_version'] = sanitize_text_field( $this->plugin_version );

		return $newinput;
	}

	protected function get_error_string( $fielderror ) {

		return esc_html__( 'Unspecified error', 'google-drive-embedder' );
	}

	public function gdm_save_network_options() {

		check_admin_referer( $this->get_options_pagename() . '-options' );

		if ( isset( $_POST[ $this->get_options_name() ] ) && is_array( $_POST[ $this->get_options_name() ] ) ) {

			$outoptions = $this->gdm_options_validate( wp_unslash( $_POST[ $this->get_options_name() ] ) );// WPCS:XSS ok array sanitized when set in gdm_options_validate

			$error_code    = [];
			$error_setting = [];
			foreach ( get_settings_errors() as $e ) {
				if ( is_array( $e ) && isset( $e['code'] ) && isset( $e['setting'] ) ) {
					$error_code[]    = $e['code'];
					$error_setting[] = $e['setting'];
				}
			}

			update_site_option( $this->get_options_name(), $outoptions );

			// redirect to settings page in network
			wp_redirect(
				add_query_arg(
					[
						'page'          => $this->get_options_menuname(),
						'updated'       => true,
						'error_setting' => $error_setting,
						'error_code'    => $error_code,
					],
					network_admin_url( 'admin.php' )
				)
			);
			exit;
		}
	}

	protected function gdm_options_do_network_errors() {

		if ( isset( $_REQUEST['updated'] ) && sanitize_text_field( wp_unslash( $_REQUEST['updated'] ) ) ) {
			?>
			<div id="setting-error-settings_updated" class="updated settings-error">
				<p>
					<strong><?php esc_html_e( 'Settings saved', 'google-drive-embedder' ); ?></strong>
				</p>
			</div>
			<?php
		}

		if (
			isset( $_REQUEST['error_setting'] ) && is_array( $_REQUEST['error_setting'] ) &&
			isset( $_REQUEST['error_code'] ) && is_array( $_REQUEST['error_code'] )
		) {
			if ( count( $_REQUEST['error_code'] ) > 0 && count( $_REQUEST['error_code'] ) === count( $_REQUEST['error_setting'] ) ) {
				$count = count( $_REQUEST['error_code'] );
				for ( $i = 0; $i < $count; ++ $i ) {
					if ( ! isset( $_REQUEST['error_setting'][ $i ] ) || ! isset( $_REQUEST['error_setting'][ $i ] ) ) {
						return;
					}
					?>
					<div id="setting-error-settings_<?php echo esc_attr( $i ); ?>" class="error settings-error">
						<p>
							<strong><?php echo esc_html( $this->get_error_string( sanitize_text_field( wp_unslash( $_REQUEST['error_setting'][ $i ] ) ) . '|' . sanitize_text_field( wp_unslash( $_REQUEST['error_setting'][ $i ] ) ) ) ); ?></strong>
						</p>
					</div>
					<?php
				}
			}
		}
	}

	// OPTIONS

	protected function get_default_options() {

		return [
			'gdm_version' => $this->plugin_version,
		];
	}

	protected $gdm_options = null;

	protected function get_option_gdm() {

		if ( null !== $this->gdm_options ) {
			return $this->gdm_options;
		}

		$option = get_site_option( $this->get_options_name(), [] );

		$default_options = $this->get_default_options();
		foreach ( $default_options as $k => $v ) {
			if ( ! isset( $option[ $k ] ) ) {
				$option[ $k ] = $v;
			}
		}

		$this->gdm_options = $option;

		return $this->gdm_options;
	}

	// ADMIN

	public function gdm_init() {

		add_shortcode( 'google-drive-embed', [ $this, 'gdm_shortcode_display_drivefile' ] );
		add_action( 'enqueue_block_assets', [ $this, 'gutenberg_enqueue_block_assets' ] );
	}

	public function gdm_admin_init() {

		register_setting( $this->get_options_pagename(), $this->get_options_name(), [ $this, 'gdm_options_validate' ] );

		// Check Google Apps Login is configured - display warnings if not.
		if ( apply_filters( 'gal_get_clientid', '' ) === '' ) {
			if ( is_multisite() ) {
				add_action( 'network_admin_notices', [ $this, 'gdm_admin_auth_message' ] );
			} else {
				add_action( 'admin_notices', [ $this, 'gdm_admin_auth_message' ] );
			}
		}

		global $pagenow;

		// If on post/page edit screen, set up Add Drive File button.
		if ( in_array( $pagenow, [ 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ], true ) ) {
			add_action( 'admin_head', [ $this, 'gdm_admin_downloads_icon' ] );
			add_action( 'media_buttons', [ $this, 'gdm_media_button' ], 11 );
			add_action( 'admin_enqueue_scripts', [ $this, 'gdm_admin_load_scripts' ] );
			add_action( 'admin_footer', [ $this, 'gdm_admin_footer' ] );

			add_action( 'enqueue_block_editor_assets', [ $this, 'gutenberg_enqueue_block_editor_assets' ] );
		}
	}

	public function gdm_admin_auth_message() {
		?>
		<div class="error">
			<p>
			<?php
				printf(
					wp_kses(
						__( '<strong>Google Drive Embedder</strong>: You need to install and configure <a href="%s" target="_blank">Google Apps Login</a> plugin to make the plugin work (Free, Premium, or Enterprise version).', 'google-drive-embedder' ),
						[
							'strong' => [],
							'a'      => [
								'href'   => [],
								'target' => [],
							],
						]
					),
					'https://wp-glogin.com/glogin/?utm_source=Admin%20Configmsg&utm_medium=freemium&utm_campaign=Drive'
				);
				?>
			</p>
		</div>
		<?php
	}

	protected function add_actions() {
		/**
		 * No longer want to request access to scopes through 'Login with Google'.
		 * Now wait until the user clicks 'Add Google File' or accesses a folder.
		 * This is best practice, and also defers any 'Invalid Scope' error further down the chain
		 * so it is clearly a 'Drive' issue.
		 * add_filter('gal_gather_scopes', Array($this, 'gdm_gather_scopes') );
		 */

		add_action( 'init', [ $this, 'gdm_init' ], 5, 0 );

		if ( is_admin() ) {
			add_action( 'admin_init', [ $this, 'gdm_admin_init' ], 5, 0 );

			add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', [ $this, 'gdm_admin_menu' ] );

			if ( is_multisite() ) {
				add_action(
					'network_admin_edit_' . $this->get_options_menuname(),
					[ $this, 'gdm_save_network_options' ]
				);
			}
		}
	}

	// Gutenberg enqueues

	public function gutenberg_enqueue_block_editor_assets() {

		wp_enqueue_script(
			'gdm-gutenberg-block-js', // Unique handle.
			$this->my_plugin_url() . 'js/gdm-blocks.js',
			[ 'wp-blocks', 'wp-i18n', 'wp-element' ], // Dependencies, defined above.
			$this->plugin_version
		);

		wp_enqueue_style(
			'gdm-gutenberg-block-css', // Handle.
			$this->my_plugin_url() . 'css/gdm-blocks.css', // editor.css: This file styles the block within the Gutenberg editor.
			[],//[ 'wp-edit-blocks' ], // Dependencies, defined above.
			$this->plugin_version
		);
	}

	public function gutenberg_enqueue_block_assets() {

		wp_enqueue_style(
			'gdm-gutenberg-block-backend-js', // Handle.
			$this->my_plugin_url() . 'css/gdm-blocks.css', // style.css: This file styles the block on the frontend.
			[],//array( 'wp-blocks' ), // Dependencies, defined above.
			$this->plugin_version
		);
	}
}

class Gdm_Drive_Exception extends Exception {
}
