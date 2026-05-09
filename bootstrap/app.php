<?php

use Inertia\Inertia;
use App\Tec\Core\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Application;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Encryption\MissingAppKeyException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['cart_id', 'language', 'rtl_support']);

        $middleware->web(append: [
            App\Http\Middleware\Language::class,
            App\Http\Middleware\Impersonate::class,
            App\Http\Middleware\HandleInertiaRequests::class,
            Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'language'  => App\Http\Middleware\Language::class,
            'store'     => App\Http\Middleware\SelectStore::class,
            'register'  => App\Http\Middleware\OpenRegister::class,
            'installed' => Tecdiary\Installer\Http\Middleware\RedirectIfNotInstalled::class,
        ]);
    })
    ->withCommands([__DIR__ . '/../app/Tec/Console/Commands'])
    ->withEvents(discover: [__DIR__ . '/../app/Tec/Listeners'])
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if ($exception instanceof MissingAppKeyException) {
                if (! File::exists(base_path('.env'))) {
                    File::copy(base_path('.env.example'), base_path('.env'));
                }
                cache()->forget('sma_modules');

                return redirect()->to('/install');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'errors'  => method_exists($exception, 'errors') ? $exception->errors() : null,
                ], $response->getStatusCode());
            }

            if (in_array($response->getStatusCode(), [500, 403])) {
                return back()->with([
                    'error' => $response->getStatusCode() . ': ' . $exception->getMessage(),
                ]);
            } elseif (in_array($response->getStatusCode(), [503, 404])) {
                return Inertia::render('Error', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            } elseif ($response->getStatusCode() === 419) {
                return back()->with([
                    'message' => __('The page expired, please try again.'),
                ]);
            }

            return $response;
        });
    })->create();

$app->singleton('router', fn ($app) => new Router($app['events'], $app));

return $app;
