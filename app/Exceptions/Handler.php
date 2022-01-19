<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use PDOException;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \App\Exceptions\BusinessException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $errors         = null;
        $status_code    = Response::HTTP_BAD_REQUEST;
        $message        = $exception->getMessage();

        if (config('app.debug')) {
            $errors = [
                'class' => get_class($exception),
                'line' => $exception->getFile().':'.$exception->getLine(),
            ];
        }

        switch ($exception) {

            case $exception instanceof BusinessException:
                $code = $exception->getCode();
                $message = $message ?: Lang::transCode($code);

                break;

            case $exception instanceof AuthorizationException:
                // 401 Unauthorized
                $status_code = Response::HTTP_UNAUTHORIZED;
                break;

            case $exception instanceof ModelNotFoundException:
            case $exception instanceof NotFoundHttpException:
                // 404 not found
                $status_code = Response::HTTP_NOT_FOUND;
                break;

            case $exception instanceof MethodNotAllowedHttpException:
                // 405 not allowed method
                $status_code = Response::HTTP_METHOD_NOT_ALLOWED;
                $message     = 'Method Not Allowed';
                break;

            case $exception instanceof ValidationException:
                // 422 HTTP UNPROCESSABLE ENTITY
                $status_code = $exception->status;
                $errors      = $exception->errors();
                $message     = current(current($errors));
                break;

            case $exception instanceof PDOException:
            case $exception instanceof MassAssignmentException:
                // 500 http internal server error
                $status_code = Response::HTTP_INTERNAL_SERVER_ERROR;
                break;

            case $exception instanceof MaintenanceModeException:
                // 503 service unavailable
                $status_code = Response::HTTP_SERVICE_UNAVAILABLE;
                break;

            case $exception instanceof HttpException:
                $status_code = $exception->getStatusCode();
                // 401 Unauthorized
                break;

            default:
                break;
        }

        empty($message) && $message = Response::$statusTexts[$status_code];

        $data = [
            'code' => $code ?? $status_code,
            'msg'  => $message,
        ];

        if (isset($errors)) {
            $data['errors'] = $errors;
        }

        return response()->json($data, $status_code);

    }
}
