<?php
namespace Core;

use Throwable;
use Enums\HttpStatus;

class ErrorHandler
{
    public static function handle(Throwable $e): array
    {
        $GLOBALS['logger']->error($e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $message = strtolower($e->getMessage());

        if (str_contains($message, 'method not allowed')) {
            return self::methodNotAllowed($e);
        }

        if (str_contains($message, 'not found')) {
            return self::notFound($e);
        }

        if (str_contains($message, 'invalid') || str_contains($message, 'required') || str_contains($message, 'must be')) {
            return self::badRequest($e);
        }

        return self::internalServerError($e);
    }

    private static function methodNotAllowed(Throwable $e): array
    {
        return Response::failed($e->getMessage(), HttpStatus::METHOD_NOT_ALLOWED);
    }

    private static function notFound(Throwable $e): array
    {
        return Response::failed($e->getMessage(), HttpStatus::NOT_FOUND);
    }

    private static function internalServerError(Throwable $e): array
    {
        return Response::failed("Internal Server Error", HttpStatus::INTERNAL_SERVER_ERROR);
    }

    public static function badRequest(Throwable $e): array
    {
        $errors = [];

        $decoded = json_decode($e->getMessage(), true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $errors = $decoded;
        } else {
            $errors[] = $e->getMessage();
        }

        return Response::failed($errors, HttpStatus::BAD_REQUEST);
    }
}
