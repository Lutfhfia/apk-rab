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
     * Approve RAB by Manajer Keuangan.
     */
    public function approveByManager(Request $request, Rab $rab)
    {
        if ($rab->status === RabStatus::DISETUJUI_MANAJER || $rab->status === RabStatus::DISETUJUI) {
            return back()->with('info', 'RAB ini sudah disetujui sebelumnya.');
        }

        if ($rab->status !== RabStatus::DIAJUKAN) {
            return back()->with('error', 'RAB ini tidak dalam status yang dapat disetujui.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            RabApproval::create([
                'rab_id' => $rab->id,
                'user_id' => auth()->id(),
                'role' => 'manajer_keuangan',
                'approval_level' => 'manager',
                'status' => ApprovalStatus::APPROVED,
                'notes' => $request->notes,
                'approved_at' => now(),
            ]);

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
            $rab->addDiscussionNote(
                auth()->id(),
                $request->notes ?: 'Rincian sudah sesuai, saya teruskan ke Direktur.'
            );
            $rab->notifyRole(
                UserRole::DIREKTUR->value,
                'RAB perlu diperiksa Direktur',
                "Manager Keuangan menyetujui RAB {$rab->rab_number} dan meneruskan pengajuan kepada Direktur."
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
     * Approve RAB by Direktur.
     */
    public function approveByDirector(Request $request, Rab $rab)
    {
        if ($rab->status === RabStatus::DISETUJUI) {
            return back()->with('info', 'RAB ini sudah disetujui sebelumnya.');
        }

        if ($rab->status !== RabStatus::DISETUJUI_MANAJER) {
            return back()->with('error', 'RAB ini belum disetujui oleh Manajer Keuangan.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            RabApproval::create([
                'rab_id' => $rab->id,
                'user_id' => auth()->id(),
                'role' => 'direktur',
                'approval_level' => 'director',
                'status' => ApprovalStatus::APPROVED,
                'notes' => $request->notes,
                'approved_at' => now(),
            ]);

            $rab->update([
                'status' => RabStatus::DISETUJUI,
                'approved_by_director_at' => now(),
            ]);

            AuditLog::log(
                'approve_director',
                "RAB {$rab->rab_number} disetujui oleh Direktur " . auth()->user()->name,
                rabId: $rab->id
            );

            $rab->addDiscussionNote(
                auth()->id(),
                $request->notes ?: 'Disetujui. Silakan diproses pembayaran.'
            );
            $rab->notifyUser(
                $rab->user_id,
                'RAB disetujui Direktur',
                "Direktur menyetujui RAB {$rab->rab_number}. RAB siap diproses pembayaran."
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
     * Reject RAB.
     */
    public function reject(Request $request, Rab $rab)
    {
        if (!in_array($rab->status, [RabStatus::DIAJUKAN, RabStatus::DISETUJUI_MANAJER])) {
            return back()->with('error', 'RAB ini tidak dalam status yang dapat ditolak.');
        }

        $request->validate([
            'notes' => 'required|string|max:1000',
        ], [
            'notes.required' => 'Catatan penolakan wajib diisi.',
        ]);

        $userRole = auth()->user()->role->value;

        DB::beginTransaction();

        try {
            RabApproval::create([
                'rab_id' => $rab->id,
                'user_id' => auth()->id(),
                'role' => $userRole,
                'approval_level' => $userRole === 'manajer_keuangan' ? 'manager' : 'director',
                'status' => ApprovalStatus::REJECTED,
                'notes' => $request->notes,
                'rejected_at' => now(),
            ]);

            $rab->update([
                'status' => RabStatus::DITOLAK,
            ]);

            AuditLog::log(
                'reject_rab',
                "RAB {$rab->rab_number} ditolak oleh " . auth()->user()->name . ". Catatan: " . $request->notes,
                rabId: $rab->id
            );

            $rab->addDiscussionNote(auth()->id(), $request->notes);
            $rab->notifyUser(
                $rab->user_id,
                'RAB dikembalikan untuk diperbaiki',
                "RAB {$rab->rab_number} dikembalikan untuk diperbaiki. Silakan cek catatan."
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
