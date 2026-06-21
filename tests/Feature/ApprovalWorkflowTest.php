<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Rab;
use App\Models\ExpenseType;
use App\Models\RabApproval;
use App\Enums\UserRole;
use App\Enums\RabStatus;
use App\Enums\ApprovalStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manajer;
    private User $direktur;
    private ExpenseType $expenseType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::ADMIN_KEUANGAN,
            'is_active' => true,
        ]);

        $this->manajer = User::factory()->create([
            'role' => UserRole::MANAJER_KEUANGAN,
            'is_active' => true,
        ]);

        $this->direktur = User::factory()->create([
            'role' => UserRole::DIREKTUR,
            'is_active' => true,
        ]);

        $this->expenseType = ExpenseType::create([
            'code' => 'petty_cash',
            'name' => 'Petty Cash',
            'description' => 'Pengeluaran kecil',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        putenv('FONNTE_TOKEN');
        unset($_ENV['FONNTE_TOKEN'], $_SERVER['FONNTE_TOKEN']);

        parent::tearDown();
    }

    private function createRab(RabStatus $status, string $rabNumber = '001/RAB/SBK/VI/2026'): Rab
    {
        return Rab::create([
            'rab_number' => $rabNumber,
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseType->id,
            'description' => 'Test RAB',
            'status' => $status,
            'total_amount' => 500000,
        ]);
    }

    // ── Manager Approval ──

    private function fakeFonnteToken(): void
    {
        config(['logging.default' => 'null']);
        putenv('FONNTE_TOKEN=test-token');
        $_ENV['FONNTE_TOKEN'] = 'test-token';
        $_SERVER['FONNTE_TOKEN'] = 'test-token';
    }

    public function test_admin_submit_sends_personalized_whatsapp_to_manajer(): void
    {
        $this->fakeFonnteToken();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $this->admin->update(['name' => 'Mba Mery']);
        $this->manajer->update([
            'name' => 'Pak Alpian',
            'phone_number' => '08123456789',
        ]);

        $rab = $this->createRab(RabStatus::DRAFT);

        $this->actingAs($this->admin)->post("/rab/{$rab->id}/submit");

        Http::assertSent(function ($request) use ($rab) {
            $message = $request['message'];

            return $request->url() === 'https://api.fonnte.com/send'
                && $request['target'] === '628123456789'
                && str_contains($message, 'Yth. Pak Alpian')
                && str_contains($message, 'Pengajuan Pembayaran Petty Cash - Juni 2026')
                && str_contains($message, route('manajer.rab.show', $rab))
                && str_contains($message, "Hormat saya,\n\nMba Mery");
        });
    }

    public function test_manager_approval_sends_detailed_personalized_whatsapp_to_direktur(): void
    {
        $this->fakeFonnteToken();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $this->admin->update(['name' => 'Mba Mery']);
        $this->direktur->update([
            'name' => 'Pak Direktur',
            'phone_number' => '08111111111',
        ]);

        $rab = $this->createRab(RabStatus::DIAJUKAN);

        $this->actingAs($this->manajer)->post("/rab/{$rab->id}/approve-manager", [
            'notes' => 'Disetujui untuk proses lebih lanjut.',
        ]);

        Http::assertSent(function ($request) use ($rab) {
            $message = $request['message'];

            return $request->url() === 'https://api.fonnte.com/send'
                && $request['target'] === '628111111111'
                && str_contains($message, 'Yth. Pak Direktur')
                && str_contains($message, 'Pengajuan Pembayaran Petty Cash - Juni 2026')
                && str_contains($message, 'Mohon pemeriksaan dan persetujuan Bapak')
                && str_contains($message, route('direktur.rab.show', $rab))
                && str_contains($message, "Hormat saya,\n\nMba Mery");
        });
    }

    public function test_manajer_can_approve_submitted_rab(): void
    {
        $rab = $this->createRab(RabStatus::DIAJUKAN);

        $response = $this->actingAs($this->manajer)->post("/rab/{$rab->id}/approve-manager", [
            'notes' => 'Disetujui untuk proses lebih lanjut.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rab->refresh();
        $this->assertEquals(RabStatus::DISETUJUI_MANAJER, $rab->status);
        $this->assertNotNull($rab->approved_by_manager_at);

        // Verify approval record created
        $this->assertDatabaseHas('rab_approvals', [
            'rab_id' => $rab->id,
            'user_id' => $this->manajer->id,
            'role' => 'manajer_keuangan',
            'status' => ApprovalStatus::APPROVED->value,
        ]);

        // Verify audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'approve_manager',
            'rab_id' => $rab->id,
        ]);

        // Verify notification sent to Direktur
        $this->assertDatabaseHas('rab_notifications', [
            'user_id' => $this->direktur->id,
            'rab_id' => $rab->id,
        ]);
    }

    public function test_manajer_cannot_approve_draft_rab(): void
    {
        $rab = $this->createRab(RabStatus::DRAFT);

        $response = $this->actingAs($this->manajer)->post("/rab/{$rab->id}/approve-manager", [
            'notes' => 'Test',
        ]);

        $response->assertSessionHas('error');

        $rab->refresh();
        $this->assertEquals(RabStatus::DRAFT, $rab->status);
    }

    public function test_manajer_cannot_approve_already_approved_rab(): void
    {
        $rab = $this->createRab(RabStatus::DISETUJUI_MANAJER);

        $response = $this->actingAs($this->manajer)->post("/rab/{$rab->id}/approve-manager", [
            'notes' => 'Test',
        ]);

        $response->assertSessionHas('info');
    }

    // ── Director Approval ──

    public function test_direktur_can_approve_manager_approved_rab(): void
    {
        $rab = $this->createRab(RabStatus::DISETUJUI_MANAJER, '002/RAB/SBK/VI/2026');

        $response = $this->actingAs($this->direktur)->post("/rab/{$rab->id}/approve-director", [
            'notes' => 'Disetujui, proses pembayaran.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rab->refresh();
        $this->assertEquals(RabStatus::DISETUJUI, $rab->status);
        $this->assertNotNull($rab->approved_by_director_at);

        $this->assertDatabaseHas('rab_approvals', [
            'rab_id' => $rab->id,
            'user_id' => $this->direktur->id,
            'role' => 'direktur',
            'status' => ApprovalStatus::APPROVED->value,
        ]);

        // Verify notification sent back to Admin (the RAB creator)
        $this->assertDatabaseHas('rab_notifications', [
            'user_id' => $this->admin->id,
            'rab_id' => $rab->id,
        ]);
    }

    public function test_direktur_cannot_approve_submitted_rab_directly(): void
    {
        $rab = $this->createRab(RabStatus::DIAJUKAN, '003/RAB/SBK/VI/2026');

        $response = $this->actingAs($this->direktur)->post("/rab/{$rab->id}/approve-director", [
            'notes' => 'Test',
        ]);

        $response->assertSessionHas('error');

        $rab->refresh();
        $this->assertEquals(RabStatus::DIAJUKAN, $rab->status);
    }

    public function test_direktur_cannot_approve_already_fully_approved_rab(): void
    {
        $rab = $this->createRab(RabStatus::DISETUJUI, '004/RAB/SBK/VI/2026');

        $response = $this->actingAs($this->direktur)->post("/rab/{$rab->id}/approve-director", [
            'notes' => 'Test',
        ]);

        $response->assertSessionHas('info');
    }

    // ── Rejection ──

    public function test_manajer_can_reject_submitted_rab(): void
    {
        $rab = $this->createRab(RabStatus::DIAJUKAN, '005/RAB/SBK/VI/2026');

        $response = $this->actingAs($this->manajer)->post("/rab/{$rab->id}/reject-manager", [
            'notes' => 'Data rincian tidak lengkap, mohon diperbaiki.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rab->refresh();
        $this->assertEquals(RabStatus::DITOLAK, $rab->status);

        $this->assertDatabaseHas('rab_approvals', [
            'rab_id' => $rab->id,
            'status' => ApprovalStatus::REJECTED->value,
        ]);

        // Verify notification sent to Admin
        $this->assertDatabaseHas('rab_notifications', [
            'user_id' => $this->admin->id,
            'rab_id' => $rab->id,
        ]);
    }

    public function test_direktur_can_reject_manager_approved_rab(): void
    {
        $rab = $this->createRab(RabStatus::DISETUJUI_MANAJER, '006/RAB/SBK/VI/2026');

        $response = $this->actingAs($this->direktur)->post("/rab/{$rab->id}/reject-director", [
            'notes' => 'Anggaran terlalu besar, perlu penyesuaian.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rab->refresh();
        $this->assertEquals(RabStatus::DITOLAK, $rab->status);
    }

    public function test_rejection_requires_notes(): void
    {
        $rab = $this->createRab(RabStatus::DIAJUKAN, '007/RAB/SBK/VI/2026');

        $response = $this->actingAs($this->manajer)->post("/rab/{$rab->id}/reject-manager", [
            'notes' => '', // Empty notes
        ]);

        $response->assertSessionHasErrors('notes');

        $rab->refresh();
        $this->assertEquals(RabStatus::DIAJUKAN, $rab->status);
    }

    public function test_cannot_reject_draft_rab(): void
    {
        $rab = $this->createRab(RabStatus::DRAFT, '008/RAB/SBK/VI/2026');

        $response = $this->actingAs($this->manajer)->post("/rab/{$rab->id}/reject-manager", [
            'notes' => 'Test rejection',
        ]);

        $response->assertSessionHas('error');

        $rab->refresh();
        $this->assertEquals(RabStatus::DRAFT, $rab->status);
    }

    // ── Full Workflow ──

    public function test_complete_approval_workflow(): void
    {
        // 1. Admin creates and submits RAB
        $rab = $this->createRab(RabStatus::DIAJUKAN, '010/RAB/SBK/VI/2026');

        // 2. Manajer approves
        $response = $this->actingAs($this->manajer)->post("/rab/{$rab->id}/approve-manager", [
            'notes' => 'Sudah sesuai.',
        ]);
        $rab->refresh();
        $this->assertEquals(RabStatus::DISETUJUI_MANAJER, $rab->status);

        // 3. Direktur approves
        $response = $this->actingAs($this->direktur)->post("/rab/{$rab->id}/approve-director", [
            'notes' => 'ACC.',
        ]);
        $rab->refresh();
        $this->assertEquals(RabStatus::DISETUJUI, $rab->status);

        // Verify 2 approval records exist
        $this->assertEquals(2, $rab->approvals()->count());
    }

    // ── Discussion Notes ──

    public function test_discussion_notes_are_created_during_approval(): void
    {
        $rab = $this->createRab(RabStatus::DIAJUKAN, '011/RAB/SBK/VI/2026');

        $this->actingAs($this->manajer)->post("/rab/{$rab->id}/approve-manager", [
            'notes' => 'Catatan dari manajer',
        ]);

        $this->assertDatabaseHas('rab_discussions', [
            'rab_id' => $rab->id,
            'user_id' => $this->manajer->id,
            'message' => 'Catatan dari manajer',
        ]);
    }
}
