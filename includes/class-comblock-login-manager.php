<?php

/**
 * Class Comblock_Login_Manager
 *
 * This class manages the login functionality for the Comblock system.
 * Add a brief description of what this class does and its main responsibilities.
 *
 * @since 1.0.0
 * @package comblock-login
 * @subpackage comblock-login/includes
 */
class Comblock_Login_Manager {

	/**
	 * Register the plugin shortcode with WordPress to manage authenticated access to the front office dashboard.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_shortcode(): void {
		$this->register_login_shortcode();
		$this->register_logout_shortcode();
		$this->register_disconnection_shortcode();
		$this->register_user_info_shortcode();
	}

	/**
	 * Registers the [comblock_login] shortcode with WordPress.
	 *
	 * The shortcode callback handles rendering the login form.
	 *
	 * @since 1.0.0
	 *
	 * @example [comblock_login id="subscriber-login" class="comblock-form__login" dashboard-post-id="1" privacy-page-id="2" action="subscriber/dashboard"]
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function register_login_shortcode(): void {
		/**
		 * Callback function for the [comblock_login] shortcode.
		 *
		 * @since 1.0.0
		 *
		 * @var callback<array<string, scalar>> $callback
		 * @return string
		 */
		$callback = function ( array $attributes = array() ): string {
			if ( isset( $_SERVER['REQUEST_METHOD'] ) && sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) === 'post' ) {
				$this->do_login();
			}

			return comblock_login_shortcode_template( $attributes );
		};

		add_shortcode( 'comblock_login', $callback );
	}

	/**
	 * Registers the [comblock_logout] shortcode with WordPress.

	 * The shortcode callback handles rendering the logout link.
	 *
	 * @since 1.0.0
	 *
	 * @example [comblock_logout id="subscriber-logout" class="comblock-form__logout"]
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function register_logout_shortcode(): void {
		/**
		 * Callback function for the [comblock_logout] shortcode.
		 *
		 * @since 1.0.0
		 *
		 * @var callback<array<string, scalar>> $callback
		 * @return string
		 */
		$callback = function ( array $attributes = array() ): string {
			if ( isset( $_SERVER['REQUEST_METHOD'] ) && sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) === 'post' ) {
				$this->do_logout();
			}

			return comblock_logout_shortcode_template( $attributes );
		};

		add_shortcode( 'comblock_logout', $callback );
	}

	/**
	 * Registers the [comblock_user_info] shortcode with WordPress.
	 *
	 * The shortcode callback handles rendering user information and logout from all devices.
	 *
	 * @since 1.0.0
	 *
	 * @example [comblock_user_info]
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function register_user_info_shortcode(): void {
		/**
		 * Callback function for the [comblock_user_info] shortcode.
		 *
		 * @since 1.0.0
		 *
		 * @var callback<array<string, scalar>> $callback
		 * @return string
		 */
		$callback = function ( array $attributes = array() ): string {
			return comblock_user_info_shortcode_template( $attributes );
		};

		add_shortcode( 'comblock_user_info', $callback );
	}

	/**
	 * Registers the [comblock_disconnection] shortcode with WordPress.
	 *
	 * The shortcode callback handles rendering the disconnection link.
	 *
	 * @since 1.0.0
	 *
	 * @example [comblock_disconnection id="subscriber-disconnection" class="comblock-form__disconnection"]
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function register_disconnection_shortcode(): void {
		/**
		 * Callback function for the [comblock_disconnection] shortcode.
		 *
		 * @since 1.0.0
		 *
		 * @var callback<array<string, scalar>> $callback
		 * @return string
		 */
		$callback = function ( array $attributes = array() ): string {
			return comblock_disconnection_shortcode_template( $attributes );
		};

		add_shortcode( 'comblock_disconnection', $callback );
	}

	/**
	 * Handles user login for the plugin via form submission.
	 *
	 * Checks for submission, verifies the nonce for security,
	 * sanitizes inputs, performs user authentication with wp_signon(),
	 * sets auth cookies, and redirects user accordingly.
	 *
	 * @since 1.0.0
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_signon/
	 *
	 * @return void
	 * @throws RuntimeException If any validation or authentication step fails.
	 */
	public function do_login(): void {
		/** * @global null|WP_Roles $wp_roles */
		global $wp_roles;

		try {
			if ( ! isset( $_POST['comblock_do_login_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['comblock_do_login_nonce'] ) ), 'comblock_do_login' ) ) {
				throw new RuntimeException( __( 'Error: Invalid nonce.', 'comblock-login' ) );
			}

			if ( ! $wp_roles || empty( $wp_roles->roles ) ) {
				throw new RuntimeException( __( 'Error: No user roles defined in the system.', 'comblock-login' ) );
			}

			// Sanitize user login input.
			$user_login = isset( $_POST['user'] ) ? sanitize_user( wp_unslash( $_POST['user'] ) ) : '';

			// Password is not sanitized since it may contain special chars; treat carefully.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$user_password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : '';

			// Convert rememberme checkbox to boolean.
			$remember = ! empty( $_POST['rememberme'] );

			// Get redirect URL from form input, sanitize it.
			$dashboard_id = isset( $_POST['redirect_to'] ) ? absint( $_POST['redirect_to'] ) : 0;

			/** * @var WP_Post|null $dashboard_post */
			$dashboard_post = $dashboard_id > 0 ? get_post( $dashboard_id ) : null;
			if ( ! $dashboard_post instanceof WP_Post ) {
				throw new RuntimeException( __( 'Error: Invalid redirect URL.', 'comblock-login' ) );
			}

			// Verify the redirect points to a valid dashboard post type.
			if ( Comblock_Login_Dashboard::POST_TYPE_SLUG !== $dashboard_post->post_type ) {
				throw new RuntimeException( __( 'Error: Redirect URL does not point to a valid dashboard.', 'comblock-login' ) );
			}

			/** * @var string[] $allowed_user_roles */
			$allowed_user_roles = get_post_meta( $dashboard_id, 'allowed_user_roles', false );

			/** * @var WP_User|false $check_user */
			$check_user = get_user_by( 'login', $user_login );

			if ( ! $check_user instanceof WP_User ) {
				throw new RuntimeException( __( 'Error: Invalid username.', 'comblock-login' ) );
			}

			if ( ! $check_user->has_cap( 'read' ) ) {
				throw new RuntimeException( __( 'Error: Checked capability "read" failed.', 'comblock-login' ) );
			}

			if ( ! empty( $allowed_user_roles ) && ! array_intersect( $allowed_user_roles, $check_user->roles ) ) {
				throw new RuntimeException( __( 'Error: Checked user roles do not match allowed roles.', 'comblock-login' ) );
			}

			// Clear any existing auth cookies to prevent conflicts.
			wp_clear_auth_cookie();

			/** * @var array<string, string> $credentials */
			$credentials = array(
				'user_login'    => $user_login,
				'user_password' => $user_password,
				'remember'      => $remember,
			);

			/** * @var WP_User|WP_Error $user Result of login attempt */
			$user = wp_signon( $credentials, is_ssl() );

			// Authentication failed, throw exception with error message.
			if ( is_wp_error( $user ) ) {
				throw new RuntimeException( $user->get_error_message() );
			}

			// Set current user explicitly (wp_signon may not always do this).
			wp_set_current_user( $user->ID );

			// Set auth cookies based on login result and remember flag.
			wp_set_auth_cookie( $user->ID, $remember, is_ssl() );

			// Verify user has 'read' capability to access the site.
			if ( ! current_user_can( 'read' ) ) {
				throw new RuntimeException( __( 'Error: The current user does not have permission to access this site.', 'comblock-login' ) );
			}

			/** * @var string $redirect */
			$redirect = get_permalink( $dashboard_id );
		} catch ( Throwable $e ) {
			// Save error message as a transient for showing after redirect.
			set_transient( 'comblock_login_errors', esc_html( $e->getMessage() ), 30 );

			// On error, redirect back to referer or fallback to home URL.
			$referer  = wp_get_raw_referer();
			$redirect = $referer ? $referer : '';
		}

		// Validate redirect URL; fallback to home URL if invalid.
		if ( ! wp_validate_redirect( $redirect, home_url() ) ) {
			$redirect = home_url();
		}

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Logs out the current user, then redirects to a specified URL.
	 *
	 * Checks for a POST request key 'comblock_do_logout' and verifies the nonce for security.
	 * Performs user logout using wp_logout().
	 * Destroys any active PHP sessions.
	 * Retrieves, sanitizes, and validates the redirect URL.
	 * Redirects to the validated URL or defaults to the home URL.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function do_logout(): void {
		if ( ! isset( $_POST['comblock_do_logout_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['comblock_do_logout_nonce'] ) ), 'comblock_do_logout' ) ) {
			return;
		}

		// Leave as a last check to prevent multiple disconnect calls.
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Log out user.
		wp_logout();

		// Destroy PHP session if started.
		if ( session_id() ) {
			$_SESSION = array();
			session_destroy();
		}

		// Redirect to login page.
		$this->login_redirect();

		// If the redirect doesn't work, go to the default page.
		$default_redirect = home_url();

		// Perform safe redirect and exit.
		wp_safe_redirect( $default_redirect );
		exit;
	}

	/**
	 * Handles redirect logic for user login access to a custom dashboard.
	 *
	 * This function checks if headers are already sent, whether the current request
	 * is in the admin area, and validates the global $post object to ensure it is
	 * the correct custom dashboard post type.
	 *
	 * It retrieves allowed user roles and IDs from post meta and enforces access
	 * control by redirecting users who are not logged in or do not have the
	 * correct permissions. Redirects use proper HTTP status codes (401 for unauthorized
	 * and 403 for forbidden).
	 *
	 * Additionally, it logs an error if the login page meta is not configured for
	 * the current dashboard post.
	 *
	 * @return void
	 */
	public function login_redirect(): void {
		/** * @var null|WP_Post $post */
		global $post;

		if ( ! $post instanceof WP_Post || headers_sent() || is_admin() ) {
			return;
		}

		if ( Comblock_Login_Dashboard::POST_TYPE_SLUG !== $post->post_type ) {
			return;
		}

		/** * @var string[] $allowed_user_roles */
		$allowed_user_roles = get_post_meta( $post->ID, 'allowed_user_roles', false );

		/** * @var WP_User $current_user */
		$current_user = wp_get_current_user();

		/** * @var string[] $user_roles */
		$user_roles = $current_user->roles;

		/** * @var bool $is_logged */
		$is_logged = is_user_logged_in();

		/** * @var int|null $login_page_id */
		$login_page_id = get_post_meta( $post->ID, 'login_page_id', true );
		$login_page_id = is_numeric( $login_page_id ) ? absint( $login_page_id ) : 0;

		// Error: Login page not configured.
		if ( $login_page_id < 1 ) {
			Comblock_Login_Logger::log( "[Login redirect] [login page not configured for dashboard post ID] {$post->ID}" );
		}

		/** * @var string|false $redirect_url */
		$redirect_url = get_permalink( $login_page_id );

		/** * @var string $redirect_to */
		$redirect_to = $redirect_url ? $redirect_url : home_url();

		// Validate dashboard access permission.
		if ( ! $is_logged ) {
			Comblock_Login_Logger::log( "[Login redirect] [unauthenticated user redirected for dashboard post ID] {$post->ID}" );
			wp_safe_redirect( $redirect_to );
			exit;
		}

		if ( empty( $current_user->roles ) ) {
			Comblock_Login_Logger::log( "[Login redirect] [user with empty roles redirected for dashboard post ID] {$post->ID}" );
			wp_safe_redirect( $redirect_to );
			exit;
		}

		if ( ! empty( $allowed_user_roles ) && ! array_intersect( $allowed_user_roles, $user_roles ) ) {
			Comblock_Login_Logger::log( "[Login redirect] [user role not allowed redirected for dashboard post ID] {$post->ID}, user ID: {$current_user->ID}" );
			wp_safe_redirect( $redirect_to );
			exit;
		}
	}

	/**
	 * Handles logging out the user from all devices.
	 *
	 * This function verifies the nonce for security, checks if the user is logged in,
	 * destroys all active sessions for the user, logs out the current session,
	 * and redirects to the home page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_logout_all_devices(): void {
		if ( ! isset( $_POST['logout_all_devices_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['logout_all_devices_nonce'] ) ), 'logout_all_devices_action' ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		/** * @var int $user_id */
		$user_id = get_current_user_id();

		// Destroy all sessions for the user.
		wp_destroy_all_sessions( $user_id );

		// Log out user.
		wp_logout();

		// Destroy PHP session if started.
		if ( session_id() ) {
			$_SESSION = array();
			session_destroy();
		}

		/** * @var string $redirect_to */
		$redirect_to = home_url();

		wp_safe_redirect( $redirect_to );
		exit;
	}
}
