<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Convert validation and duplicate errors to user-friendly redirects with toast/SweetAlert message.
     */
    public function render($request, Throwable $e)
    {
        // Validation errors (e.g. unique rule) -> redirect back with friendly toast message
        if ($e instanceof ValidationException) {
            $errors = $e->errors();
            $firstMessage = is_array($errors) && !empty($errors)
                ? reset($errors)
                : [];
            $message = is_array($firstMessage) ? implode(' ', $firstMessage) : $e->getMessage();
            $message = $this->makeFriendlyDuplicateMessage($message);
            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'errors' => $errors], 422);
            }
            return redirect()->back()
                ->withInput($request->except($this->dontFlash))
                ->with('toast_error', $message)
                ->withErrors($errors);
        }

        // Database duplicate key (e.g. race condition or missing validation)
        if ($e instanceof QueryException && $this->isDuplicateKeyError($e)) {
            $message = 'This value already exists. Please use a different one.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }
            return redirect()->back()
                ->withInput($request->except($this->dontFlash))
                ->with('toast_error', $message);
        }

        return parent::render($request, $e);
    }

    /**
     * Check if the query exception is a duplicate key / unique constraint violation.
     */
    protected function isDuplicateKeyError(QueryException $e): bool
    {
        $code = $e->getCode();
        $message = $e->getMessage();
        // MySQL: 1062 = Duplicate entry, SQLite: 19 = UNIQUE constraint failed, PostgreSQL: 23505 = unique_violation
        return $code == 1062
            || $code == 19
            || $code == 23505
            || stripos($message, 'Duplicate entry') !== false
            || stripos($message, 'UNIQUE constraint') !== false
            || stripos($message, 'unique_violation') !== false;
    }

    /**
     * Convert Laravel's default unique validation message to a user-friendly one.
     */
    protected function makeFriendlyDuplicateMessage(string $message): string
    {
        if (stripos($message, 'already been taken') !== false || stripos($message, 'has already been taken') !== false) {
            return 'This value is already in use. Please enter a different one.';
        }
        if (stripos($message, 'unique') !== false) {
            return 'This value already exists. Please use a different one.';
        }
        return $message;
    }
}
