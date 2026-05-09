<?php

namespace Modules\Shop\Console\Commands;

use Illuminate\Console\Command;

class ShopDbSeed extends Command
{
    protected $signature = 'shop:db-seed';

    protected $description = 'Seed the shop settings database';

    public function handle(): void
    {
        $this->call('db:seed', ['--force' => true, '--class' => 'Modules\\Shop\\Database\\Seeders\\ShopSettingSeeder']);
        $this->info('Shop settings seeded successfully.');
    }
}
