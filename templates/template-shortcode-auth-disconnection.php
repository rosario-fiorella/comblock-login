<?php

defined( 'WPINC' ) || exit;

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
function comblock_disconnection_shortcode_template( array $attributes = array() ): string {
	/** * @var null|WP_Post $post */
	global $post;

	if ( ! is_user_logged_in() ) {
		return '';
	}

	if ( ! $post instanceof WP_Post || Comblock_Login_Dashboard::POST_TYPE_SLUG !== $post->post_type ) {
		return '';
	}

	// Default attributes.
	$defaults = array(
		'id'    => '',
		'class' => '',
	);

	// Merge and sanitize attributes.
	$atts = shortcode_atts( $defaults, $attributes, 'user_info' );

	// Sanitize inputs.
	$id    = sanitize_key( $atts['id'] );
	$class = sanitize_html_class( $atts['class'] );

	ob_start();
	?>
	<form id="{form_id}" method="{form_method}" class="comblock-disconnection {form_class}" action="{form_action}" novalidate="novalidate">
		<fieldset class="comblock-disconnection__fieldset">
			<div class="comblock-disconnection__hidden">
				{nonce} <input type="hidden" name="action" value="logout_all_devices" />
			</div>
			<div class="comblock-disconnection__actions">
				<button type="submit" class="comblock-disconnection__button comblock-disconnection__button--submit">
					<span class="dashicons dashicons-editor-break"></span> {submit_label}
				</button>
			</div>
		</fieldset>
	</form>
	<?php

	// Get template HTML.
	$template_html = ob_get_clean();
	if ( false === $template_html ) {
		$template_html = '';
	}

	// Placeholder array for template substitution.
	$placeholders = array(
		'{form_id}'      => esc_attr( $id ),
		'{form_class}'   => esc_attr( $class ),
		'{form_method}'  => 'post',
		'{form_action}'  => esc_url( admin_url( 'admin-post.php' ) ),
		'{nonce}'        => wp_nonce_field( 'logout_all_devices_action', 'logout_all_devices_nonce', true, false ),
		'{submit_label}' => esc_html__( 'Log out of all devices', 'comblock-login' ),
	);

	// Replace placeholders with actual escaped values.
	$template_html = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $template_html );

	// Allowed HTML tags and attributes for output sanitization.
	$allowed_html = array(
		'form'     => array(
			'id'         => true,
			'action'     => true,
			'method'     => true,
			'class'      => true,
			'novalidate' => true,
		),
		'fieldset' => array( 'class' => true ),
		'div'      => array( 'class' => true ),
		'button'   => array(
			'type'  => true,
			'class' => true,
		),
		'input'    => array(
			'type'     => true,
			'id'       => true,
			'name'     => true,
			'value'    => true,
			'required' => true,
			'class'    => true,
		),
		'span'     => array( 'class' => true ),
	);

	return wp_kses( $template_html, $allowed_html );
}
