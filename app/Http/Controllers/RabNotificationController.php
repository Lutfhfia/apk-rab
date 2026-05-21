<?php

namespace App\Http\Controllers;

use App\Models\RabNotification;

class RabNotificationController extends Controller
{
    public function open(RabNotification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $notification->markAsRead();
        $rab = $notification->rab;

        $route = 'rab.index';
        if (auth()->user()->isManajer()) {
            $route = 'manajer.rab.index';
        }
        if (auth()->user()->isDirektur()) {
            $route = 'direktur.rab.index';
        }

        return redirect()->route($route, [
            'status' => $rab?->status?->value,
            'open_rab_id' => $rab?->id,
        ]);
    }
}
