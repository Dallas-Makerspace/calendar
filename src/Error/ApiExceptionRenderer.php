<?php
namespace App\Error;

use Cake\Error\ExceptionRenderer;
use Cake\Http\Exception\HttpException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\Response;
use Cake\Routing\Exception\MissingRouteException;

/**
 * Renders any exception raised while serving /api/* as the v1 error envelope:
 *
 *   { "errors": [ { "status", "title", "detail", "source"? } ] }
 *
 * Non-HTTP exceptions become 500s without leaking internals.
 */
class ApiExceptionRenderer extends ExceptionRenderer
{
    /**
     * Default titles per HTTP status code.
     *
     * @var array
     */
    protected $defaultTitles = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        409 => 'Conflict',
        415 => 'Unsupported Media Type',
        422 => 'Unprocessable Entity',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    ];

    /**
     * Render the exception as a JSON error envelope response.
     *
     * @return \Cake\Http\Response
     */
    public function render()
    {
        $exception = $this->error;

        if ($exception instanceof ApiException) {
            $code = $exception->getCode();
            $errors = $exception->getApiErrors();
        } elseif ($exception instanceof MissingRouteException) {
            // MissingRouteException extends Cake\Core\Exception\Exception (not
            // HttpException), so without this branch unknown /api/* routes and
            // unknown extensions (e.g. /api/v1/prerequisites.xml) would 500.
            $code = 404;
            $errors = [[
                'status' => '404',
                'title' => 'Not Found',
                'detail' => 'The requested URL was not found on this server.',
            ]];
        } elseif ($exception instanceof MethodNotAllowedException) {
            $code = 405;
            $errors = [[
                'status' => '405',
                'title' => 'Method Not Allowed',
                'detail' => $exception->getMessage() !== ''
                    ? $exception->getMessage()
                    : 'Method Not Allowed',
            ]];
        } elseif ($exception instanceof HttpException) {
            $code = $exception->getCode();
            $title = isset($this->defaultTitles[$code]) ? $this->defaultTitles[$code] : 'Error';
            $detail = $exception->getMessage() !== '' ? $exception->getMessage() : $title;
            $errors = [[
                'status' => (string)$code,
                'title' => $title,
                'detail' => $detail,
            ]];
        } else {
            $code = 500;
            $errors = [[
                'status' => '500',
                'title' => 'Internal Server Error',
                'detail' => 'An internal error occurred.',
            ]];
        }

        if ($code < 400 || $code > 599) {
            $code = 500;
        }

        $response = new Response();
        $response = $response
            ->withStatus($code)
            ->withType('json')
            ->withStringBody(json_encode(['errors' => $errors]));

        return $response;
    }
}
