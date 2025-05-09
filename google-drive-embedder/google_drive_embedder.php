<?php
/**
 * Plugin Name: 	  Google Drive Embedder
 * Plugin URI: 	      https://wp-glogin.com/
 * Description: 	  Easily browse for Google Drive documents and embed directly in your posts and pages. Extends the popular Google Apps Login plugin so no extra user authentication (or admin setup) is required.
 * Requires at least: 5.5
 * Requires PHP:      7.2
 * Version: 		  5.3.0
 * Author: 			  WP Glogin Team
 * Author URI: 		  https://wp-glogin.com/
 * Text Domain: 	  google-drive-embedder
 * Domain Path:       /lang
 *
 * Google Drive Embedder is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Google Drive Embedder is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Google Drive Embedder. If not, see <https://www.gnu.org/licenses/>.
 */

if ( class_exists( 'Drive_Embedder_Core' ) ) {
	global $gdm_core_already_exists;
	$gdm_core_already_exists = true;
} else {
	require_once plugin_dir_path( __FILE__ ) . '/core/core_google_drive_embedder.php';
}

/**
 * Drive Embedder Class.
 */
class Drive_Embedder_Basic extends Drive_Embedder_Core {

	protected $plugin_version = '5.3.0';

	// Singleton.
	private static $instance = null;

	public static function get_instance() {

		if ( self::$instance === null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	// Basic specific.

	protected function get_extra_js_name() {
		return 'basic';
	}

	// ADMIN.

	protected function get_options_name() {
		return 'gdm_basic';
	}

	protected function gdm_mainsection_text() {
		?>
		<p>
			<?php esc_html_e( 'There are no settings to configure in this free version of Google Drive Embedder.', 'google-drive-embedder' ); ?></p>
		<p>

		<p>
			<?php
			printf(
				wp_kses( /* translators: %s: link to website */
					__( 'Please <a href="%s" target="_blank">visit our website</a> for more details about our premium and enterprise versions.', 'google-drive-embedder' ),
					[ 'a' => [ 'href' => [], 'target' => [] ] ]
				),
				'https://wp-glogin.com/drive/?utm_source=Drive%20Settings&utm_medium=freemium&utm_campaign=Drive'
			);
			?>
		</p>

		<h2><?php esc_html_e( 'Premium Version', 'google-drive-embedder' ); ?></h2>

		<ul style="list-style: disc inside; max-width: 800px">
			<li>
				<?php
				echo wp_kses(
					__( '<strong>My Drive:</strong> locate files to embed by searching, browsing your Drive, starred or recent files - just like on Google Drive itself.', 'google-drive-embedder' ),
					[ 'strong' => [] ]
				);
				?>
			</li>
			<li>
				<?php
				echo wp_kses(
					__( '<strong>Embed Folders:</strong> simply keep your Google Drive folder up-to-date with your files, and your staff or website visitors will always be able to view a list of the latest documents. For more advanced folder integration please take a look at the Enterprise version.', 'google-drive-embedder' ),
					[ 'strong' => [] ]
				);
				?>
			</li>
			<li>
				<?php
				echo wp_kses(
					__( '<strong>Calendars:</strong> pick from your Google Calendars and provide download links to ICAL or XML, or embed them directly in your site.', 'google-drive-embedder' ),
					[ 'strong' => [] ]
				);
				?>
			</li>
			<li>
				<?php
				echo wp_kses(
					__( '<strong>Support and updates</strong> for one year.', 'google-drive-embedder' ),
					[ 'strong' => [] ]
				);
				?>
			</li>
		</ul>

		<p>
			<a href="https://wp-glogin.com/drive/?utm_source=Drive%20Settings&utm_medium=freemium&utm_campaign=Drive&utm_content=Premium" target="_blank">
				<?php esc_html_e( 'Click here for details or purchase', 'google-drive-embedder' ); ?>
			</a>
		</p>

		<h2><?php esc_html_e( 'Enterprise Version', 'google-drive-embedder' ); ?></h2>

		<p>
			<?php esc_html_e( 'Google Drive is a versatile way to store files and share with colleagues, while your intranet is clearer and better structured for sharing more focused information. ', 'google-drive-embedder' ); ?>
			<br>
			<?php esc_html_e( 'But using both at the same time can lead to confusion about where information is stored.', 'google-drive-embedder' ); ?>
		</p>

		<p>
			<?php esc_html_e( "Wouldn't it be great if your intranet could be used to control and structure the information your organization stores in Drive?", 'google-drive-embedder' ); ?>
		</p>

		<p>
			<?php esc_html_e( 'Our Enterprise version of Google Drive Embedder integrates Drive much more closely with your WordPress intranet, essentially allowing each page or post on your intranet to host its own file attachments, completely backed by Drive.', 'google-drive-embedder' ); ?>
		</p>

		<p>
			<?php esc_html_e( 'This means you no longer need to manage Drive and your Intranet as two completely separate document sharing systems!', 'google-drive-embedder' ); ?>
		</p>

		<p>
			<?php esc_html_e( 'Drive Embedder Enterprise has all the features of the premium and basic versions - easily embed files from Google Drive into your WordPress site - plus much more advanced folder embedding. This starts with much slicker styling.', 'google-drive-embedder' ); ?>
			<br>
			<?php esc_html_e( 'Instead of embedding folders as iframes, they are built directly into your WordPress pages, meaning users can click into subfolders and preview files without leaving your website.', 'google-drive-embedder' ); ?>
		</p>

		<p>
			<?php esc_html_e( 'Supports Google Shared Drives (Team Drives).', 'google-drive-embedder' ); ?>
		</p>

		<p>
			<strong><?php esc_html_e( 'Includes support and updates for one year.', 'google-drive-embedder' ); ?></strong>
		</p>

		<p>
			<a href="https://wp-glogin.com/drive/?utm_source=Drive%20Settings%20Enterprise&utm_medium=freemium&utm_campaign=Drive&utm_content=Enterprise" target="_blank">
				<?php esc_html_e( 'Click here for details or purchase', 'google-drive-embedder' ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * Don't need a submit button here.
	 */
	protected function gdm_options_submit() {
	}

	protected function my_plugin_basename() {

		$basename = plugin_basename( __FILE__ );
		// Maybe due to symlink.
		if ( '/' . $basename === __FILE__ ) {
			$basename = basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ );
		}

		return $basename;
	}

	protected function my_plugin_url() {

		$basename = plugin_basename( __FILE__ );

		// Maybe due to symlink.
		if ( '/' . $basename === __FILE__ ) {
			return plugins_url() . '/' . basename( dirname( __FILE__ ) ) . '/';
		}

		// Normal case (non symlink).
		return plugin_dir_url( __FILE__ );
	}
}

// Global accessor function to singleton.
function google_drive_embedder_init() {
	return Drive_Embedder_Basic::get_instance();
}

// Initialise at least once.
google_drive_embedder_init();
