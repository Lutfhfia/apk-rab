<?php

namespace App\Http\Controllers;

use App\Models\Rab;
use App\Models\RabApproval;
use App\Models\AuditLog;
use App\Enums\RabStatus;
use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalRabController extends Controller
{
    /**
     * Menyetujui RAB oleh Manajer Keuangan.
     */
    public function approveByManager(Request $request, Rab $rab)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Mengunci baris data RAB di database untuk mencegah race condition (tumburan data)
            $rab = Rab::where('id', $rab->id)->lockForUpdate()->first();

            if ($rab->status === RabStatus::DISETUJUI_MANAJER || $rab->status === RabStatus::DISETUJUI) {
                DB::rollBack();
                return back()->with('info', 'RAB ini sudah disetujui sebelumnya.');
            }

            if ($rab->status !== RabStatus::DIAJUKAN) {
                DB::rollBack();
                return back()->with('error', 'RAB ini tidak dalam status yang dapat disetujui.');
            }

            RabApproval::create([
                'rab_id' => $rab->id,
                'user_id' => auth()->id(),
                'role' => 'manajer_keuangan',
                'approval_level' => 'manager',
                'status' => ApprovalStatus::APPROVED,
                'notes' => $request->notes,
                'approved_at' => now(),
            ]);

            if ($request->filled('notes')) {
                \App\Models\RabDiscussion::create([
                    'rab_id' => $rab->id,
                    'user_id' => auth()->id(),
                    'message' => $request->notes,
                ]);
            }

            $rab->update([
                'status' => RabStatus::DISETUJUI_MANAJER,
                'approved_by_manager_at' => now(),
            ]);

            AuditLog::log(
                'approve_manager',
                "RAB {$rab->rab_number} disetujui oleh Manajer Keuangan " . auth()->user()->name,
                rabId: $rab->id
            );

            $rab->loadMissing('expenseType');

            $rab->notifyRole(
                UserRole::DIREKTUR->value,
                'RAB perlu diperiksa Direktur',
                "Manajer Keuangan menyetujui RAB {$rab->rab_number} dan meneruskan pengajuan kepada Direktur.",
                null,
                fn ($direktur) => $rab->buildWhatsAppSubmissionMessage(route('direktur.rab.show', $rab), $direktur)
            );

            DB::commit();

            return back()
                ->with('success', 'RAB berhasil disetujui dan diteruskan ke Direktur.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Menyetujui RAB oleh Direktur.
     */
    public function approveByDirector(Request $request, Rab $rab)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Mengunci baris data RAB di database untuk mencegah race condition (tumburan data)
            $rab = Rab::where('id', $rab->id)->lockForUpdate()->first();

            if ($rab->status === RabStatus::DISETUJUI) {
                DB::rollBack();
                return back()->with('info', 'RAB ini sudah disetujui sebelumnya.');
            }

            if ($rab->status !== RabStatus::DISETUJUI_MANAJER) {
                DB::rollBack();
                return back()->with('error', 'RAB ini belum disetujui oleh Manajer Keuangan.');
            }

            RabApproval::create([
                'rab_id' => $rab->id,
                'user_id' => auth()->id(),
                'role' => 'direktur',
                'approval_level' => 'director',
                'status' => ApprovalStatus::APPROVED,
                'notes' => $request->notes,
                'approved_at' => now(),
            ]);

            if ($request->filled('notes')) {
                \App\Models\RabDiscussion::create([
                    'rab_id' => $rab->id,
                    'user_id' => auth()->id(),
                    'message' => $request->notes,
                ]);
            }

            $rab->update([
                'status' => RabStatus::DISETUJUI,
                'approved_by_director_at' => now(),
            ]);

            AuditLog::log(
                'approve_director',
                "RAB {$rab->rab_number} disetujui oleh Direktur " . auth()->user()->name,
                rabId: $rab->id
            );


            $rab->notifyUser(
                $rab->user_id,
                'RAB disetujui Direktur',
                "Direktur menyetujui RAB {$rab->rab_number}. RAB siap diproses pembayaran.",
                "*RAB Disetujui Direktur*\n\nDirektur telah menyetujui pengajuan RAB *{$rab->rab_number}*. RAB sudah siap untuk diproses pembayarannya.\nSilakan cek sistem:\n" . route('rab.show', $rab)
            );

            DB::commit();

            return back()
                ->with('success', 'RAB berhasil disetujui! RAB siap diproses pembayaran.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Menolak RAB dan mengembalikannya ke Admin.
     */
    public function reject(Request $request, Rab $rab)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ], [
            'notes.required' => 'Catatan penolakan wajib diisi.',
        ]);

        $userRole = auth()->user()->role->value;

        DB::beginTransaction();

        try {
            // Mengunci baris data RAB di database untuk mencegah race condition (tumburan data)
            $rab = Rab::where('id', $rab->id)->lockForUpdate()->first();

            if (!in_array($rab->status, [RabStatus::DIAJUKAN, RabStatus::DISETUJUI_MANAJER])) {
                DB::rollBack();
                return back()->with('error', 'RAB ini tidak dalam status yang dapat ditolak.');
            }

            RabApproval::create([
                'rab_id' => $rab->id,
                'user_id' => auth()->id(),
                'role' => $userRole,
                'approval_level' => $userRole === 'manajer_keuangan' ? 'manager' : 'director',
                'status' => ApprovalStatus::REJECTED,
                'notes' => $request->notes,
                'rejected_at' => now(),
            ]);

            if ($request->filled('notes')) {
                \App\Models\RabDiscussion::create([
                    'rab_id' => $rab->id,
                    'user_id' => auth()->id(),
                    'message' => $request->notes,
                ]);
            }

            $rab->update([
                'status' => RabStatus::DITOLAK,
            ]);

            AuditLog::log(
                'reject_rab',
                "RAB {$rab->rab_number} ditolak oleh " . auth()->user()->name . ". Catatan: " . $request->notes,
                rabId: $rab->id
            );


            $rab->notifyUser(
                $rab->user_id,
                'RAB dikembalikan untuk diperbaiki',
                "RAB {$rab->rab_number} dikembalikan untuk diperbaiki. Silakan cek catatan.",
                "*RAB Ditolak/Dikembalikan*\n\nRAB *{$rab->rab_number}* telah dikembalikan oleh " . auth()->user()->name . " dengan catatan penolakan:\n_" . $request->notes . "_\n\nSilakan cek aplikasi untuk memperbaikinya:\n" . route('rab.show', $rab)
            );

            DB::commit();

            return back()
                ->with('success', 'RAB telah ditolak dan dikembalikan ke Admin untuk revisi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
