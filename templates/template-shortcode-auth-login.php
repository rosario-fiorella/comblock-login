<?php

defined('WPINC') or exit;

/**
 * Renders the frontend login form via shortcode.
 *
 * This shortcode allows inserting a custom login form with configurable
 * attributes such as ID, class, action (page slug), and HTTP method.
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $attributes Shortcode attributes.
 * @return string The login form HTML or error message.
 */
function comblock_login_shortcode_template(array $attributes = []): string
{
    // Default attributes
    $defaults = [
        'id' => '',
        'class' => '',
        'dashboard-post-id' => '',
        'privacy-page-id' => ''
    ];

    // Merge and sanitize attributes
    $atts = shortcode_atts($defaults, $attributes, 'comblock_login');

    // Sanitize inputs
    $id = sanitize_key($atts['id']);
    $class = sanitize_html_class($atts['class']);
    $method = 'post';
    $dashboard_id = absint($atts['dashboard-post-id']);
    $policy_id = absint($atts['privacy-page-id']);
    $policy_url = get_permalink($policy_id) ?: '';
    $action_url = '';

    // Error message template
    $error_template = '<div class="notice notice-error inline"><p>%s</p></div>';

    // Validate all shortcode attributes
    if ($id && '' === trim($id)) {
        return sprintf($error_template, esc_html__('Error: The "id" shortcode attribute is required and must be valid.', 'comblock-login'));
    }

    if ($class && '' === trim($class)) {
        return sprintf($error_template, esc_html__('Error: The "class" shortcode attribute is required and must be valid.', 'comblock-login'));
    }

    if ($dashboard_id < 1) {
        return sprintf($error_template, esc_html__('Error: The "dashboard-post-id" shortcode attribute is required and must be valid.', 'comblock-login'));
    }

    if ($policy_id > 0 && !$policy_url) {
        return sprintf($error_template, esc_html__('Error: The "privacy-page-id" shortcode attribute is required and must be valid.', 'comblock-login'));
    }

    // Retrieve and clear transient errors
    $errors = get_transient('comblock_login_errors');
    if ($errors !== false) {
        delete_transient('comblock_login_errors');
    }

    // Render form HTML via output buffering
    ob_start();
?>
    <form id="{form_id}" action="{form_action}" method="{form_method}" class="comblock-login {form_class}" novalidate="novalidate">
        <fieldset class="comblock-login__fieldset">
            <legend class="comblock-login__legend">{form_legend}</legend>
            <div class="comblock-login__group comblock-login__group--errors">
                {errors}
            </div>
            <div class="comblock-login__group">
                <label for="user" class="comblock-login__label">{username_label}</label>
                <input type="text" id="user" name="user" value="" placeholder="{username_placeholder}" required="required" aria-required="true" aria-describedby="user_error" autocomplete="username" class="comblock-login__input" />
            </div>
            <div class="comblock-login__group">
                <label for="password" class="comblock-login__label">{password_label}</label>
                <input type="password" id="password" name="password" placeholder="{password_placeholder}" required="required" aria-required="true" aria-describedby="password_error" autocomplete="current-password" class="comblock-login__input" />
            </div>
            <div class="comblock-login__group comblock-login__group--checkbox">
                <input type="checkbox" id="rememberme" name="rememberme" value="1" aria-checked="false" class="comblock-login__checkbox" />
                <label for="rememberme" class="comblock-login__label comblock-login__label--checkbox">{rememberme_label}</label>
            </div>
            <div class="comblock-login__privacy-notice">
                <p>
                    {policy_text}
                    <a href="{policy_url}" target="_blank" rel="noopener">{policy_label}</a>
                </p>
            </div>
            <div class="comblock-login__hidden">
                {nonce}
                <input id="redirect_to" type="hidden" name="redirect_to" value="{redirect_to}" />
            </div>
            <div class="comblock-login__actions">
                <button type="reset" class="comblock-login__button comblock-login__button--reset">{reset_label}</button>
                <button type="submit" class="comblock-login__button comblock-login__button--submit">{submit_label}</button>
            </div>
        </fieldset>
    </form>
<?php

    // Get template HTML
    $template_html = ob_get_clean() ?: '';

    // Placeholder array for template substitution
    $placeholders = [
        '{form_id}' => esc_attr($id),
        '{form_action}' => $action_url,
        '{form_method}' => esc_attr($method),
        '{form_class}' => esc_attr($class),
        '{form_legend}' => esc_html__('Login', 'comblock-login'),
        '{username_label}' => esc_html__('Username', 'comblock-login'),
        '{username_placeholder}' => esc_attr__('Enter your username', 'comblock-login'),
        '{errors}' => is_string($errors) ? wp_kses_post($errors) : '',
        '{password_label}' => esc_html__('Password', 'comblock-login'),
        '{password_placeholder}' => esc_attr__('Enter your password', 'comblock-login'),
        '{rememberme_label}' => esc_html__('Remember me', 'comblock-login'),
        '{policy_text}' => esc_html__('For more information about the processing of personal data, please consult the Privacy Policy.', 'comblock-login'),
        '{policy_label}' => esc_html__('Click here', 'comblock-login'),
        '{policy_url}' => $policy_url,
        '{nonce}' => wp_nonce_field('comblock_do_login', 'comblock_do_login_nonce', true, false),
        '{redirect_to}' => $dashboard_id,
        '{reset_label}' => esc_html__('Reset', 'comblock-login'),
        '{submit_label}' => esc_html__('Log In', 'comblock-login')
    ];

    // Replace placeholders with actual escaped values
    $template_html = str_replace(array_keys($placeholders), array_values($placeholders), $template_html);

    // Allowed HTML tags and attributes for output sanitization
    $allowed_html = [
        'form' => ['id' => true, 'action' => true, 'method' => true, 'class' => true, 'novalidate' => true],
        'fieldset' => [],
        'legend' => [],
        'div' => ['class' => true, 'aria-live' => true, 'role' => true],
        'label' => ['for' => true, 'class' => true],
        'input' => [
            'type' => true,
            'id' => true,
            'name' => true,
            'value' => true,
            'placeholder' => true,
            'required' => true,
            'aria-required' => true,
            'aria-describedby' => true,
            'autocomplete' => true,
            'aria-checked' => true,
            'class' => true,
        ],
        'p' => [],
        'a' => ['href' => true, 'target' => true, 'rel' => true],
        'button' => ['type' => true, 'class' => true],
    ];

    return wp_kses($template_html, $allowed_html);
}
