<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Nnjeim\World\Actions\SeedAction;

class WorldSeeder extends Seeder
{
    public function run()
    {
        // $this->call([SeedAction::class]);

        $path = (base_path('database/schema/world.sql'));

        $expression = DB::raw(file_get_contents($path));
        DB::unprepared($expression->getValue(DB::connection()->getQueryGrammar()));
    }
}
