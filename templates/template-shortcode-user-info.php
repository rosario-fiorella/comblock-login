<?php

defined( 'WPINC' ) || exit;

/**
 * Renders the frontend user info form via shortcode.
 *
 * This shortcode allows inserting a custom form to display user info
 * with configurable attributes such as title and fields.
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $attributes Shortcode attributes.
 * @return string The user info form HTML or an empty string on failure.
 */
function comblock_user_info_shortcode_template( array $attributes = array() ): string {
	try {
		$user_info_manager = new Comblock_Login_Shortcode_User( $attributes );
		return $user_info_manager->render();
	} catch ( Throwable $e ) {
		Comblock_Login_Logger::log( $e->getMessage() );
		return '';
	}
}
