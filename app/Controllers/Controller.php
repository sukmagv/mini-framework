<?php
namespace App\Controllers;

use Core\Logger;
use Core\Response;

class Controller
{
    protected Logger $logger;

    public function __construct(?Logger $logger = null)
    {
        $this->logger = $logger ?? $GLOBALS['logger'];
    }

    /**
     * Handles a given callback function with automatic logging and standardized response.
     *
     * @param callable $callback
     * @param string $action
     * @return array
     */
    protected function handle(callable $callback, string $action = ''): array
    {
        try {
            $result = $callback();

            $isFailed = is_array($result) && (
                ($result['status'] ?? '') === 'failed' ||
                isset($result['error']) ||
                isset($result['not_found'])
            );

            if ($isFailed) {
                $this->logger->app("ERROR", "Action failed: $action", ['result' => $result]);

                if (!isset($result['status'])) {
                    return Response::failed($result['message'] ?? 'Unknown error', $result['code'] ?? 500);
                }

                return $result;
            }

            $this->logger->app("INFO", "Action executed successfully: $action", ['result' => $result]);
            return $result;

        } catch (\Throwable $e) {
            $this->logger->app("ERROR", "Exception in action: $action", [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString()
            ]);

            return Response::failed('Internal Server Error', 500);
        }
    }
}