<?php

declare(strict_types=1);

namespace Pebble;

use function Safe\json_encode;

class JSON
{
    public static bool $debug = false;

    /**
     * json_encode wrapper which just add content-type header
     * @param mixed $value
     * @param int $flags
     * @param int<1, max> $depth
     * @return mixed
     */
    public static function response(mixed $value, int $flags = 0, int $depth = 512, bool $send_header = true)
    {
        if ($send_header) {
            header('Content-Type: application/json; charset=utf-8');
        }

        if (self::$debug) {
            if (is_array($value)) {
                $value['__POST'] = $_POST;
                $value['__GET'] = $_GET;
            }
        }

        $res = json_encode($value, $flags, $depth);
        return $res;
    }

    /**
     * Render JSON
     * @param mixed $value
     * @param int $flags
     * @param int<1, max> $depth
     */
    public function render($value, int $flags = 0, int $depth = 512, bool $send_header = true): void
    {
        echo self::response($value, $flags, $depth, $send_header);
    }

    /**
     * Render an error response where 'error' => true
     * @param array<mixed> $response
     */
    public function renderError(array $response = []): void
    {
        $response['error'] = true;
        $this->render($response);
    }

    /**
     * Render an error response where 'error' => true and 'message' => $message
     */
    public function renderErrorWithMessage(string $message): void
    {
        $this->renderError(['message' => $message]);
    }

    /**
     * Render a success response where 'error' => false
     * @param array<mixed> $response
     */
    public function renderSuccess(array $response = []): void
    {
        $response['error'] = false;
        $this->render($response);
    }

    /**
     * Render a success response where 'error' => false and 'message' => $message
     */
    public function renderSuccessWithMessage(string $message): void
    {
        $this->renderSuccess(['message' => $message]);
    }
}
