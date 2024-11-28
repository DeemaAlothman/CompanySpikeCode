<?php

namespace App\Exceptions;
use Illuminate\Auth\AuthenticationException;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // تحقق إذا كان الطلب API
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Authentication token is missing or invalid'
            ], 401);
        }
    
        // بدلاً من تحويل المستخدم إلى صفحة تسجيل الدخول
        return response()->json([
            'error' => 'Unauthorized',
            'message' => 'You need to log in to access this route.'
        ], 401);
    }
    
    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
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
}
