=== Plugin Name ===
Comblock Front-End Login
Contributors: Rosario Fiorella
Donate link: 
Tags: login, user management, dashboard, shortcode, security
Requires at least: 6.8
Tested up to: 6.8.3
Stable tag: 1.0.0
Requires PHP: 8.3
License: MIT License
License URI: https://opensource.org/license/MIT

== Description ==

The Comblock Front-End Login plugin manages secure and personalized access, allowing controlled access to multiple dashboards based on user roles.

== Features ==

1. Registration of the shortcode for the login form, logout button, logout button for all devices, and display of logged-in users.
2. Secure login management with nonce verification and input sanitization.
3. Custom redirects to the dashboard after login and to the login page after logout.
4. Creation of multiple dashboards by configuring which user roles can access them.
5. Logout functionality from all devices simultaneously.
6. Logging of errors related to access and permissions.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin via the ‘Plugins’ menu in WordPress.
3. Configure the dashboards, the user roles that can access them, and the login page to which users will be redirected.
4. Insert the shortcodes into pages or posts to display the login, logout, etc. forms.

== Usage ==

- Use the shortcode `[comblock_login]` to display the login form. You can insert this shortcode into any page or post. This shortcode requires a mandatory attribute **`dashboard-post-id`**, whose value is the ID of a Dashboard post type created in the back office.
It also handles other optional attributes: `id`, `class`, `privacy-page-id` with the value of the privacy policy page ID. 
Complete example
**`[comblock_login id="subscriber-login" class="subscriber-form-login" dashboard-post-id="8" privacy-page-id="2"]`**
- Use the shortcode `[comblock_logout]` in the dashboard post type to display the logout link. 
It also handles other optional attributes: `id`, `class`.
- Use the shortcode `[comblock_disconnection]` in the dashboard post type to manage disconnection. 
It also handles other optional attributes: `id`, `class`.
- Use the shortcode `[comblock_user_info]` in the dashboard post type to display user information and the option to log out from all devices.

== Security ==

The plugin implements the following security mechanisms:

- Nonce verification for all critical login and logout actions to protect against CSRF.
- Sanitization of input from login forms to prevent injection.
- Authentication via WordPress' secure `wp_signon()` function.
- Strict session cookie management with SSL support.
- Granular control of access permissions based on user roles defined for each dashboard.
- Complete destruction of user sessions upon logout from all devices.

== Screenshots ==

1. Login form with shortcode
assets/screenshot-1.png
2. Editing Front-end dashboard with role-controlled access
assets/screenshot-2.png
3. Page with user information and logout option from all devices
assets/screenshot-3.png

== Changelog ==

= 1.0.0 =
* Initial version with login, logout, multiple dashboards, and integrated security management.

== Upgrade Notice ==

= 1.0.0 =
First stable version, introduces comprehensive authentication and authorization management features for multiple dashboards.
