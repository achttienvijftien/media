<?php
/**
 * This file contains the logic for Bootstrap.
 *
 * @package AchttienVijftien\Plugin\Media
 */

namespace AchttienVijftien\Plugin\Media;

use AchttienVijftien\Plugin\Media\Compatibility\GravityForms;

/**
 * Bootstrap plugin.
 */
class Bootstrap {

	/**
	 * Instance.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Get (singleton) instance.
	 *
	 * @return $this
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize plugin.
	 */
	public function init(): void {
		new Upload();
		new Loading();

		// third party compatibility.
		new GravityForms();

		if ( is_admin() ) {
			$this->init_admin();
		}
	}

	/**
	 * Initialize admin.
	 */
	public function init_admin(): void {
		new Admin();
	}
}
