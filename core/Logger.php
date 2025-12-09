<?php
namespace Core;

class Logger
{
    private string $logDir;
    private string $file;

    /**
     * Initialize the Logger class by specifying the directory and log file
     *
     * @param [type] $logDir
     * @param string $file
     */
    public function __construct(string $logDir = __DIR__ . '/../logs', string $file = 'app.log')
    {
        $this->logDir = rtrim($logDir, '/');
        $this->file   = $file;

        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }

        if (!file_exists("$this->logDir/{$this->file}")) {
            touch("$this->logDir/{$this->file}");
        }
    }

    /**
     * Write a log entry to the log file
     *
     * @param string $status
     * @param string $message
     * @param array $context
     * @return void
     */
    private function write(string $status, string $message, array $context = []): void
    {
        $time = date('Y-m-d H:i:s');

        $line = "[$time][$status] $message";
        if (!empty($context)) {
            $line .= ' | ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        file_put_contents("$this->logDir/{$this->file}", $line . PHP_EOL, FILE_APPEND);
    }

    /**
     * Log a message with a given status
     *
     * @param string $status
     * @param string $message
     * @param array $context
     * @return void
     */
    public function app(string $status, string $message, array $context = []): void
    {
        $this->write($status, $message, $context);
    }
}
