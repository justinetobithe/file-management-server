<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Generate a response.
     *
     * @param bool|null $success
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function response($is_success = null, $data = null, $message = null, $code = 200): JsonResponse
    {
        $response = [];

        if (!is_null($is_success)) {
            $response['success'] = $is_success;
        }

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        if (!is_null($message)) {
            $response['message'] = $message;
        }

        return response()->json($response, $code);
    }

    /**
     * Generate a success response.
     *
     * @param mixed $data
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, $message = null, $code = 200): JsonResponse
    {
        return $this->response(true, $data, $message, $code);
    }

    /**
     * Generate an error response.
     *
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error(string $message, $code = 400): JsonResponse
    {
        return $this->response(false, null, $message, $code);
    }
}
