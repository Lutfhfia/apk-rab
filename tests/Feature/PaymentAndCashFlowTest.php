<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Rab;
use App\Models\CashFlow;
use App\Models\ExpenseType;
use App\Enums\UserRole;
use App\Enums\RabStatus;
use App\Enums\CashFlowType;
use App\Enums\PaymentValidationStatus;
use App\Models\RabPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentAndCashFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manajer;
    private User $direktur;
    private ExpenseType $expenseType;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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

    private function createApprovedRab(string $rabNumber = '001/RAB/SBK/VI/2026', float $amount = 500000): Rab
    {
        return Rab::create([
            'rab_number' => $rabNumber,
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseType->id,
            'description' => 'Approved RAB',
            'status' => RabStatus::DISETUJUI,
            'total_amount' => $amount,
        ]);
    }

    // ── Cash Flow: Dana Masuk ──

    public function test_manajer_can_add_saldo_awal(): void
    {
        $response = $this->actingAs($this->manajer)->post('/manajer/cash-flow', [
            'transaction_date' => '2026-06-01',
            'type' => 'saldo_awal',
            'description' => 'Saldo awal Juni 2026',
            'amount' => 10000000,
        ]);

        $response->assertRedirect(route('manajer.cash-flow.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cash_flows', [
            'type' => 'saldo_awal',
            'debit' => 0,
            'credit' => 10000000,
            'balance' => 10000000,
        ]);
    }

    public function test_manajer_can_add_dana_masuk(): void
    {
        // First add saldo awal
        CashFlow::create([
            'transaction_date' => '2026-06-01',
            'type' => 'saldo_awal',
            'description' => 'Saldo awal',
            'debit' => 5000000,
            'credit' => 0,
            'balance' => 5000000,
            'created_by' => $this->manajer->id,
        ]);

        $response = $this->actingAs($this->manajer)->post('/manajer/cash-flow', [
            'transaction_date' => '2026-06-05',
            'type' => 'dana_masuk',
            'description' => 'Transfer dari pusat',
            'amount' => 3000000,
        ]);

        $response->assertRedirect(route('manajer.cash-flow.index'));

        $latestCf = CashFlow::latest('id')->first();
        $this->assertEquals(8000000, (float) $latestCf->balance);
    }

    public function test_dana_keluar_fails_when_balance_insufficient(): void
    {
        // Start with 1 juta
        CashFlow::create([
            'transaction_date' => '2026-06-01',
            'type' => 'saldo_awal',
            'description' => 'Saldo awal',
            'debit' => 1000000,
            'credit' => 0,
            'balance' => 1000000,
            'created_by' => $this->manajer->id,
        ]);

        // Try to withdraw 5 juta
        $response = $this->actingAs($this->manajer)->post('/manajer/cash-flow', [
            'transaction_date' => '2026-06-05',
            'type' => 'dana_keluar',
            'description' => 'Test withdrawal',
            'amount' => 5000000,
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_admin_cannot_add_cash_flow(): void
    {
        $response = $this->actingAs($this->admin)->post('/manajer/cash-flow', [
            'transaction_date' => '2026-06-01',
            'type' => 'saldo_awal',
            'description' => 'Test',
            'amount' => 1000000,
        ]);

        $response->assertStatus(403);
    }

    // ── Cash Flow Index ──

    public function test_manajer_can_view_cash_flow(): void
    {
        $response = $this->actingAs($this->manajer)->get('/manajer/cash-flow');
        $response->assertStatus(200);
    }

    public function test_direktur_can_view_cash_flow(): void
    {
        $response = $this->actingAs($this->direktur)->get('/direktur/cash-flow');
        $response->assertStatus(200);
    }

    public function test_admin_cannot_view_cash_flow(): void
    {
        $response = $this->actingAs($this->admin)->get('/manajer/cash-flow');
        $response->assertStatus(403);
    }

    // ── Payment Upload ──

    public function test_manager_can_create_payment_for_approved_rab(): void
    {
        $rab = $this->createApprovedRab();

        $response = $this->actingAs($this->manajer)->post("/rab/{$rab->id}/payment", [
            'payment_date' => '2026-06-03',
            'paid_amount' => '500.000',
            'payment_method' => 'transfer',
            'recipient_name' => 'PT Supplier',
            'recipient_account' => '1234567890',
            'proof_file' => UploadedFile::fake()->image('bukti.jpg', 100, 100)->size(50),
            'notes' => 'Pembayaran Petty Cash.',
        ]);

        $response->assertSessionHas('success');

        $rab->refresh();
        $this->assertEquals(RabStatus::DISETUJUI, $rab->status);
        $this->assertNull($rab->completed_at);

        // Verify payment record
        $this->assertDatabaseHas('rab_payments', [
            'rab_id' => $rab->id,
            'paid_by' => $this->manajer->id,
            'paid_amount' => 500000,
            'validation_status' => PaymentValidationStatus::VALID->value,
            'validated_by' => $this->manajer->id,
        ]);

        $this->assertDatabaseHas('cash_flows', [
            'rab_id' => $rab->id,
            'type' => CashFlowType::DANA_MASUK->value,
            'credit' => 500000,
        ]);
    }

    public function test_admin_cannot_create_payment(): void
    {
        $rab = $this->createApprovedRab();

        $response = $this->actingAs($this->admin)->post("/rab/{$rab->id}/payment", [
            'payment_date' => '2026-06-03',
            'paid_amount' => 500000,
            'payment_method' => 'transfer',
            'proof_file' => UploadedFile::fake()->image('bukti.jpg', 100, 100)->size(50),
        ]);

        $response->assertStatus(403);
    }

    public function test_payment_fails_for_non_approved_rab(): void
    {
        $rab = Rab::create([
            'rab_number' => '003/RAB/SBK/VI/2026',
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseType->id,
            'description' => 'Draft RAB',
            'status' => RabStatus::DIAJUKAN,
            'total_amount' => 500000,
        ]);

        $response = $this->actingAs($this->manajer)->post("/rab/{$rab->id}/payment", [
            'payment_date' => '2026-06-03',
            'paid_amount' => '500.000',
            'payment_method' => 'transfer',
            'proof_file' => UploadedFile::fake()->image('bukti.jpg', 100, 100)->size(50),
        ]);

        $response->assertSessionHas('error');
    }

    // ── Cash Flow Validation ──

    public function test_cash_flow_requires_valid_fields(): void
    {
        $response = $this->actingAs($this->manajer)->post('/manajer/cash-flow', [
            'transaction_date' => '',
            'type' => '',
            'description' => '',
            'amount' => '',
        ]);

        $response->assertSessionHasErrors(['transaction_date', 'type', 'description', 'amount']);
    }

    public function test_cash_flow_amount_must_be_positive(): void
    {
        $response = $this->actingAs($this->manajer)->post('/manajer/cash-flow', [
            'transaction_date' => '2026-06-01',
            'type' => 'dana_masuk',
            'description' => 'Test',
            'amount' => 0,
        ]);

        $response->assertSessionHasErrors('amount');
    }
}
