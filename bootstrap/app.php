<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsClient;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/admin.php'));
            Route::middleware('web')->group(base_path('routes/portal.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias(['admin' => EnsureUserIsAdmin::class, 'client' => EnsureUserIsClient::class]);

        // PayPal posts IPN messages server-to-server, so they can never carry a CSRF token.
        $middleware->preventRequestForgery(except: ['paypal/ipn']);

        $middleware->trustProxies(at: '*');

        // Set and read by the browser only (cookie banner choice).
        $middleware->encryptCookies(except: ['cookie_consent']);

        $middleware->redirectGuestsTo(fn (Request $request) => route($request->is('portal', 'portal/*') ? 'portal.login' : 'login'));
        $middleware->redirectUsersTo(fn (Request $request) => route($request->user()?->is_admin ? 'admin.dashboard' : 'portal.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $status = $response->getStatusCode();

            if (! app()->hasDebugModeEnabled() && in_array($status, [403, 404, 410, 419, 429, 500, 503], true)) {
                if ($status === 419) {
                    return back()->with('error', 'The page expired, please try again.');
                }

                return Inertia::render('Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
