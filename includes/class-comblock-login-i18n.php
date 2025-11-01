<?php

/**
 * Handles localization (i18n) for the Comblock Login plugin.
 *
 * Loads translation files from the /languages/ directory of the plugin.
 *
 * @since 1.0.0
 * @package wordpress-comblock-login
 * @subpackage wordpress-comblock-login/includes
 */
class Comblock_Login_i18n
{
    /**
     * Loads the plugin textdomain for translation.
     *
     * @since 1.0.0
     * @return void
     */
    public function load_plugin_textdomain(): void
    {
        $path_base = plugin_basename(__FILE__);

        $path_lang = sprintf('%s/languages/', dirname(dirname($path_base)));

        load_plugin_textdomain(Comblock_Login::DOMAIN, false, $path_lang);
    }
}
