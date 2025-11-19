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
function comblock_user_info_shortcode_template( array $attributes = array() ): string {
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
		'title'  => esc_html__( 'User info', 'comblock-login' ),
		'fields' => 'user_email,display_name',
	);

	// Merge and sanitize attributes.
	$atts = shortcode_atts( $defaults, $attributes, 'comblock_user_info' );

	/** * @var array<int, string> $fields */
	$fields = explode( ',', $atts['fields'] );
	if ( empty( $fields ) ) {
		$fields = array();
	}

	/** * @var WP_User $user */
	$user = wp_get_current_user();

	/** * @var string $user_roles */
	$user_roles = implode( ', ', $user->roles );

	/** * @var string $html_fields */
	$html_fields = '';
	foreach ( $fields as $field ) {
		// Sanitize field name.
		$field = sanitize_key( trim( $field ) );

		// Retrieve user property or meta.
		$value = $user->has_prop( $field ) ? $user->get( $field ) : get_user_meta( $user->ID, $field, true );
		$value = is_array( $value ) ? implode( ', ', $value ) : $value;
		$value = esc_html( $value ? $value : '' );

		// CSS class for the field.
		$css_class = 'user-info field-' . esc_attr( $field );

		// Format output based on field type.
		if ( 'display_name' === $field ) {
			$html_fields .= sprintf( '<p class="%s"><span class="dashicons dashicons-admin-users"></span> %s</p>', $css_class, $value );
		} elseif ( 'user_email' === $field ) {
			$html_fields .= sprintf( '<p class="%s"><span class="dashicons dashicons-email"></span> <a href="mailto:%2$s">%2$s</a></p>', $css_class, sanitize_email( $value ) );
		} elseif ( 'user_roles' === $field ) {
			$html_fields .= sprintf( '<p class="%s"><span class="dashicons dashicons-admin-generic"></span> %s</p>', $css_class, $user_roles );
		} else {
			$html_fields .= sprintf( '<p class="%s"><span class="dashicons dashicons-info"></span> %s: %s</p>', $css_class, esc_html( ucwords( str_replace( '_', ' ', $field ) ) ), $value );
		}
	}

	// Build the final HTML output.
	$template_html  = '';
	$template_html .= '<div class="comblock-user-info">';
	$template_html .= sprintf( '<legend class="comblock-user-info__legend">%s</legend>', esc_html( $atts['title'] ) );
	$template_html .= sprintf( '<div class="comblock-user-info__group">%s</div>', $html_fields );
	$template_html .= '</div>';

	// Allowed HTML tags and attributes for output sanitization.
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
