<?php

namespace Modules\Shop;

use Livewire\Livewire;
use App\Models\Sma\Product\Track;
use Modules\Shop\Tec\ShopComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Shop\Http\Middleware\ShopMode;
use Modules\Shop\Tec\Jobs\NotifyBackInStock;
use Modules\Shop\Console\Commands\ShopDbSeed;
use App\Http\Middleware\HandleInertiaRequests;
use Modules\Shop\Console\Commands\ShopSetting;
use Modules\Shop\Http\Middleware\PageShortCode;

class ShopServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (get_module('shop')) {
            $this->registerViews();
            $this->registerRoutes();
            $this->registerCommands();
            $this->registerMiddleware();
            $this->registerLivewireComponents();

            require_once __DIR__ . '/Tec/functions.php';
            // $this->loadJsonTranslationsFrom(__DIR__ . '/lang');
            $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
            $this->registerObservers();

            if (app()->environment('local', 'testing') || demo()) {
                $this->loadMigrationsFrom(__DIR__ . '/Database/migrations/dump');
            }
        }
    }

    private function registerViews(): void
    {
        View::composer('*', ShopComposer::class);
        View::addNamespace('shop', __DIR__ . '/resources');
        Blade::anonymousComponentPath(__DIR__ . '/resources/components');
        Blade::componentNamespace('Modules\\Shop\\Http\\Components', 'shop');
    }

    private function registerLivewireComponents(): void
    {
        config([
            'livewire.component_layout' => 'shop::components.layouts.shop',
            'livewire.view_path'        => base_path('modules/Shop/resources'),
        ]);

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/shop/livewire/update', $handle)
                ->middleware(['web']);
        });

        // Register all Livewire components from the Shop module
        $this->registerLivewireComponentsFromDirectory(
            base_path('modules/Shop/Http/Livewire'),
            'Modules\\Shop\\Http\\Livewire'
        );
        $this->registerLivewireComponentsFromDirectory(
            base_path('modules/Shop/Http/Jet'),
            'Modules\\Shop\\Http\\Jet',
            'shop.jet'
        );
    }

    private function registerLivewireComponentsFromDirectory(string $directory, string $namespace, string $prefix = ''): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (new \DirectoryIterator($directory) as $file) {
            if ($file->isDot()) {
                continue;
            }

            if ($file->isDir()) {
                $subPrefix = $prefix ? $prefix . '.' . str($file->getFilename())->kebab()->toString() : str($file->getFilename())->kebab()->toString();
                $this->registerLivewireComponentsFromDirectory(
                    $file->getPathname(),
                    $namespace . '\\' . $file->getFilename(),
                    $subPrefix
                );
            } elseif ($file->getExtension() === 'php') {
                $className = str($file->getFilename())->before('.php')->toString();
                $componentName = $prefix ? $prefix . '.' . str($className)->kebab()->toString() : str($className)->kebab()->toString();
                $fullClass = $namespace . '\\' . $className;

                if (class_exists($fullClass) && is_subclass_of($fullClass, \Livewire\Component::class)) {
                    Livewire::component($componentName, $fullClass);
                }
            }
        }
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ShopDbSeed::class,
                ShopSetting::class,
            ]);
        }
    }

    private function registerMiddleware(): void
    {
        $router = app('router');
        $router->aliasMiddleware('shopmode', ShopMode::class);
        $router->aliasMiddleware('shortcode', PageShortCode::class);
    }

    private function registerObservers(): void
    {
        Track::created(function (Track $track) {
            if ($track->value <= 0 || ! $track->product_id) {
                return;
            }

            $previousBalance = $track->trackable->tracks()
                ->where('id', '<', $track->id)
                ->sum('value');

            if ($previousBalance <= 0) {
                NotifyBackInStock::dispatch($track->product_id, $track->variation_id ?: null);
            }
        });
    }

    private function registerRoutes(): void
    {
        if (get_module('shop')) {
            Route::group([
                'middleware'          => ['installed', 'web'],
                'namespace'           => '\\Modules\\Shop\\Http',
                'excluded_middleware' => [HandleInertiaRequests::class],
            ], function () {
                $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
            });
        }
    }
}
