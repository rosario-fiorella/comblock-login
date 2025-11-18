<?php

/**
 * Comblock Login plugin.
 *
 * Manages secure and customized login on the frontend,
 * allowing controlled access to multiple dashboards based on user roles.
 *
 * @package     comblock-login
 * @author      Rosario Fiorella
 * @link        https://github.com/rosario-fiorella/comblock-login/
 * @link        https://profiles.wordpress.org/wprosario/
 * @since       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Comblock Login
 * Plugin URI:        https://github.com/rosario-fiorella/comblock-login/
 * Description:       The Comblock Login plugin manages secure and customized login on the frontend,
 *                    allowing controlled access to multiple dashboards based on user roles.
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      8.3
 * Author:            Rosario Fiorella
 * Author URI:        https://profiles.wordpress.org/wprosario
 * Text Domain:       comblock-login
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-comblock-login.php';

try {
	$comblock_login = new Comblock_Login();
	$comblock_login->run();
} catch ( Throwable $e ) {
	add_action(
		'admin_notices',
		function () use ( $e ): void {
			$html = sprintf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html( $e->getMessage() ) );
			echo wp_kses_post( $html );
		}
	);
}
