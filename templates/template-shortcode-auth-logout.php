<?php

defined('WPINC') or exit;

/**
 * Renders the frontend logout form via shortcode.
 *
 * This shortcode allows inserting a custom logout form with configurable
 * attributes such as ID, class, and HTTP method.
 *
 * @since 1.0.0
 *
 * @param array<string, mixed> $attributes Shortcode attributes.
 * @return string The logout form HTML or error message.
 */
function comblock_logout_shortcode_template(array $attributes = []): string
{
    // Validate rendering: User logged in to frontend required
    if (!is_user_logged_in() || is_admin()) {
        return '';
    }

    // Default attributes
    $defaults = ['id' => '', 'class' => ''];

    // Merge and sanitize attributes
    $atts = shortcode_atts($defaults, $attributes, 'comblock_logout');

    // Sanitize inputs
    $id = sanitize_key($atts['id']);
    $class = sanitize_html_class($atts['class']);
    $nonce = wp_nonce_field('comblock_do_logout', 'comblock_do_logout_nonce', true, false);

    // Output the form
    ob_start();
?>
    <form id="{form_id}" method="{form_method}" class="comblock-logout {form_class}" action="{form_action}" novalidate="novalidate">
        <fieldset class="comblock-logout__fieldset">
            <div class="comblock-logout__hidden">{nonce}</div>
            <div class="comblock-logout__actions">
                <button type="submit" class="comblock-logout__button comblock-logout__button--submit">
                    <span class="dashicons dashicons-exit"></span> {submit_label}
                </button>
            </div>
        </fieldset>
    </form>
<?php

    // Cattura template HTML
    $template_html = ob_get_clean() ?: '';

    // Array placeholder per la sostituzione
    $placeholders = [
        '{form_id}' => esc_attr($id),
        '{form_method}' => 'post',
        '{form_class}' => esc_attr($class),
        '{form_action}' => '',
        '{nonce}' => $nonce,
        '{submit_label}' => esc_html__('Logout', 'comblock-login')
    ];

    // Sostituisci placeholder con i valori reali
    $template_html = str_replace(array_keys($placeholders), array_values($placeholders), $template_html);

    // Allowed HTML tags and attributes
    $allowed_html = [
        'form' => ['id' => true, 'action' => true, 'method' => true, 'class' => true, 'novalidate' => true],
        'fieldset' => ['class' => true],
        'div' => ['class' => true],
        'button' => ['type' => true, 'class' => true],
        'input' => [
            'type' => true,
            'id' => true,
            'name' => true,
            'value' => true,
            'required' => true,
            'class' => true,
        ],
        'span' => ['class' => true],
    ];

    return wp_kses($template_html, $allowed_html);
}
