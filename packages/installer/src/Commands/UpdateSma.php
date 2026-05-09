<?php

namespace Tecdiary\Installer\Commands;

use Exception;
use ZipArchive;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\MPS\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class UpdateSma extends Command
{
    protected $composer = 0;

    protected $description = 'Update module (SMA) and packages';

    protected $signature = 'tec:update {--now} {--c} {--force}';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        set_time_limit(1200);
        if (! $this->option('force') && ! $this->confirm('Have you backup the files & database?')) {
            $this->line('Please backup your files & database then try again.');

            return 1;
        }

        $this->composer = $this->option('c');
        $this->updateNow = $this->option('now');
        if (! $this->composer) {
            if ($this->confirm('Do you want to update composer packages too?')) {
                $this->composer = 1;
            }
        }

        return $this->update();
    }

    private function update()
    {
        $now = now()->startOfDay();
        $time = get_settings('auto_update_time');
        $next = isset($time->checked_at) ? Carbon::parse($time->checked_at)->addDays(6)->startOfDay() : now()->subDay()->startOfDay();
        if ($this->updateNow || $now->greaterThan($next)) {
            $modules = get_module();
            if ($modules['sma']) {
                if ($time) {
                    $time->checked_at = $next->toDateString();
                }
                $this->callSilently('down');
                $this->line('Checking if update available, please wait...');
                try {
                    // $url = 'http://be.tecdiary.net/api/v1/updates/check';
                    $url = 'http://be-tec-net.test/api/v1/updates/check';
                    $comp = json_decode(file_get_contents(base_path('composer.json')), true);
                    $postData = ['ver' => $comp['version'], 'key' => $modules['mps'], 'dom' => url('/')];
                    $response = Http::withHeaders(['Accept' => 'application/json'])->withOptions(['verify' => false])->get($url, $postData);

                    $data = $response->json();
                    if ($response->successful() && $data && $data['success']) {
                        if (! empty($data['updates'])) {
                            $this->line('Update available, installing now...');
                            $this->updateModule($data['updates']);

                            if ($modules['pos']['key'] && $modules['pos']['enabled']) {
                                $postData = ['ver' => $comp['version'], 'key' => $modules['pos']['key'], 'dom' => url('/')];
                                $moduleResponse = Http::withHeaders(['Accept' => 'application/json'])->withOptions(['verify' => false])->get($url, $postData);
                                $moduleData = $moduleResponse->json();
                                if ($moduleResponse->successful() && $moduleData && $moduleData['success']) {
                                    $this->updateModule($moduleData['updates']);
                                }
                            }

                            if ($modules['shop']['key'] && $modules['shop']['enabled']) {
                                $postData = ['ver' => $comp['version'], 'key' => $modules['shop']['key'], 'dom' => url('/')];
                                $moduleResponse = Http::withHeaders(['Accept' => 'application/json'])->withOptions(['verify' => false])->get($url, $postData);
                                $moduleData = $moduleResponse->json();
                                if ($moduleResponse->successful() && $moduleData && $moduleData['success']) {
                                    $this->updateModule($moduleData['updates']);
                                }
                            }

                            $this->line('Running migrations now...');
                            Artisan::call('migrate --force');
                            $this->line(Artisan::output());

                            if ($this->composer) {
                                $this->line('Updating the composer packages now, this could take few minutes. Please wait...');
                                $exitCode = $this->callSilently('composer:update');
                                if ($exitCode) {
                                    logger()->error('Failed to update composer packages, please run `composer install` manually.');
                                    $this->error('Failed to update composer packages, please run `composer install` manually.');
                                }
                            }

                            Setting::updateOrCreate(['tec_key' => 'auto_update_time'], ['tec_value' => json_encode($time)]);
                            $this->info('Update completed! you are using the latest version now.');
                        } else {
                            $this->info($data['message'] ?? 'You are using the latest version.');
                        }
                    } else {
                        Setting::updateOrCreate(['tec_key' => 'auto_update_time'], ['tec_value' => json_encode($time)]);

                        $this->callSilently('up');
                        logger()->error($data['message'], ['errors' => $data['errors'] ?? []]);
                        $this->fail('Update request failed! ' . $data['message']);
                    }
                } catch (Exception $e) {
                    $this->callSilently('up');
                    $this->fail('Update failed, ' . $e->getMessage());
                }
            } else {
                $this->fail('Application key is not set, please contact developer with your license key. Thank you');
            }
        } else {
            $this->fail("Your settings don't allow to update the item at this time. Please use --now flag if you wish to update now.");
        }
    }

    private function updateModule($updates)
    {
        if ($updates) {
            foreach ($updates as $update) {
                try {
                    $this->line('Updating to version ' . $update['version'] . ', please wait...');
                    $path = Storage::disk('local')->putFileAs('updates', $update['url'], $update['filename']);
                    if (Storage::disk('local')->exists($path)) {
                        $filepath = Storage::disk('local')->path($path);
                        try {
                            $zip = new ZipArchive;
                            if ($zip->open($filepath) === true) {
                                $zip->extractTo(base_path());
                                $zip->close();
                                Storage::disk('local')->delete($path);
                                $this->info('Updated to version ' . $update['version']);
                            } else {
                                $this->callSilently('up');
                                Storage::disk('local')->delete($path);
                                $this->fail('Failed to extract the update file ' . $path);
                            }
                        } catch (Exception $e) {
                            $this->callSilently('up');
                            $this->fail('Update failed: ' . $e->getMessage());
                        }
                    } else {
                        $this->callSilently('up');
                        $this->fail('Failed to copy the update file ' . $path);
                    }
                } catch (Exception $e) {
                    $this->callSilently('up');
                    $this->fail('Update failed! ' . $e->getMessage());
                }
            }
        }
    }
}
