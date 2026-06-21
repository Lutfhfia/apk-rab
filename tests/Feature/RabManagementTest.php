<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Rab;
use App\Models\ExpenseType;
use App\Models\AuditLog;
use App\Models\RabNotification;
use App\Enums\UserRole;
use App\Enums\RabStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RabManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manajer;
    private ExpenseType $expenseTypePettyCash;
    private ExpenseType $expenseTypeGaji;
    private ExpenseType $expenseTypeBulanan;
    private ExpenseType $expenseTypeListrik;
    private ExpenseType $expenseTypePnbp;

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

        $this->expenseTypePettyCash = ExpenseType::updateOrCreate(
            ['code' => 'petty_cash'],
            [
                'name' => 'Petty Cash',
                'description' => 'Pengeluaran kecil',
                'is_active' => true,
            ]
        );

        $this->expenseTypeBulanan = ExpenseType::updateOrCreate(
            ['code' => 'bulanan'],
            [
                'name' => 'Biaya Bulanan',
                'description' => 'Pembayaran rutin bulanan',
                'is_active' => true,
            ]
        );

        $this->expenseTypeGaji = ExpenseType::updateOrCreate(
            ['code' => 'gaji'],
            [
                'name' => 'Biaya Gaji',
                'description' => 'Pembayaran gaji karyawan',
                'is_active' => true,
            ]
        );

        $this->expenseTypeListrik = ExpenseType::updateOrCreate(
            ['code' => 'listrik'],
            [
                'name' => 'Biaya Listrik',
                'description' => 'Pembayaran tagihan listrik bulanan',
                'is_active' => true,
            ]
        );

        $this->expenseTypePnbp = ExpenseType::updateOrCreate(
            ['code' => 'pnbp'],
            [
                'name' => 'Pembayaran PNBP',
                'description' => 'Pembayaran Penerimaan Negara Bukan Pajak',
                'is_active' => true,
            ]
        );
    }

    // ── Create RAB (Petty Cash as Draft) ──

    public function test_admin_can_create_petty_cash_rab_as_draft(): void
    {
        $response = $this->actingAs($this->admin)->post('/rab', [
            'rab_month' => '06',
            'rab_year' => '2026',
            'request_date' => '2026-06-03',
            'expense_type_id' => $this->expenseTypePettyCash->id,
            'description' => 'Petty Cash Juni 2026',
            'action' => 'draft',
            'items' => [
                [
                    'expense_name' => 'Beli ATK',
                    'transaction_date' => '2026-06-01',
                    'volume' => 2,
                    'unit' => 'pcs',
                    'unit_price' => 50000,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rabs', [
            'user_id' => $this->admin->id,
            'status' => RabStatus::DRAFT->value,
            'expense_type_id' => $this->expenseTypePettyCash->id,
        ]);

        $rab = Rab::latest()->first();
        $this->assertNotNull($rab);
        $this->assertEquals(100000.00, (float) $rab->total_amount);
    }

    // ── Create RAB & Submit ──

    public function test_admin_can_create_and_submit_petty_cash_rab(): void
    {
        $response = $this->actingAs($this->admin)->post('/rab', [
            'rab_month' => '06',
            'rab_year' => '2026',
            'request_date' => '2026-06-03',
            'expense_type_id' => $this->expenseTypePettyCash->id,
            'description' => 'Petty Cash Juni 2026',
            'action' => 'submit',
            'items' => [
                [
                    'expense_name' => 'Tinta Printer',
                    'transaction_date' => '2026-06-01',
                    'volume' => 1,
                    'unit' => 'pcs',
                    'unit_price' => 150000,
                ],
            ],
        ]);

        $response->assertRedirect(route('rab.index'));
        $response->assertSessionHas('success');

        $rab = Rab::latest()->first();
        $this->assertNotNull($rab);
        $this->assertEquals(RabStatus::DIAJUKAN, $rab->status);
        $this->assertNotNull($rab->submitted_at);

        // Verify audit log was created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'submit_rab',
            'rab_id' => $rab->id,
        ]);

        // Verify notification was sent to manajer
        $this->assertDatabaseHas('rab_notifications', [
            'user_id' => $this->manajer->id,
            'rab_id' => $rab->id,
        ]);
    }

    // ── Create RAB (Bulanan) ──

    public function test_admin_can_create_bulanan_rab(): void
    {
        $response = $this->actingAs($this->admin)->post('/rab', [
            'rab_month' => '06',
            'rab_year' => '2026',
            'request_date' => '2026-06-03',
            'expense_type_id' => $this->expenseTypeBulanan->id,
            'description' => 'Biaya Bulanan Juni 2026',
            'action' => 'draft',
            'items' => [
                [
                    'payment_name' => 'Listrik Kantor',
                    'total_expense' => 2500000,
                    'transaction_date' => '2026-06-05',
                    'period' => 'Juni 2026',
                ],
                [
                    'payment_name' => 'Internet',
                    'total_expense' => 500000,
                    'transaction_date' => '2026-06-05',
                    'period' => 'Juni 2026',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rab = Rab::latest()->first();
        $this->assertNotNull($rab);
        $this->assertEquals(3000000.00, (float) $rab->total_amount);
        $this->assertEquals(2, $rab->monthlyExpenseItems()->count());
    }

    // ── Submit a Draft ──

    public function test_admin_can_create_listrik_rab_with_monthly_items(): void
    {
        $response = $this->actingAs($this->admin)->post('/rab', [
            'rab_month' => '06',
            'rab_year' => '2026',
            'request_date' => '2026-06-03',
            'expense_type_id' => $this->expenseTypeListrik->id,
            'period_month' => '6',
            'description' => 'Biaya Listrik Juni 2026',
            'action' => 'draft',
            'items' => [
                [
                    'payment_name' => 'Tagihan Listrik Kantor',
                    'registration_number' => 'PLN-001',
                    'account_name' => 'PT SBK',
                    'total_expense' => 2500000,
                    'admin_fee' => 5000,
                    'transaction_date' => '2026-06-05',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rab = Rab::latest()->first();
        $this->assertNotNull($rab);
        $this->assertEquals(2505000.00, (float) $rab->total_amount);
        $this->assertEquals(1, $rab->monthlyExpenseItems()->count());
        $this->assertDatabaseHas('monthly_expense_items', [
            'rab_id' => $rab->id,
            'payment_name' => 'Tagihan Listrik Kantor',
            'period' => 'Juni 2026',
        ]);
    }

    public function test_admin_can_create_salary_rab_with_deduction(): void
    {
        $response = $this->actingAs($this->admin)->post('/rab', [
            'rab_month' => '06',
            'rab_year' => '2026',
            'request_date' => '2026-06-03',
            'expense_type_id' => $this->expenseTypeGaji->id,
            'description' => 'Gaji Juni 2026',
            'action' => 'draft',
            'items' => [
                [
                    'employee_name' => 'Budi',
                    'position' => 'Admin',
                    'bank_account_number' => '1234567890',
                    'attendance_days' => 20,
                    'base_salary' => 3000000,
                    'meal_allowance_daily' => 25000,
                    'transport_daily' => 20000,
                    'overtime' => 150000,
                    'deduction' => 100000,
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rab = Rab::latest()->first();
        $this->assertNotNull($rab);
        $this->assertEquals(3950000.00, (float) $rab->total_amount);

        $this->assertDatabaseHas('salary_expense_items', [
            'rab_id' => $rab->id,
            'employee_name' => 'Budi',
            'deduction' => 100000,
            'total_salary' => 3950000,
        ]);
    }

    public function test_admin_can_submit_draft_rab(): void
    {
        $rab = Rab::create([
            'rab_number' => '001/RAB/SBK/VI/2026',
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseTypePettyCash->id,
            'description' => 'Test Draft',
            'status' => RabStatus::DRAFT,
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->admin)->post("/rab/{$rab->id}/submit");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rab->refresh();
        $this->assertEquals(RabStatus::DIAJUKAN, $rab->status);
        $this->assertNotNull($rab->submitted_at);
    }

    public function test_cannot_submit_non_draft_rab(): void
    {
        $rab = Rab::create([
            'rab_number' => '002/RAB/SBK/VI/2026',
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseTypePettyCash->id,
            'description' => 'Test',
            'status' => RabStatus::DIAJUKAN,
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->admin)->post("/rab/{$rab->id}/submit");

        $response->assertSessionHas('error');
    }

    // ── Edit RAB ──

    public function test_admin_can_edit_draft_rab(): void
    {
        $rab = Rab::create([
            'rab_number' => '003/RAB/SBK/VI/2026',
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseTypePettyCash->id,
            'description' => 'Draft RAB',
            'status' => RabStatus::DRAFT,
            'total_amount' => 100000,
        ]);

        // Note: View 'rab.edit' doesn't exist as standalone file (uses modal in index)
        // The controller should redirect to the index route with the RAB's status.
        $response = $this->actingAs($this->admin)->get("/rab/{$rab->id}/edit");
        $response->assertRedirect(route('rab.index', ['status' => $rab->status->value]));
    }

    public function test_admin_can_edit_rejected_rab(): void
    {
        $rab = Rab::create([
            'rab_number' => '004/RAB/SBK/VI/2026',
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseTypePettyCash->id,
            'description' => 'Rejected RAB',
            'status' => RabStatus::DITOLAK,
            'total_amount' => 100000,
        ]);

        // Note: View 'rab.edit' doesn't exist as standalone file (uses modal in index)
        // The controller should redirect to the index route with the RAB's status.
        $response = $this->actingAs($this->admin)->get("/rab/{$rab->id}/edit");
        $response->assertRedirect(route('rab.index', ['status' => $rab->status->value]));
    }

    public function test_admin_cannot_edit_approved_rab(): void
    {
        $rab = Rab::create([
            'rab_number' => '005/RAB/SBK/VI/2026',
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseTypePettyCash->id,
            'description' => 'Approved RAB',
            'status' => RabStatus::DISETUJUI,
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->admin)->get("/rab/{$rab->id}/edit");
        $response->assertRedirect();
    }

    public function test_manajer_cannot_edit_rab(): void
    {
        $rab = Rab::create([
            'rab_number' => '006/RAB/SBK/VI/2026',
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseTypePettyCash->id,
            'description' => 'Draft RAB',
            'status' => RabStatus::DRAFT,
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->manajer)->get("/rab/{$rab->id}/edit");
        $response->assertStatus(403);
    }

    // ── Delete RAB ──

    public function test_admin_can_delete_draft_rab(): void
    {
        $rab = Rab::create([
            'rab_number' => '007/RAB/SBK/VI/2026',
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseTypePettyCash->id,
            'description' => 'Draft to delete',
            'status' => RabStatus::DRAFT,
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->admin)->delete("/rab/{$rab->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('rabs', ['id' => $rab->id]);
    }

    public function test_admin_cannot_delete_submitted_rab(): void
    {
        $rab = Rab::create([
            'rab_number' => '008/RAB/SBK/VI/2026',
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseTypePettyCash->id,
            'description' => 'Submitted RAB',
            'status' => RabStatus::DIAJUKAN,
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($this->admin)->delete("/rab/{$rab->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('rabs', ['id' => $rab->id, 'deleted_at' => null]);
    }

    // ── RAB Number Generation ──

    public function test_rab_number_is_generated_correctly(): void
    {
        $number = Rab::buildNumber(1, '06', '2026');
        $this->assertEquals('001/RAB/SBK/VI/2026', $number);

        $number2 = Rab::buildNumber(15, '12', '2026');
        $this->assertEquals('015/RAB/SBK/XII/2026', $number2);
    }

    public function test_rab_number_auto_increments(): void
    {
        Rab::create([
            'rab_number' => '001/RAB/SBK/VI/2026',
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseTypePettyCash->id,
            'status' => RabStatus::DRAFT,
            'total_amount' => 0,
        ]);

        $nextSeq = Rab::nextSequence();
        $this->assertEquals(2, $nextSeq);
    }

    // ── Validation ──

    public function test_rab_creation_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post('/rab', [
            'action' => 'draft',
        ]);

        $response->assertSessionHasErrors(['rab_month', 'rab_year', 'request_date', 'expense_type_id']);
    }

    public function test_rab_creation_fails_with_invalid_expense_type(): void
    {
        $response = $this->actingAs($this->admin)->post('/rab', [
            'rab_month' => '06',
            'rab_year' => '2026',
            'request_date' => '2026-06-03',
            'expense_type_id' => 9999,
            'action' => 'draft',
        ]);

        $response->assertSessionHasErrors('expense_type_id');
    }

    // ── Create RAB (PNBP) ──

    public function test_admin_can_create_pnbp_rab(): void
    {
        $response = $this->actingAs($this->admin)->post('/rab', [
            'rab_month' => '06',
            'rab_year' => '2026',
            'request_date' => '2026-06-07',
            'expense_type_id' => $this->expenseTypePnbp->id,
            'description' => 'Pembayaran PNBP Juni 2026',
            'action' => 'draft',
            'items' => [
                [
                    'item_name' => 'John Doe',
                    'agenda_number' => 'AGN-001',
                    'level' => '2',
                    'tarif_pnbp' => 300000,
                    'company_name' => 'PT Maju Jaya',
                ],
                [
                    'item_name' => 'Jane Smith',
                    'agenda_number' => 'AGN-002',
                    'level' => '4',
                    'tarif_pnbp' => 525000,
                    'company_name' => 'CV Sejahtera',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $rab = Rab::latest()->first();
        $this->assertNotNull($rab);
        // Total = 300000 + 525000 = 825000
        $this->assertEquals(825000.00, (float) $rab->total_amount);
        $this->assertEquals(2, $rab->pnbpExpenseItems()->count());

        // Verify the items were stored correctly
        $this->assertDatabaseHas('pnbp_expense_items', [
            'rab_id' => $rab->id,
            'item_name' => 'John Doe',
            'agenda_number' => 'AGN-001',
            'level' => '2',
            'company_name' => 'PT Maju Jaya',
        ]);

        $this->assertDatabaseHas('pnbp_expense_items', [
            'rab_id' => $rab->id,
            'item_name' => 'Jane Smith',
            'level' => '4',
            'company_name' => 'CV Sejahtera',
        ]);
    }

    public function test_pnbp_rab_requires_valid_level(): void
    {
        $response = $this->actingAs($this->admin)->post('/rab', [
            'rab_month' => '06',
            'rab_year' => '2026',
            'request_date' => '2026-06-07',
            'expense_type_id' => $this->expenseTypePnbp->id,
            'description' => 'PNBP Test',
            'action' => 'draft',
            'items' => [
                [
                    'item_name' => 'Test',
                    'agenda_number' => 'AGN-001',
                    'level' => '5', // Invalid level
                    'tarif_pnbp' => 300000,
                    'company_name' => 'PT Test',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.level');
    }
}
