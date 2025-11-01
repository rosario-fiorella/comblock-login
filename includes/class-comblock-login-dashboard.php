<?php

/**
 * Class for managing custom post type 'dashboard' with meta boxes
 */
class Comblock_Login_Dashboard
{
    /**
     * Post type slug
     */
    public const string POST_TYPE_SLUG = 'comblock_dashboard';

    /**
     * Register custom post type
     */
    public function register_post_type(): void
    {
        $args = [
            'labels' => [
                'name' => __('Dashboard', 'comblock-login'),
                'singular_name' => __('Dashboard', 'comblock-login'),
                'add_new' => __('Add New', 'comblock-login'),
                'add_new_item' => __('Add New Dashboard', 'comblock-login'),
                'edit_item' => __('Edit Dashboard', 'comblock-login'),
                'new_item' => __('New Dashboard', 'comblock-login'),
                'view_item' => __('View Dashboard', 'comblock-login'),
                'search_items' => __('Search Dashboard', 'comblock-login'),
                'not_found' => __('No dashboard found', 'comblock-login'),
                'not_found_in_trash' => __('No dashboard found in Trash', 'comblock-login'),
                'all_items' => __('All Dashboard', 'comblock-login'),
                'menu_name' => __('Dashboard', 'comblock-login'),
                'name_admin_bar' => __('Dashboard', 'comblock-login')
            ],
            'public' => true,
            'has_archive' => true,
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'show_in_rest' => true,
            'map_meta_cap' => true,
            'menu_icon' => 'dashicons-screenoptions',
            'rewrite' => ['slug' => 'dashboard'],
            'supports' => ['title', 'editor', 'author', 'page-attributes']
        ];

        register_post_type(self::POST_TYPE_SLUG, $args);
    }

    /**
     * Add meta boxes for roles and login page selection
     * 
     * @return void
     */
    public function add_meta_boxes(): void
    {
        add_meta_box('roles_metabox', __('Select Roles', 'comblock-login'), [$this, 'render_roles_metabox'], self::POST_TYPE_SLUG, 'side');
        add_meta_box('login_page_metabox', __('Select Login Page', 'comblock-login'), [$this, 'render_login_page_metabox'], self::POST_TYPE_SLUG, 'side');
    }

    /**
     * Render the roles checkbox list in the metabox using template placeholders and output buffering.
     * 
     * @param WP_Post $post
     */
    public function render_roles_metabox(WP_Post $post): void
    {
        /**
         * @global null|WP_Roles $wp_roles
         */
        global $wp_roles;

        if (!$wp_roles || empty($wp_roles->roles)) {
            return;
        }

        /** * @var array<int, array<string, string>> $roles_sorted */
        $roles_sorted = $wp_roles->roles;
        uasort($roles_sorted, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        /**
         * Get saved roles or empty array if none
         * 
         * @var array<string, array<string, string>> $roles
         */
        $roles = get_post_meta($post->ID, 'allowed_user_roles', false) ?: [];

        /**
         * Informative text
         * 
         * @var string $info
         */
        $info = __('Select the user roles that can access the dashboard. By default, all roles can access it.', 'comblock-login');

        /** @var string $nonce */
        $nonce = wp_nonce_field('save_roles_nonce', 'roles_nonce', true, false);

        /**
         * @var string $template_html
         */
        $template_html = sprintf('<p>%s</p><p>%s</p>', $info, $nonce);
        foreach ($roles_sorted as $role_slug => $role) {
            $esc_slug = esc_attr($role_slug);
            $esc_name = esc_html($role['name']);

            $input_html = '';
            if (in_array($role_slug, $roles, true)) {
                $input_html .= sprintf('<input type="checkbox" id="role_%s" name="allowed_user_roles[]" value="%s" checked="checked"> %s', $esc_slug, $esc_slug, $esc_name);
            } else {
                $input_html .= sprintf('<input type="checkbox" id="role_%s" name="allowed_user_roles[]" value="%s"> %s', $esc_slug, $esc_slug, $esc_name);
            }

            $template_html .= sprintf('<p>%s</p>', $input_html);
        }

        // Allowed HTML tags and attributes for wp_kses
        $allowed_html = [
            'p' => [],
            'label' => ['for' => true],
            'input' => ['type' => true, 'id' => true, 'name' => true, 'class' => [], 'value' => true, 'checked' => true],
        ];

        // Sanitize the final output
        echo wp_kses($template_html, $allowed_html);
    }

    /**
     * Render select list of pages for login page
     * 
     * @param WP_Post $post
     */
    public function render_login_page_metabox(WP_Post $post): void
    {
        /** * @var string $selected_page_id */
        $selected_page_id = get_post_meta($post->ID, 'login_page_id', true) ?: '';

        /**
         * Retrieve all published pages
         * 
         * @var WP_Post[]
         */
        $pages = get_posts([
            'post_type' => ['page', 'post'],
            'post_status' => 'publish',
            'orderby' => 'post_title',
            'order' => 'ASC',
            'numberposts' => -1
        ]) ?: [];

        /** * @var string $options_html */
        $options_html = sprintf('<option value="0">%s</option>', esc_html__('Select a page', 'comblock-login'));
        foreach ($pages as $page) {
            $esc_id = esc_attr($page->ID);
            $esc_title = esc_html($page->post_title);

            if (absint($page->ID) === absint($selected_page_id)) {
                $options_html .= sprintf('<option value="%s" selected="selected">%s</option>', $esc_id, $esc_title);
            } else {
                $options_html .= sprintf('<option value="%s">%s</option>', $esc_id, $esc_title);
            }
        }

        /** @var string $nonce */
        $nonce = wp_nonce_field('save_page_nonce', 'page_nonce', true, false);

        /**
         * Informative text
         * 
         * @var string $info
         */
        $info = __('Select the login page that allows you to access this dashboard.', 'comblock-login');

        /**
         * Template HTML
         * 
         * @var string $template_html
         */
        $template_html = sprintf('%s<p>%s</p><p><select name="login_page_id">%s</select></p>', $nonce, $info, $options_html);

        /**
         * Allowed HTML tags and attributes for wp_kses
         * 
         * @var array<string, array<string, mixed>> $allowed_html
         */
        $allowed_html = [
            'p' => [],
            'select' => ['name' => true],
            'option' => ['value' => true, 'selected' => true],
            'input'  => ['type' => true, 'id' => true, 'name' => true, 'class' => [], 'value' => true],
        ];

        // Sanitize the final output
        echo wp_kses($template_html, $allowed_html);
    }

    /**
     * Save meta box data securely
     *
     * @param int $post_id
     */
    public function save_post(int $post_id): void
    {
        // Avoid autosave/revision saving
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id) || empty($_POST)) {
            return;
        }

        // Verify nonce for roles
        if (!isset($_POST['roles_nonce']) || !wp_verify_nonce($_POST['roles_nonce'], 'save_roles_nonce')) {
            return;
        }

        // Verify nonce for login page
        if (!isset($_POST['page_nonce']) || !wp_verify_nonce($_POST['page_nonce'], 'save_page_nonce')) {
            return;
        }

        // Check user capability
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save selected roles
        $roles = isset($_POST['allowed_user_roles']) && is_array($_POST['allowed_user_roles']) ? $_POST['allowed_user_roles'] : [];
        delete_post_meta($post_id, 'allowed_user_roles');
        foreach ($roles as $role) {
            if (is_string($role)) {
                add_post_meta($post_id, 'allowed_user_roles', sanitize_text_field($role), false);
            }
        }

        // Save selected login page
        $page_id = isset($_POST['login_page_id']) ? absint($_POST['login_page_id']) : 0;
        delete_post_meta($post_id, 'login_page_id');
        if ($page_id > 0 && get_post_status($page_id) === 'publish') {
            add_post_meta($post_id, 'login_page_id', $page_id, true);
        }
    }
}
