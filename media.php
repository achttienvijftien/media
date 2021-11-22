<?php
/**
 * Plugin Name:     1815 - Media
 * Plugin URI:      https://1815.nl
 * Description:     Media plugin by 1815
 * Version:         0.1.3
 * Author:          1815 <it@1815.nl>
 * Author URI:      https://1815.nl
 * Text Domain:     1815-media
 * Domain Path:     /languages
 * License:         GPL-3.0+
 * License URI:     https://1815.nl
 *
 * @package         AchttienVijftien\Plugin\Media
 */

if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

\AchttienVijftien\Plugin\Media\Bootstrap::get_instance()->init();
