<?php

/**
 * @link              https://github.com/rosario-fiorella/wordpress-comblock-login/
 * @since             1.0.0
 * @package           wordpress-comblock-login
 * @author            Rosario Fiorella
 *
 * @wordpress-plugin
 * Plugin Name:       Comblock Front-End Login
 * Plugin URI:        https://github.com/rosario-fiorella/wordpress-comblock-login/
 * Description:       WordPress plugin that allows you to authenticate users in the front-end in a reserved area.
 * Version:           1.0.0
 * Requires at least: 6.8.3
 * Requires PHP:      8.3
 * Author:            Rosario Fiorella
 * Author URI:        https://github.com/rosario-fiorella/
 * Text Domain:       comblock-login
 * Domain Path:       /languages
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 */

if (!defined('WPINC')) {
    die;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-comblock-login.php';

try {
    $comblock_login = new Comblock_Login();
    $comblock_login->run();
} catch (Throwable $e) {
    add_action('admin_notices', function () use ($e): void {
        $html = sprintf('<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html($e->getMessage()));
        echo wp_kses_post($html);
    });
}
