<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\Form;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            EnsureUserIsActive::class,
        ]);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            if (! $request->isMethod('post') || ! $request->is('formulario/*')) {
                return null;
            }

            $form = $request->route('form');
            $formId = $form instanceof Form ? $form->getKey() : $form;

            if (! is_string($formId) && ! is_int($formId)) {
                $formId = $request->segment(2);
            }

            if (! is_string($formId) && ! is_int($formId)) {
                return null;
            }

            return redirect()
                ->to(URL::signedRoute('public.forms.fill', [
                    'form' => $formId,
                    'upload_error' => 1,
                ]));
        });
    })->create();
