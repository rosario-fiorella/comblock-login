<?php

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @since 1.0.0
 * @package comblock-login
 * @subpackage comblock-login/includes
 */
class Comblock_Login_Loader
{
    /**
     * Default priority for hooks
     */
    private const DEFAULT_PRIORITY = 10;

    /**
     * Default number of accepted arguments
     */
    private const DEFAULT_ACCEPTED_ARGS = 1;

    /**
     * The array of actions registered with WordPress.
     *
     * @since 1.0.0
     * @access protected
     * @var array $actions Array of registered actions [(string) hook, (object) component, (string) callback]
     */
    protected array $actions;

    /**
     * The array of filters registered with WordPress.
     *
     * @since 1.0.0
     * @access protected
     * @var array $filters
     */
    protected array $filters;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->actions = [];
        $this->filters = [];
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @since 1.0.0
     * @param string $hook          The name of the WordPress action that is being registered.
     * @param object $component     A reference to the instance of the object on which the action is defined.
     * @param string $callback      The name of the function definition on the $component.
     * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
     * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_action(string $hook, object $component, string $callback, int $priority = self::DEFAULT_PRIORITY, int $accepted_args = self::DEFAULT_ACCEPTED_ARGS): void
    {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @since 1.0.0
     * @param string $hook          The name of the WordPress filter that is being registered.
     * @param object $component     A reference to the instance of the object on which the filter is defined.
     * @param string $callback      The name of the function definition on the $component.
     * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
     * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_filter(string $hook, object $component, string $callback, int $priority = self::DEFAULT_PRIORITY, int $accepted_args = self::DEFAULT_ACCEPTED_ARGS): void
    {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    /**
     * @since 1.0.0
     * @access private
     * @param array $hooks
     * @param string $hook
     * @param object $component
     * @param string $callback
     * @param int $priority
     * @param int $accepted_args
     * @return array<int, array<string, mixed>>
     * @throws InvalidArgumentException
     */
    private function add(array $hooks, string $hook, object $component, string $callback, int $priority, int $accepted_args): array
    {
        if (empty($hook)) {
            throw new InvalidArgumentException(esc_html__('Error: hook name cannot be empty', 'comblock-login'));
        }

        if (!method_exists($component, $callback)) {
            throw new InvalidArgumentException(esc_html__('Error: callback method does not exist on component', 'comblock-login'));
        }

        if ($priority < 1) {
            throw new InvalidArgumentException(esc_html__('Error: priority must be a positive integer', 'comblock-login'));
        }

        if ($accepted_args < 1) {
            throw new InvalidArgumentException(esc_html__('Error: accepted_args must be a positive integer', 'comblock-login'));
        }

        $hooks[] = [
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
        ];

        return $hooks;
    }

    /**
     * Register the filters and actions with WordPress.
     *
     * @since 1.0.0
     * @return void
     */
    public function run(): void
    {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], [$hook['component'], $hook['callback']], $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], [$hook['component'], $hook['callback']], $hook['priority'], $hook['accepted_args']);
        }
    }
}
