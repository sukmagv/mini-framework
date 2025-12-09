<?php

use Core\Logger;

require __DIR__ . '/../vendor/autoload.php';

$GLOBALS['logger'] = new Logger();

/**
 * Shutdown handler to catch fatal errors and log them.
 *
 * This function is automatically executed when the PHP script terminates.
 *
 * @return void
 */
register_shutdown_function(function() {
    $error = error_get_last();
    
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $GLOBALS['logger']->app('ERROR', $error['message'], [
            'file'    => $error['file'],
            'line'    => $error['line'],
            'type'    => $error['type']
        ]);
    }
});