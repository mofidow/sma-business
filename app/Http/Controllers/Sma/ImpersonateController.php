<?php

namespace App\Http\Controllers\Sma;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ImpersonateController extends Controller
{
    public function start(Request $request, User $user)
    {
        $result = false;
        if ($request->user()?->canImpersonate() && $user->canBeImpersonated()) {
            $user->startImpersonating();
            log_activity('Start impersonating as ' . $user->name, $user, $request->user());
            $result = true;
        }

        return to_route('dashboard')->with('message', __('{record} has been {action}.', ['record' => __('Impersonation'), 'action' => $result ? __('started') : __('failed')]));
    }

    public function stop(Request $request)
    {
        if ($acting_user = $request->user()->impersonatedAs()) {
            log_activity('Stop impersonating as ' . $acting_user['name'], [$acting_user], $request->user());
        }
        $request->user()->stopImpersonating();

        return to_route('dashboard')->with('message', __('{record} has been {action}.', ['record' => __('Impersonation'), 'action' => __('stopped')]));
    }
}
