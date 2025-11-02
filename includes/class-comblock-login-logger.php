<?php

/**
 * Class for logging messages related to login activities.
 *
 * @since 1.0.0
 * @package comblock-login
 * @subpackage comblock-login/includes
 */
class Comblock_Login_Logger
{
    /**
     * Log a message to the error log if WP_DEBUG is enabled.
     *
     * @since 1.0.0
     * @param string $message The message to log.
     * @return void
     */
    public static function log(string $message): void
    {
        if (WP_DEBUG === true && !empty($message)) {
            error_log($message);
        }
    }
}
