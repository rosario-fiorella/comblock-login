<?php

/**
 * Class Comblock_Login_Manager
 *
 * This class manages the login functionality for the Comblock system.
 * Add a brief description of what this class does and its main responsibilities.
 *
 * @package wordpress-comblock-login
 * @author Rosario Fiorella
 * @version 1.0.0
 */
class Comblock_Login_Manager
{
    /**
     * Register the plugin shortcode with WordPress to manage authenticated access to the front office dashboard.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_shortcode(): void
    {
        $this->register_login_shortcode();

        $this->register_logout_shortcode();
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
    protected function register_login_shortcode(): void
    {
        /**
         * @var callback<array<string, scalar>> $callback
         * @return string
         */
        $callback = function (array $attributes = []): string {
            return comblock_login_shortcode_template($attributes);
        };

        add_shortcode('comblock_login', $callback);
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
    protected function register_logout_shortcode(): void
    {
        /**
         * @var callback<array<string, scalar>> $callback
         * @return string
         */
        $callback = function (array $attributes = []): string {
            return comblock_logout_shortcode_template($attributes);
        };

        add_shortcode('comblock_logout', $callback);
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
     */
    public function do_login(): void
    {
        if (!isset($_POST['comblock_do_login_nonce'])) {
            return;
        }

        // Clear any existing auth cookies to prevent conflicts
        wp_clear_auth_cookie();

        try {
            // Verify nonce to protect against CSRF attacks
            if (!isset($_POST['comblock_do_login_nonce']) || !wp_verify_nonce($_POST['comblock_do_login_nonce'], 'comblock_do_login')) {
                throw new InvalidArgumentException(__('Error: Invalid nonce.', 'comblock-login'));
            }

            // Sanitize user login input
            $user_login = isset($_POST['user']) ? sanitize_user(wp_unslash($_POST['user'])) : '';

            // Password is not sanitized since it may contain special chars; treat carefully
            $user_password = $_POST['password'] ?? '';

            // Convert rememberme checkbox to boolean
            $remember = !empty($_POST['rememberme']);

            /** * @var array<string, string> $credentials */
            $credentials = [
                'user_login' => $user_login,
                'user_password' => $user_password,
                'remember' => $remember
            ];

            /**
             * @var WP_User|WP_Error $user Result of login attempt
             */
            $user = wp_signon($credentials, is_ssl());

            // Authentication failed, throw exception with error message
            if (is_wp_error($user)) {
                throw new RuntimeException($user->get_error_message());
            }

            // Set current user explicitly (wp_signon may not always do this)
            wp_set_current_user($user->ID);

            // Set auth cookies based on login result and remember flag
            wp_set_auth_cookie($user->ID, $remember, is_ssl());

            // Get redirect URL from form input, sanitize it
            $redirect = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : '';
        } catch (Throwable $e) {
            // Save error message as a transient for showing after redirect
            set_transient('comblock_login_errors', $e->getMessage(), 30);

            // On error, redirect back to referer or fallback to home URL
            $redirect = wp_get_raw_referer() ?: '';
        }

        // Validate redirect URL; fallback to home URL if invalid
        if (!wp_validate_redirect($redirect, home_url())) {
            $redirect = home_url();
        }

        // Perform safe redirect and exit
        wp_safe_redirect($redirect);
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
    public function do_logout(): void
    {
        // Check if logout form was submitted and nonce is valid to avoid CSRF
        if (!isset($_POST['comblock_do_logout_nonce']) || !wp_verify_nonce($_POST['comblock_do_logout_nonce'], 'comblock_do_logout')) {
            return;
        }

        // leave as a last check to prevent multiple disconnect calls
        if (!is_user_logged_in()) {
            return;
        }

        // Log out user
        wp_logout();

        // Destroy PHP session if started
        if (session_id()) {
            $_SESSION = [];
            session_destroy();
        }

        // Redirect to login page
        $this->login_redirect();

        // If the redirect doesn't work, go to the default page
        $default_redirect = home_url();

        // Perform safe redirect and exit
        wp_safe_redirect($default_redirect);
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
    public function login_redirect(): void
    {
        /**
         * @var null|WP_Post $post
         */
        global $post;

        if (headers_sent() || is_admin()) {
            return;
        }

        if (!$post instanceof WP_Post) {
            return;
        }

        if ($post->post_type !== Comblock_Login_Dashboard::POST_TYPE_SLUG) {
            return;
        }

        /** * @var string[] $allowed_user_roles */
        $allowed_user_roles = get_post_meta($post->ID, 'allowed_user_roles', false) ?: [];

        /** * @var WP_User $current_user */
        $current_user = wp_get_current_user();

        /** * @var string[] $user_roles */
        $user_roles = array_keys($current_user->roles);

        /** * @var bool $is_logged */
        $is_logged = is_user_logged_in();

        /** * @var int|null $login_page_id */
        $login_page_id = get_post_meta($post->ID, 'login_page_id', true) ?: null;

        // Error: Login page not configured
        if ($login_page_id === null) {
            error_log("[Login redirect] [login page not configured for dashboard post ID] {$post->ID}");
        }

        /** * @var string $redirect_to */
        $redirect_to = is_numeric($login_page_id) ? get_permalink($login_page_id) : home_url();

        // Validate dashboard access permission
        if (!$is_logged) {
            error_log("[Login redirect] [unauthenticated user redirected for dashboard post ID] {$post->ID}");
            wp_safe_redirect($redirect_to);
            exit;
        }

        if (empty($current_user->roles)) {
            error_log("[Login redirect] [user with empty roles redirected for dashboard post ID] {$post->ID}");
            wp_safe_redirect($redirect_to);
            exit;
        }

        if (!empty($allowed_user_roles) && !array_intersect($allowed_user_roles, $user_roles)) {
            error_log("[Login redirect] [user role not allowed redirected for dashboard post ID] {$post->ID}, user ID: {$current_user->ID}");
            wp_safe_redirect($redirect_to);
            exit;
        }
    }
}
