<?php

use Illuminate\Support\Facades\Schedule;

if (demo()) {
    Schedule::command('tec:data-reset')->hourly()->withoutOverlapping(5);
} else {
    Schedule::command('backup:clean')->daily()->at('12:50');
    Schedule::command('backup:run --only-db')->daily()->at('01:00');
}
