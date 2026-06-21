<?php

namespace App\Http\Controllers;

use App\Models\RabNotification;

class RabNotificationController extends Controller
{
    /**
     * Membuka notifikasi, menandainya sudah dibaca, dan mengarahkan pengguna ke halaman RAB yang bersangkutan.
     */
    public function open(RabNotification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $notification->markAsRead();
        $rab = $notification->rab;

        $route = 'rab.index';
        if (auth()->user()->isAdmin() && $rab?->payment()->exists()) {
            $route = 'admin.input-nota.index';
        } elseif (auth()->user()->isManajer()) {
            $route = 'manajer.rab.index';
        } elseif (auth()->user()->isDirektur()) {
            $route = 'direktur.rab.index';
        }

        return redirect()->route($route, [
            'status' => $rab?->status?->value,
            'open_rab_id' => $rab?->id,
        ]);
    }
}
