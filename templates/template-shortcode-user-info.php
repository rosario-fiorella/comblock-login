<?php

defined( 'WPINC' ) or exit;

/**
 * Renders the frontend disconnection form via shortcode.
 *
 * This shortcode allows inserting a custom form to log out from all devices
 * with configurable attributes such as ID and class.
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $attributes Shortcode attributes.
 * @return string The disconnection form HTML or error message.
 */
function comblock_user_info_shortcode_template( array $attributes = array() ): string {
	/** @var null|WP_Post $post */
	global $post;

	if ( ! is_user_logged_in() ) {
		return '';
	}

	if ( ! $post instanceof WP_Post || $post->post_type !== Comblock_Login_Dashboard::POST_TYPE_SLUG ) {
		return '';
	}

	/** @var WP_User $user */
	$user = wp_get_current_user();

	/** @var string $user_roles */
	$user_roles = implode( ', ', $user->roles );

	ob_start();
	?>
	<div class="comblock-user-info">
		<legend class="comblock-user-info__legend">{user_info_label}</legend>

		<div class="comblock-user-info__group">
			<p><span class="dashicons dashicons-admin-users"></span> {display_name}</p>
			<p><span class="dashicons dashicons-email"></span> <a href="mailto:{user_email}">{user_email}</a></p>
			<p><span class="dashicons dashicons-admin-generic"></span> {user_roles}</p>
		</div>
	</div>
	<?php

	// Get template HTML
	$template_html = ob_get_clean() ?: '';

	// Placeholder array for template substitution
	$placeholders = array(
		'{user_info_label}' => esc_html__( 'User info', 'comblock-login' ),
		'{display_name}'    => esc_html( $user->display_name ),
		'{user_email}'      => esc_html( $user->user_email ),
		'{user_roles}'      => esc_html( $user_roles ),
	);

	// Replace placeholders with actual escaped values
	$template_html = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $template_html );

	// Allowed HTML tags and attributes for output sanitization
	$allowed_html = array(
		'legend' => array( 'class' => true ),
		'div'    => array( 'class' => true ),
		'a'      => array(
			'href'  => true,
			'class' => true,
		),
		'p'      => array(),
		'span'   => array( 'class' => true ),
	);

	return wp_kses( $template_html, $allowed_html );
}
