# Comblock Front-End Login

[![License: GPL v2 or later](https://img.shields.io/badge/License-GPL%20v2%20or%20later-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.txt)
[![PHP Version](https://img.shields.io/badge/PHP-8.3+-green.svg)]()
[![WordPress Version](https://img.shields.io/badge/WordPress-6.8+-blue.svg)]()

Secure and personalized front-end login plugin for WordPress that allows controlled access to multiple dashboards based on user roles.

## Contributing

Contributions are welcome! Please open issues and submit pull requests via GitHub.

## Requirements

- WordPress 6.8 or higher
- PHP 8.3 or higher

## Description

The Comblock Login plugin implements a complete and secure authentication system on the frontend for WordPress. Through dedicated shortcodes, you can easily insert login and logout forms, user information displays, and the option to log out of all active sessions on your site's content. The login process is strictly managed: user-entered data is sanitized, security is ensured through nonce verification to prevent CSRF attacks, and authentication is performed using native WordPress functions, with support for secure session storage and cookie management.

Logout not only allows you to securely end the current session, but also offers the advanced global logout feature, which ends all active user sessions on all devices, increasing security in case of compromise or accidental sharing of credentials. Custom dashboards, based on dedicated post types, are protected by access controls that limit visibility to authorized users only, redirecting other users to configured login pages, with failed attempts logged to improve security monitoring.

## Features

1. Registration of the shortcode for the login form, logout button, logout button for all devices, and display of logged-in users.  
2. Secure login management with nonce verification and input sanitization.  
3. Custom redirects to the dashboard after login and to the login page after logout.  
4. Creation of multiple dashboards by configuring which user roles can access them.  
5. Logout functionality from all devices simultaneously.  
6. Logging of errors related to access and permissions.

## Installation

1. Upload the plugin folder to the `/wp-content/plugins/` directory.  
2. Activate the plugin via the ‘Plugins’ menu in WordPress.  
3. Configure the dashboards, the user roles that can access them, and the login page to which users will be redirected.  
4. Insert the shortcodes into pages or posts to display the login, logout, etc. forms.

## Usage

- Use the shortcode **`[comblock_login]`** to display the login form. You can insert this shortcode into any page or post.
This shortcode requires a mandatory attribute **`dashboard-post-id`**, whose value is the ID of a Dashboard post type created in the back office.
It also handles other optional attributes: `id`, `class`, and `privacy-page-id`, whose value is the ID of the privacy policy page.
Complete example:
**`[comblock_login id="subscriber-login" class="subscriber-form-login" dashboard-post-id="8" privacy-page-id="2"]`**
- Use the shortcode **`[comblock_logout]`** only in the **`dashboard`** post type to display the logout link. 
It also handles other optional attributes: `id`, `class`.
- Use the shortcode **`[comblock_disconnection]`** in the **`dashboard`** post type to display the disconnection link. 
It also handles other optional attributes: `id`, `class`.
- Use the shortcode **`[comblock_user_info title="my-section-title" fields="display_name,user_email"]`** in the **`dashboard`** post type to display user information. The `fields` attribute contains the user's meta fields separated by commas. Some meta fields cannot be used, while others can.
It also handles other optional attributes: `id`, `class`.

## Security

The plugin implements the following security mechanisms:

- Nonce verification for all critical login and logout actions to protect against CSRF.  
- Sanitization of input from login forms to prevent injection.  
- Authentication via WordPress' secure `wp_signon()` function.  
- Strict session cookie management with SSL support.  
- Granular control of access permissions based on user roles defined for each dashboard.  
- Complete destruction of user sessions upon logout from all devices.

## Screenshots

![Customizable login form with shortcode](https://github.com/user-attachments/assets/8085008a-eb64-4ad2-be58-74ca1fe0d131)  
![Front-end dashboard with role-controlled access](https://github.com/user-attachments/assets/05907c3b-7d33-4562-baf4-f413efe63aa0)  
![Page with user information and logout option from all devices](https://github.com/user-attachments/assets/2bb75fbc-d79c-488c-bc69-f40c3e7dfd04)

### 1.0.0

First stable version, introduces comprehensive authentication and authorization management features for multiple dashboards.

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](http://www.gnu.org/licenses/gpl-2.0.txt) file for details.

## Changelog

### 1.0.0 - 2025-11-02

- Initial version with login, logout, multiple dashboards, and integrated security management.
