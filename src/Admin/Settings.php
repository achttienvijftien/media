<?php
/**
 * This file contains the logic for Settings.
 *
 * @package AchttienVijftien\Plugin\Media\Admin
 */

namespace AchttienVijftien\Plugin\Media\Admin;

use AchttienVijftien\Plugin\Media\Config;

/**
 * Admin only functionality.
 */
class Settings {

	/**
	 * Prefix used to namespace settings.
	 */
	private const SETTINGS_PREFIX = '1815_media_setting_';

	/**
	 * Page slug for api page.
	 */
	private const GENERAL_PAGE_SLUG = '1815-media-api';

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'add_settings' ] );
		add_filter(
			'plugin_action_links_media/media.php',
			[ $this, 'plugin_settings_link' ]
		);
	}

	/**
	 * Adds plugin menu item(s).
	 */
	public function add_menu_page(): void {
		// Add api plugin settings page to options menu.
		add_options_page(
			__( 'Media (by 1815)', '1815-media' ),
			__( 'Media (by 1815)', '1815-media' ),
			'manage_options',
			self::GENERAL_PAGE_SLUG,
			[ $this, 'show_api_page' ]
		);
	}

	/**
	 * Renders HTML of api page.
	 */
	public function show_api_page(): void {
		?>
		<div class="wrap">
			<h1>
				<?php esc_html_e( 'Media (by 1815)', '1815-media' ); ?>
			</h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::SETTINGS_PREFIX . 'api' );
				do_settings_sections( self::GENERAL_PAGE_SLUG );
				?>
				<input name="submit" class="button button-primary" type="submit"
						value="<?php esc_attr_e( 'Save' ); ?>"/>
			</form>
		</div>
		<?php
	}

	/**
	 * Adds settings.
	 */
	public function add_settings(): void {
		add_settings_section(
			self::SETTINGS_PREFIX . 'api',
			__( 'API Settings', '1815-media' ),
			[
				$this,
				'api_section_text',
			],
			self::GENERAL_PAGE_SLUG
		);

		$this->add_url_setting();
	}

	/**
	 * Description of api section.
	 */
	public function api_section_text(): void {
		echo '<p>';
		esc_html_e(
			'Settings to make connection to media API.',
			'1815-media'
		);
		echo '</p>';
	}

	/**
	 * Media API url setting.
	 */
	public function add_url_setting(): void {
		register_setting(
			self::SETTINGS_PREFIX . 'api',
			Config::get_option_name( 'api_url' ),
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'https://media.1815.io/',
			]
		);

		add_settings_field(
			self::SETTINGS_PREFIX . 'api_url',
			__( 'API URL', '1815-media' ),
			[
				$this,
				'api_url_setting_field',
			],
			self::GENERAL_PAGE_SLUG,
			self::SETTINGS_PREFIX . 'api'
		);

		register_setting(
			self::SETTINGS_PREFIX . 'api',
			Config::get_option_name( 'media_url' ),
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'https://media.1815.io/',
			]
		);

		add_settings_field(
			self::SETTINGS_PREFIX . 'media_url',
			__( 'Media URL', '1815-media' ),
			[
				$this,
				'media_url_setting_field',
			],
			self::GENERAL_PAGE_SLUG,
			self::SETTINGS_PREFIX . 'api'
		);

		register_setting(
			self::SETTINGS_PREFIX . 'api',
			Config::get_option_name( 'client_id' ),
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		add_settings_field(
			self::SETTINGS_PREFIX . 'client_id',
			__( 'Client ID', '1815-media' ),
			[
				$this,
				'client_id_setting_field',
			],
			self::GENERAL_PAGE_SLUG,
			self::SETTINGS_PREFIX . 'api'
		);

		register_setting(
			self::SETTINGS_PREFIX . 'api',
			Config::get_option_name( 'client_secret' ),
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		add_settings_field(
			self::SETTINGS_PREFIX . 'client_secret',
			__( 'Client secret', '1815-media' ),
			[
				$this,
				'client_secret_setting_field',
			],
			self::GENERAL_PAGE_SLUG,
			self::SETTINGS_PREFIX . 'api'
		);
	}

	/**
	 * Form field of API url setting.
	 */
	public function api_url_setting_field(): void {
		$url = Config::get_instance()->get( 'api_url' );

		echo '<input id="' . esc_attr( self::SETTINGS_PREFIX . 'api_url' ) . '" class="regular-text"
		name="' . esc_attr( Config::get_option_name( 'api_url' ) ) . '" 
		type="text" value="' . esc_attr( $url ) . '" />';

		echo '<p class="description">';
		esc_html_e( 'Normally: https://media.1815.io/', '1815-media' );
		echo '</p>';
	}

	/**
	 * Form field of media url setting.
	 */
	public function media_url_setting_field(): void {
		$url = Config::get_instance()->get( 'media_url' );

		echo '<input id="' . esc_attr( self::SETTINGS_PREFIX . 'media_url' ) . '" class="regular-text"
		name="' . esc_attr( Config::get_option_name( 'media_url' ) ) . '" 
		type="text" value="' . esc_attr( $url ) . '" />';

		echo '<p class="description">';
		esc_html_e(
			'Normally this should be the API url with a namespace string postfixed, e.g.: https://media.1815.io/name/',
			'1815-media'
		);
		echo '</p>';
	}

	/**
	 * Form field of client_id setting.
	 */
	public function client_id_setting_field(): void {
		$client_id = Config::get_instance()->get( 'client_id' );

		echo '<input id="' . esc_attr( self::SETTINGS_PREFIX . 'client_id' ) . '" class="regular-text"
		name="' . esc_attr( Config::get_option_name( 'client_id' ) ) . '" 
		type="text" value="' . esc_attr( $client_id ) . '" />';
	}

	/**
	 * Form field of client_secret setting.
	 */
	public function client_secret_setting_field(): void {
		$client_secret = Config::get_instance()->get( 'client_secret' );

		echo '<input id="' . esc_attr( self::SETTINGS_PREFIX . 'client_secret' ) . '" class="regular-text"
		name="' . esc_attr( Config::get_option_name( 'client_secret' ) ) . '" 
		type="password" value="' . esc_attr( $client_secret ) . '" />';
	}

	/**
	 * Adds link to api settings page on plugin list page.
	 *
	 * @param array $links Links of this plugin to show on plugin list page.
	 *
	 * @return array
	 */
	public function plugin_settings_link( array $links ): array {
		// Get link to api settings page.
		$url = esc_url(
			add_query_arg(
				'page',
				self::GENERAL_PAGE_SLUG,
				get_admin_url() . 'admin.php'
			)
		);

		// Create the link.
		$links[] = '<a href="' . $url . '">' . esc_html__( 'Settings' ) . '</a>';

		return $links;
	}
}
