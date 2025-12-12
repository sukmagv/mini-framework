<?php
namespace Core;

use Throwable;

class Logger
{
    private string $logDir;
    private string $file;

    public function __construct(string $logDir = __DIR__ . '/../logs', string $file = 'app.log')
    {
        $this->logDir = rtrim($logDir, '/');
        $this->file   = $file;

        if (!is_dir($this->logDir)) mkdir($this->logDir, 0777, true);
    }

    /**
     * Write log report to app.log
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $time = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        $logEntry = sprintf("[%s] [%s] %s %s\n", $time, strtoupper($level), $message, $contextStr);

        file_put_contents($this->logDir . '/' . $this->file, $logEntry, FILE_APPEND);
    }

    /**
     * Create information log level
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info(string $message, array $context = []): void
    { 
        $this->log('INFO', $message, $context); 
    }

    /**
     * Create warning log level
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning(string $message, array $context = []): void
    { 
        $this->log('WARNING', $message, $context); 
    }
    
    /**
     * Create error log level
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error(string $message, array $context = []): void
    { 
        $this->log('ERROR', $message, $context); 
    }

    /**
     * Create fatal error log level
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function fatal(string $message, array $context = []): void
    { 
        $this->log('FATAL', $message, $context); 
    }
}

$GLOBALS['logger'] = new Logger();

/**
 * Handles standard PHP errors
 */
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return;
    $GLOBALS['logger']->error($message, compact('file','line','severity'));
    return false;
});

/**
 * Handles uncaught exceptions
 */
set_exception_handler(function(Throwable $exception) {
    $GLOBALS['logger']->fatal($exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
});

/**
 * Handles fatal errors during shutdown
 */
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $GLOBALS['logger']->fatal($error['message'], [
            'file' => $error['file'],
            'line' => $error['line'],
            'type' => $error['type']
        ]);
    }
});
