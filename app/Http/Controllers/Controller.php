<?php

namespace App\Http\Controllers;

use App\Tec\Traits\Authorizable;
use Illuminate\Routing\Controller as RoutingController;

abstract class Controller extends RoutingController
{
    use Authorizable;
}
