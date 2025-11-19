<?php

defined( 'WPINC' ) || exit;

/**
 * Class Comblock_Login_User_Shortcode
 *
 * Handles rendering user information via shortcode.
 *
 * @since 1.0.0
 * @package comblock-login
 * @subpackage comblock-login/includes
 */
class Comblock_Login_Shortcode_User {

	/**
	 * WordPress database global object.
	 *
	 * @access protected
	 * @var wpdb $wpdb
	 */
	protected wpdb $wpdb;

	/**
	 * Current post object.
	 *
	 * @access protected
	 * @var WP_Post $post
	 */
	protected WP_Post $post;

	/**
	 * Current user object.
	 *
	 * @access protected
	 * @var WP_User $user
	 */
	protected WP_User $user;

	/**
	 * Title for the user info display.
	 *
	 * @access protected
	 * @var string $title
	 */
	protected string $title = '';

	/**
	 * List of user meta keys to display.
	 *
	 * @access protected
	 * @var string[] $usermeta_keys
	 */
	protected array $usermeta_keys = array();

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $attributes Shortcode attributes.
	 *
	 * @throws RuntimeException If required context or dependencies are missing.
	 */
	public function __construct( array $attributes ) {
		$this->set_wpdb();
		$this->set_current_post();
		$this->set_current_user();
		$this->set_attributes( $attributes );
	}

	/**
	 * Sets the global $wpdb object.
	 *
	 * @access protected
	 * @return void
	 * @throws RuntimeException If $wpdb is not properly initialized.
	 */
	protected function set_wpdb(): void {
		/** * @global wpdb $wpdb */
		global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
			throw new RuntimeException( __('Global "wpdb" not initialized.', 'comblock-login') );
		}

		$this->wpdb = $wpdb;
	}

	/**
	 * Sets the current post.
	 *
	 * @access protected
	 * @return void
	 * @throws RuntimeException If $post is invalid or not the required post type.
	 */
	protected function set_current_post(): void {
		/** * @var null|WP_Post $post */
		global $post;

		if ( ! $post || ! $post instanceof WP_Post || Comblock_Login_Dashboard::POST_TYPE_SLUG !== $post->post_type ) {
			throw new RuntimeException( __('Invalid post context.', 'comblock-login') );
		}

		$this->post = $post;
	}

	/**
	 * Sets the current logged in user.
	 *
	 * @access protected
	 * @return void
	 * @throws RuntimeException If no user is logged in.
	 */
	protected function set_current_user(): void {
		if ( ! is_user_logged_in() ) {
			throw new RuntimeException( __('User not logged in.', 'comblock-login') );
		}

		$this->user = wp_get_current_user();
	}

	/**
	 * Sets shortcode attributes.
	 *
	 * @access protected
	 * @param array<string, mixed> $attributes Shortcode attributes.
	 * @return void
	 */
	protected function set_attributes( array $attributes ): void {
		// Define default attributes.
		$defaults = array(
			'title'  => esc_html__( 'User info', 'comblock-login' ),
			'fields' => 'user_email,display_name',
		);

		$atts = shortcode_atts( $defaults, $attributes, 'comblock_user_info' );

		// Process fields attribute into an array of sanitized keys.
		$this->usermeta_keys = array_map( 'sanitize_key', array_map( 'trim', (array) explode( ',', $atts['fields'] ) ) );

		// Set title user info section.
		$this->title = esc_html( $atts['title'] );
	}

	/**
	 * Returns a list of forbidden user meta keys.
	 *
	 * @return string[] Forbidden meta keys.
	 */
	public function get_forbidden_usermeta_key(): array {
		// Get the blog prefix.
		$prefix = $this->wpdb->get_blog_prefix();

		// Default forbidden fields.
		$forbidden_fields = array(
			'user_pass',
			'default_password_nag',
			'_new_email',
			'user_activation_key',
			'user_nicename',
			'nickname',
			'rich_editing',
			'syntax_highlighting',
			'comment_shortcuts',
			'admin_color',
			'use_ssl',
			'show_admin_bar_front',
			'dismissed_wp_pointers',
			'show_welcome_panel',
			'session_tokens',
			'community-events-location',
			'closedpostboxes_dashboard',
			'metaboxhidden_dashboard',
			'primary_blog',
			'source_domain',
			'community-events-location',
			WP_Application_Passwords::USERMETA_KEY_APPLICATION_PASSWORDS,
			$prefix . 'capabilities',
			$prefix . 'user_level',
			$prefix . 'dashboard_quick_press_last_post_id',
			$prefix . 'persisted_preferences',
		);

		/**
		 * Filter to add additional forbidden fields.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $add_forbidden_fields Additional forbidden meta keys.
		 */
		$add_forbidden_fields = apply_filters( 'comblock_login_user_ban_fields', array() );

		$add_forbidden_fields = array_unique( array_merge( $forbidden_fields, $add_forbidden_fields ) );

		return array_filter( $add_forbidden_fields );
	}

	/**
	 * Returns a list of allowed user meta keys.
	 *
	 * @return string[] Allowed meta keys.
	 */
	public function get_allowed_usermeta_key(): array {
		// Default allowed fields.
		$allowed_fields = array(
			'user_description',
			'user_email',
			'user_firstname',
			'user_lastname',
			'user_registered',
			'user_url',
			'description',
			'display_name',
			'first_name',
			'last_name',
		);

		/**
		 * Filter to modify allowed user info fields.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $allowed_fields Allowed meta keys.
		 */
		$allowed_fields = apply_filters( 'comblock_login_user_info_allowed_fields', $allowed_fields );

		if ( ! is_array( $allowed_fields ) ) {
			$allowed_fields = array();
		}

		return $allowed_fields;
	}

	/**
	 * Renders the user info HTML.
	 *
	 * @return string User info HTML output.
	 */
	public function render(): string {
		// Prepare user roles as a comma-separated string.
		$user_roles = implode( ', ', $this->user->roles );

		// Get allowed and forbidden user meta keys.
		$allowed_usermeta_key = $this->get_allowed_usermeta_key();

		// Get forbidden user meta keys.
		$forbidden_usermeta_key = $this->get_forbidden_usermeta_key();

		// Build HTML fields.
		$html_fields = '';
		foreach ( $this->usermeta_keys as $meta_key ) {
			if ( in_array( $meta_key, $forbidden_usermeta_key, true ) || ! in_array( $meta_key, $allowed_usermeta_key, true ) ) {
				continue;
			}

			$value = $this->user->has_prop( $meta_key ) ? $this->user->get( $meta_key ) : get_user_meta( $this->user->ID, $meta_key, true );
			if ( ! $value || is_serialized( $value, true ) || is_serialized_string( $value ) ) {
				continue;
			}

			// Prepare CSS class for the field.
			$esc_css = sprintf( 'user-info field-%s', esc_attr( $meta_key ) );

			// Sanitize and format the value based on the meta key.
			$value = sanitize_user_field( $meta_key, $value, $this->user->ID, 'display' );

			if ( 'display_name' === $meta_key ) {
				$html_fields .= sprintf( '<p class="%s"><span class="dashicons dashicons-admin-users"></span> %s</p>', $esc_css, esc_html( $value ) );
			} elseif ( 'user_email' === $meta_key ) {
				$html_fields .= sprintf( '<p class="%s"><span class="dashicons dashicons-email"></span> <a href="mailto:%2$s">%2$s</a></p>', $esc_css, sanitize_email( $value ) );
			} elseif ( 'user_roles' === $meta_key ) {
				$html_fields .= sprintf( '<p class="%s"><span class="dashicons dashicons-admin-generic"></span> %s</p>', $esc_css, esc_html( $user_roles ) );
			} elseif ( 'user_url' === $meta_key ) {
				$html_fields .= sprintf( '<p class="%s"><span class="dashicons dashicons-admin-site"></span> <a href="%2$s" target="_blank" rel="noopener noreferrer">%2$s</a></p>', $esc_css, esc_url( $value ) );
			} else {
				$label        = esc_html( ucwords( str_replace( '_', ' ', $meta_key ) ) );
				$html_fields .= sprintf( '<p class="%s"><span class="dashicons dashicons-info"></span> %s: %s</p>', $esc_css, $label, esc_html( $value ) );
			}
		}

		// Build the final template HTML.
		$template_html  = '';
		$template_html .= '<div class="comblock-user-info">';
		if ( ! empty( $this->title ) ) {
			$template_html .= sprintf( '<legend class="comblock-user-info__legend">%s</legend>', $this->title );
		}
		$template_html .= sprintf( '<div class="comblock-user-info__group">%s</div>', $html_fields );
		$template_html .= '</div>';

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
}
