<?php
namespace App\Error\Middleware;

use App\Error\ApiExceptionRenderer;
use Cake\Error\Middleware\ErrorHandlerMiddleware;

/**
 * Error handler that renders /api/* exceptions with the v1 JSON error
 * envelope while leaving every other request on the default HTML renderer.
 *
 * Needed because the base middleware resolves the renderer class from its
 * construction-time config, so the Configure value written by
 * ApiAppController::initialize() would otherwise never apply.
 */
class ApiAwareErrorHandlerMiddleware extends ErrorHandlerMiddleware
{
    /**
     * Pick the renderer per request path.
     *
     * @param \Exception $exception The exception being rendered.
     * @param \Cake\Http\ServerRequest $request The request.
     * @return \Cake\Error\ExceptionRendererInterface The exception renderer.
     */
    protected function getRenderer($exception, $request)
    {
        if (strpos($request->getUri()->getPath(), '/api/') === 0) {
            $this->exceptionRenderer = ApiExceptionRenderer::class;
        } else {
            $this->exceptionRenderer = null;
        }

        return parent::getRenderer($exception, $request);
    }
}
