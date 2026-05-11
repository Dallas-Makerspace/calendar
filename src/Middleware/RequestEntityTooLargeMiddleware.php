<?php
namespace App\Middleware;

use Cake\Http\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Rejects requests that PHP couldn't fully parse for size reasons, so the
 * controller (and the user) never see a half-saved state or a misleading
 * "CSRF token mismatch."
 *
 *  - Total body over post_max_size: PHP drops $_POST and $_FILES entirely.
 *    Detected from the still-intact Content-Length header.
 *  - A single file over upload_max_filesize: PHP marks that file with
 *    UPLOAD_ERR_INI_SIZE and lets the rest of the request through.
 *    Detected by walking the parsed uploaded files.
 */
class RequestEntityTooLargeMiddleware
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        if (!in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            return $next($request, $response);
        }

        $contentLength = (int)$request->getHeaderLine('Content-Length');
        $postMaxBytes  = (int)ini_get('post_max_size') * 1048576;

        if ($postMaxBytes > 0 && $contentLength > $postMaxBytes) {
            $sentMb = round($contentLength / 1048576, 1);
            throw new HttpException(
                sprintf(
                    'Upload too large: your request was about %s MB, but the server limit is %d MB total per submission. '
                    . 'Reduce the total size of your attachments and try again.',
                    $sentMb,
                    $postMaxBytes / 1048576
                ),
                413
            );
        }

        $uploadedFiles = $request->getUploadedFiles();
        $tooLarge      = [];
        array_walk_recursive($uploadedFiles, function ($file) use (&$tooLarge) {
            if ($file instanceof UploadedFileInterface && $file->getError() === UPLOAD_ERR_INI_SIZE) {
                $tooLarge[] = $file->getClientFilename() ?: '(unnamed file)';
            }
        });

        if (!empty($tooLarge)) {
            $perFileMb = (int)ini_get('upload_max_filesize');
            throw new HttpException(
                sprintf(
                    'File too large: %s exceeds the per-file limit of %d MB. Reduce the file size and try again.',
                    implode(', ', $tooLarge),
                    $perFileMb
                ),
                413
            );
        }

        return $next($request, $response);
    }
}
