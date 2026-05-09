<?php

namespace Tecdiary\Installer\Commands;

use Exception;
use ZipArchive;
use Ramsey\Uuid\Uuid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class InstallModule extends Command
{
    protected $description = 'Install module for Stock Manager Advance';

    protected $signature = 'tec:install-module {module?} {license?}';

    public function handle()
    {
        $module = $this->argument('module');
        $license = $this->argument('license');

        if (! $module) {
            $module = $this->choice('Please provide module name', ['pos', 'shop'], 0);
        }
        if (! $license) {
            $license = $this->ask('Please provide license key');
        }
        $isValid = Uuid::isValid($license);
        if (! $isValid) {
            $this->error('Invalid license key');
            $license = $this->ask('Please provide the correct license key');
            $isValid = Uuid::isValid($license);
            if (! $isValid) {
                $this->error('Invalid license key!');
            }
        }
        if ($module && $license && $isValid) {
            $this->line("Installing {$module} module...");

            return $this->install($module, $license);
        }
    }

    private function install($name, $key)
    {
        set_time_limit(1200);
        try {
            $modules = get_module();
            if ($modules['sma'] ?? null) {
                $this->callSilently('down');
                try {
                    $comp = json_decode(file_get_contents(base_path('composer.json')), true);
                    $postData = [
                        'item_id'  => 101,
                        'key'      => $key,
                        'module'   => $name,
                        'cc_id'    => 4259689,
                        'domain'   => url('/'),
                        'main_key' => $modules['sma'],
                        'version'  => $comp['version'],
                    ];
                    $response = Http::withoutVerifying()->acceptJson()->post('http://be-tec-net.test/api/v1/module', $postData);
                    // $response = Http::withoutVerifying()->acceptJson()->post('https://be.tecdiary.net/api/v1/module', $postData);

                    $data = $response->json();
                    if ($response->successful() && $data && $data['success']) {
                        try {
                            $this->line('Please wait...');
                            $path = storage_path('app/' . $data['filename']);
                            file_put_contents($path, file_get_contents($data['url']));
                            if (file_exists($path)) {
                                try {
                                    $zip = new ZipArchive;
                                    if ($zip->open($path) === true) {
                                        $zip->extractTo(base_path());
                                        $zip->close();
                                        unlink($path);
                                        $this->info('Files are copied!');
                                    } else {
                                        unlink($path);
                                        $this->callSilently('up');
                                        $this->fail('Failed to extract the file.');
                                    }
                                } catch (Exception $e) {
                                    $this->callSilently('up');
                                    $this->fail($e->getMessage());
                                }
                            } else {
                                $this->error('Failed to copy the downloaded file ' . $path);
                            }
                        } catch (Exception $e) {
                            $this->callSilently('up');
                            $this->fail($e->getMessage());
                        }

                        $expression = DB::raw($data['sql']);
                        DB::unprepared($expression->getValue(DB::connection()->getQueryGrammar()));
                        $this->line('Database schema updated.');

                        // $this->callSilently('composer:update');
                        $this->info('Installation completed! Thank you!');
                    } else {
                        $this->callSilently('up');
                        $this->fail($data['message'] ?? 'The installation request has been failed with unknown server error.');
                    }
                } catch (Exception $e) {
                    $this->callSilently('up');
                    $this->fail('Connection Error: ' . $e->getMessage());
                }
            } else {
                $this->error('Application license key is not set, please contact developer with your license key/purchase code. Thank you');
            }
            $this->callSilently('up');
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
