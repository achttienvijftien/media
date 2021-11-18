<?php
/**
 * This file contains the logic for Bootstrap.
 *
 * @package AchttienVijftien\Plugin\Media
 */

namespace AchttienVijftien\Plugin\Media;

use AchttienVijftien\Plugin\Media\Admin\Settings;

/**
 * Admin only functionality.
 *
 * @package AchttienVijftien\Plugin\Media
 */
class Admin {
	/**
	 * Admin constructor.
	 */
	public function __construct() {
		new Settings();
	}
}
