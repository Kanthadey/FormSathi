<?php

namespace App\Support;

use Illuminate\Http\Request;

/** Whether the user entered their PIN recently enough to see papers. */
class PinGate
{
    public static function isFresh(Request $request): bool
    {
        $confirmedAt = $request->session()->get('pin_confirmed_at');

        return $confirmedAt && now()->timestamp - $confirmedAt <= config('formsathi.pin.view_papers_minutes') * 60;
    }
}
