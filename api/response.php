<?php
/**
 * Shared JSON response helpers for API endpoints.
 *
 * These helpers ensure that all endpoints return a consistent
 * JSON structure and proper HTTP status codes.
 */

/**
 * Send a raw JSON response and terminate the request.
 *
 * @param array $payload
 * @param int   $statusCode
 */
function send_json(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode($payload);
    exit;
}

/**
 * Send a successful JSON response.
 *
 * @param mixed  $data
 * @param string $message
 * @param int    $statusCode
 */
function send_success($data = null, string $message = '', int $statusCode = 200): void
{
    $payload = ['success' => true];

    if ($message !== '') {
        $payload['message'] = $message;
    }

    if ($data !== null) {
        $payload['data'] = $data;
    }

    send_json($payload, $statusCode);
}

/**
 * Send an error JSON response.
 *
 * @param string $message
 * @param int    $statusCode
 * @param array  $errors   Field-level validation errors (if any)
 * @param array  $extra    Any additional metadata to return
 */
function send_error(string $message, int $statusCode = 400, array $errors = [], array $extra = []): void
{
    $payload = [
        'success' => false,
        'message' => $message,
    ];

    if (!empty($errors)) {
        $payload['errors'] = $errors;
    }

    if (!empty($extra)) {
        $payload = array_merge($payload, $extra);
    }

    send_json($payload, $statusCode);
}

