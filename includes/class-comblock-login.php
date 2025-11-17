<?php

/**
 * Main class for the Comblock Login plugin.
 *
 * Handles initialization, dependency loading, and hook registration.
 *
 * @since 1.0.0
 * @package comblock-login
 * @subpackage comblock-login/includes
 */
class Comblock_Login
{
    /**
     * Plugin textdomain for translations.
     *
     * @since 1.0.0
     */
    public const string DOMAIN = 'comblock-login';

    /**
     * Plugin version.
     *
     * @since 1.0.0
     */
    public const string VERSION = '1.0.0';

    /**
     * Loader instance for registering hooks.
     *
     * @access protected
     * 
     * @var Comblock_Login_Loader
     */
    protected Comblock_Login_Loader $loader;

    /**
     * Constructor. Initializes the plugin by loading dependencies and defining hooks.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->load_dependencies();

        $this->define_hooks();
    }

    /**
     * Loads all plugin dependencies.
     *
     * @since 1.0.0
     * 
     * @access protected
     * 
     * @return void
     */
    private function load_dependencies(): void
    {
        $plugin_path = plugin_dir_path(__DIR__);

        require_once $plugin_path . 'includes/class-comblock-login-logger.php';
        require_once $plugin_path . 'includes/class-comblock-login-loader.php';
        require_once $plugin_path . 'includes/class-comblock-login-dashboard.php';
        require_once $plugin_path . 'includes/class-comblock-login-manager.php';
        require_once $plugin_path . 'public/class-comblock-login-public.php';
        require_once $plugin_path . 'templates/template-shortcode-auth-login.php';
        require_once $plugin_path . 'templates/template-shortcode-auth-logout.php';
        require_once $plugin_path . 'templates/template-shortcode-auth-disconnection.php';
        require_once $plugin_path . 'templates/template-shortcode-user-info.php';

        $this->loader = new Comblock_Login_Loader();
    }

    /**
     * Defines all hooks for the plugin (actions and shortcodes).
     *
     * @since 1.0.0
     * 
     * @access protected
     * 
     * @return void
     */
    protected function define_hooks(): void
    {
        $manager = new Comblock_Login_Manager();
        $dashboard = new Comblock_Login_Dashboard();
        $enqueue = new Comblock_Login_Public();

        $this->loader->add_action('wp_enqueue_scripts', $enqueue, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $enqueue, 'enqueue_scripts');
        $this->loader->add_action('template_redirect', $manager, 'login_redirect');
        $this->loader->add_action('init', $manager, 'register_shortcode');
        $this->loader->add_action('init', $dashboard, 'register_post_type');
        $this->loader->add_action('add_meta_boxes', $dashboard, 'add_meta_boxes');
        $this->loader->add_action('save_post_' . Comblock_Login_Dashboard::POST_TYPE_SLUG, $dashboard, 'save_post');
        $this->loader->add_action('admin_post_nopriv_logout_all_devices', $manager, 'handle_logout_all_devices');
        $this->loader->add_action('admin_post_logout_all_devices', $manager, 'handle_logout_all_devices');
    }

    /**
     * Runs the loader to register all hooks with WordPress.
     *
     * @since 1.0.0
     * @return void
     */
    public function run(): void
    {
        $this->loader->run();
    }
}
