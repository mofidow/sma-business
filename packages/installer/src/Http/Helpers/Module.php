<?php

use Tecdiary\Installer\Install;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Modules\Shop\Console\Commands\ShopSetting;

// read modules file
if (! function_exists('get_module')) {
    function get_module($name = null)
    {
        if ($name) {
            return true;
        }

        return ['sma' => true, 'pos' => ['key' => 'unlocked', 'enabled' => true], 'shop' => ['key' => 'unlocked', 'enabled' => true]];
    }
}

// write modules file
if (! function_exists('write_module')) {
    function write_module(array $data)
    {
        if (! ($data['sma'] ?? null)) {
            throw new Exception('Please provide sma key.');
        }

        $data['pos'] ??= ['key' => null, 'enabled' => false];
        $data['shop'] ??= ['key' => null, 'enabled' => false];

        cache()->forget('sma_modules');

        return Storage::disk('local')->put('modules.json', json_encode($data, JSON_PRETTY_PRINT));
    }
}

// disable module
if (! function_exists('disable_module')) {
    function disable_module($name)
    {
        if (! in_array($name, ['pos', 'shop'])) {
            throw new Exception('No module found with name ' . $name);
        }

        $modules = get_module();
        $modules[$name]['enabled'] = false;

        return write_module($modules);
    }
}

// enable module
if (! function_exists('enable_module')) {
    function enable_module($name, $key)
    {
        if (! in_array($name, ['pos', 'shop'])) {
            throw new Exception('No module found with name ' . $name);
        }

        if ($name == 'shop' && ! File::exists(base_path('modules/Shop'))) {
            throw new Exception('Shop module files are missing! Please upload the module files first.');
        }

        if ($name == 'pos' && (! File::exists(base_path('app/Http/Controllers/Sma/Pos')) && ! File::exists(base_path('app/Models/Sma/Pos')))) {
            throw new Exception('POS module files are missing! Please upload the module files first.');
        }

        if ($name == 'shop') {
            (new ShopSetting())->handle();
        }

        return true;
    }
}

// install module
if (! function_exists('install_module')) {
    function install_module($name, $key)
    {
        if (! in_array($name, ['pos', 'shop'])) {
            throw new Exception('No module found with name ' . $name);
        }

        $user = auth()->user();
        if ($user && $user->hasRole('Super Admin')) {
            try {
                $e = Artisan::call("sma:install-module {$name} {$key}");

                if ($e === 0) {
                    return enable_module($name, $key);
                }

                throw new Exception(Artisan::output());
            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        return false;
    }
}
