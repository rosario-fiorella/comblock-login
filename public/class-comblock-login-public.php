<?php
/**
 * Handles the public-facing styles and scripts for the Comblock Login plugin.
 *
 * This class is responsible for enqueueing the CSS and JS files
 * required on the frontend of the WordPress site.
 *
 * @since 1.0.0
 * @package comblock-login
 * @subpackage comblock-login/public
 */
class Comblock_Login_Public {
	/**
	 * Enqueue stylesheet for public side.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_styles(): void {
		wp_enqueue_style(
			Comblock_Login::DOMAIN,
			plugin_dir_url( __FILE__ ) . 'css/comblock-login-public.css',
			array(),
			Comblock_Login::VERSION,
			'all'
		);
	}

	/**
	 * Enqueue scripts for public side.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		wp_enqueue_script(
			Comblock_Login::DOMAIN,
			plugin_dir_url( __FILE__ ) . 'js/comblock-login-public.js',
			array( 'jquery' ),
			Comblock_Login::VERSION,
			false
		);
	}
}
