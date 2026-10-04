<?php
namespace App\Error;

use Cake\Http\Exception\HttpException;

/**
 * HTTP exception carrying one or more JSON:API-style error objects.
 *
 * The v1 API always renders failures with the error envelope
 * `{ "errors": [ { "status", "title", "detail", "source"? } ] }`, so this
 * exception is the single way API actions signal 4xx/5xx responses.
 */
class ApiException extends HttpException
{
    /**
     * Error objects rendered into the envelope.
     *
     * @var array
     */
    protected $apiErrors = [];

    /**
     * @param int $code HTTP status code.
     * @param string $title Short error title.
     * @param string $detail Human-readable detail.
     * @param string|null $pointer JSON pointer to the offending field, if any.
     * @param \Exception|null $previous Previous exception.
     */
    public function __construct($code, $title, $detail, $pointer = null, $previous = null)
    {
        $this->apiErrors = [$this->buildError($code, $title, $detail, $pointer)];
        parent::__construct($detail, $code, $previous);
    }

    /**
     * Create an ApiException from a pre-built list of error objects.
     *
     * @param int $code HTTP status code.
     * @param array $errors List of error objects.
     * @param \Exception|null $previous Previous exception.
     * @return static
     */
    public static function withErrors($code, array $errors, $previous = null)
    {
        $first = reset($errors);
        $instance = new static(
            $code,
            isset($first['title']) ? $first['title'] : 'Error',
            isset($first['detail']) ? $first['detail'] : ''
        );
        $instance->apiErrors = array_values($errors);

        return $instance;
    }

    /**
     * @return array The error objects for the envelope.
     */
    public function getApiErrors()
    {
        return $this->apiErrors;
    }

    /**
     * Build a single error object.
     *
     * @param int $code HTTP status code.
     * @param string $title Short error title.
     * @param string $detail Human-readable detail.
     * @param string|null $pointer JSON pointer to the offending field, if any.
     * @return array
     */
    protected function buildError($code, $title, $detail, $pointer = null)
    {
        $error = [
            'status' => (string)$code,
            'title' => $title,
            'detail' => $detail,
        ];
        if ($pointer !== null) {
            $error['source'] = ['pointer' => $pointer];
        }

        return $error;
    }
}
