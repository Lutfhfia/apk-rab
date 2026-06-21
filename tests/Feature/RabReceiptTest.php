<?php

namespace Tests\Feature;

use App\Enums\RabReceiptStatus;
use App\Enums\PaymentValidationStatus;
use App\Enums\RabStatus;
use App\Enums\UserRole;
use App\Enums\CashFlowType;
use App\Models\CashFlow;
use App\Models\ExpenseType;
use App\Models\Rab;
use App\Models\RabPayment;
use App\Models\RabReceipt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RabReceiptTest extends TestCase
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

    private function createDirectorApprovedRabWithPayment(): Rab
    {
        $rab = Rab::create([
            'rab_number' => '050/RAB/SBK/VI/2026',
            'request_date' => '2026-06-03',
            'period_month' => '6',
            'period_year' => '2026',
            'user_id' => $this->admin->id,
            'expense_type_id' => $this->expenseType->id,
            'description' => 'RAB sudah disetujui direktur',
            'status' => RabStatus::DISETUJUI,
            'total_amount' => 500000,
            'approved_by_manager_at' => now(),
            'approved_by_director_at' => now(),
        ]);

        RabPayment::create([
            'rab_id' => $rab->id,
            'paid_by' => $this->manajer->id,
            'payment_date' => '2026-06-04',
            'paid_amount' => 500000,
            'payment_method' => 'transfer',
            'proof_file_path' => 'payment-proofs/bukti-tf.jpg',
            'validation_status' => PaymentValidationStatus::VALID,
            'validated_by' => $this->manajer->id,
            'validated_at' => now(),
        ]);

        return $rab;
    }

    public function test_admin_upload_receipt_waits_for_manager_validation(): void
    {
        $rab = $this->createDirectorApprovedRabWithPayment();

        $response = $this->actingAs($this->admin)->post(route('rab.receipts.store', $rab), [
            'receipt_date' => '2026-06-05',
            'store_name' => 'PT Supplier',
            'receipt_number' => 'INV-001',
            'total_amount' => '500.000',
            'receipt_file' => UploadedFile::fake()->image('nota.jpg', 100, 100)->size(50),
            'notes' => 'Pembelian ATK',
        ]);

        $response->assertSessionHas('success');

        $receipt = RabReceipt::first();
        $this->assertNotNull($receipt);
        $this->assertEquals(RabReceiptStatus::MENUNGGU_VALIDASI, $receipt->status);
        $this->assertEquals(500000, (float) $receipt->total_amount);
        Storage::disk('public')->assertExists($receipt->receipt_file);

        $rab->refresh();
        $this->assertEquals(RabStatus::DISETUJUI, $rab->status);
        $this->assertNull($rab->completed_at);
    }

    public function test_manager_can_see_attachment_and_approve_receipt(): void
    {
        $rab = $this->createDirectorApprovedRabWithPayment();

        CashFlow::create([
            'transaction_date' => '2026-06-01',
            'type' => CashFlowType::SALDO_AWAL,
            'description' => 'Saldo awal',
            'debit' => 10000000,
            'credit' => 0,
            'balance' => 10000000,
            'created_by' => $this->manajer->id,
        ]);

        $receipt = RabReceipt::create([
            'rab_id' => $rab->id,
            'uploaded_by' => $this->admin->id,
            'receipt_date' => '2026-06-05',
            'store_name' => 'PT Supplier',
            'receipt_number' => 'INV-001',
            'total_amount' => 500000,
            'receipt_file' => 'rab-receipts/nota.jpg',
            'status' => RabReceiptStatus::MENUNGGU_VALIDASI,
        ]);

        $this->actingAs($this->manajer)
            ->get(route('manajer.receipts.index'))
            ->assertOk()
            ->assertSee('050/RAB/SBK/VI/2026')
            ->assertSee('PT Supplier')
            ->assertSee('Lihat');

        $this->actingAs($this->manajer)
            ->post(route('rab.receipts.approve', [$rab, $receipt]))
            ->assertSessionHas('success');

        $receipt->refresh();
        $rab->refresh();

        $this->assertEquals(RabReceiptStatus::VALID, $receipt->status);
        $this->assertEquals($this->manajer->id, $receipt->validated_by);
        $this->assertNotNull($receipt->validated_at);
        $this->assertEquals(RabStatus::SELESAI, $rab->status);
        $this->assertNotNull($rab->completed_at);
        
        $this->assertDatabaseHas('cash_flows', [
            'rab_id' => $rab->id,
            'debit' => 500000,
        ]);

        // Verify notification was sent to direktur
        $this->assertDatabaseHas('rab_notifications', [
            'user_id' => $this->direktur->id,
            'rab_id' => $rab->id,
            'title' => 'Nota LPJ Disetujui',
        ]);
    }

    public function test_manager_can_reject_receipt_with_notes_and_admin_can_reupload(): void
    {
        $rab = $this->createDirectorApprovedRabWithPayment();

        $receipt = RabReceipt::create([
            'rab_id' => $rab->id,
            'uploaded_by' => $this->admin->id,
            'receipt_date' => '2026-06-05',
            'store_name' => 'PT Supplier',
            'receipt_number' => 'INV-001',
            'total_amount' => 500000,
            'receipt_file' => 'rab-receipts/nota.jpg',
            'status' => RabReceiptStatus::MENUNGGU_VALIDASI,
        ]);

        $this->actingAs($this->manajer)
            ->post(route('rab.receipts.reject', [$rab, $receipt]), [
                'notes' => 'Nota buram.',
            ])
            ->assertSessionHas('success');

        $receipt->refresh();
        $this->assertEquals(RabReceiptStatus::DITOLAK, $receipt->status);
        $this->assertEquals('Nota buram.', $receipt->notes);

        // Verify notification was sent to direktur
        $this->assertDatabaseHas('rab_notifications', [
            'user_id' => $this->direktur->id,
            'rab_id' => $rab->id,
            'title' => 'Nota LPJ Ditolak',
        ]);

        $this->actingAs($this->admin)->post(route('rab.receipts.store', $rab), [
            'receipt_date' => '2026-06-06',
            'store_name' => 'PT Supplier Revisi',
            'receipt_number' => 'INV-002',
            'total_amount' => '500.000',
            'receipt_file' => UploadedFile::fake()->image('nota-baru.jpg', 100, 100)->size(50),
        ])->assertSessionHas('success');

        $newReceipt = RabReceipt::latest('id')->first();
        $this->assertEquals(RabReceiptStatus::MENUNGGU_VALIDASI, $newReceipt->status);
        $this->assertEquals('PT Supplier Revisi', $newReceipt->store_name);
        $this->assertNull($newReceipt->notes);
        $this->assertNull($newReceipt->validated_by);
        $this->assertDatabaseCount('rab_receipts', 2);
    }

    public function test_direktur_cannot_validate_receipt(): void
    {
        $rab = $this->createDirectorApprovedRabWithPayment();
        $receipt = RabReceipt::create([
            'rab_id' => $rab->id,
            'uploaded_by' => $this->admin->id,
            'receipt_date' => '2026-06-05',
            'store_name' => 'PT Supplier',
            'total_amount' => 500000,
            'receipt_file' => 'rab-receipts/nota.jpg',
            'status' => RabReceiptStatus::MENUNGGU_VALIDASI,
        ]);

        $this->actingAs($this->direktur)
            ->post(route('rab.receipts.approve', [$rab, $receipt]))
            ->assertStatus(403);
    }

    public function test_manager_can_view_validation_history_and_lpj_recap(): void
    {
        $rab = $this->createDirectorApprovedRabWithPayment();
        $rab->update([
            'status' => RabStatus::SELESAI,
            'completed_at' => '2026-06-06 10:00:00',
        ]);

        RabReceipt::create([
            'rab_id' => $rab->id,
            'uploaded_by' => $this->admin->id,
            'validated_by' => $this->manajer->id,
            'receipt_date' => '2026-06-05',
            'store_name' => 'PT Supplier Valid',
            'receipt_number' => 'INV-001',
            'total_amount' => 500000,
            'receipt_file' => 'rab-receipts/nota.jpg',
            'status' => RabReceiptStatus::VALID,
            'validated_at' => '2026-06-06 10:00:00',
        ]);

        $this->actingAs($this->manajer)
            ->get(route('manajer.receipts.index', [
                'tab' => 'history',
            ]))
            ->assertOk()
            ->assertSee('PT Supplier Valid')
            ->assertSee('Valid');

        $this->actingAs($this->manajer)
            ->get(route('manajer.receipts.index', [
                'tab' => 'recap',
                'month' => 6,
                'year' => 2026,
                'range' => 1,
            ]))
            ->assertOk()
            ->assertSee('PT Supplier Valid')
            ->assertSee('500.000')
            ->assertSee('Preview')
            ->assertSee('Download');
    }

    public function test_manager_can_preview_and_download_lpj_recap_pdf(): void
    {
        $rab = $this->createDirectorApprovedRabWithPayment();
        $rab->update([
            'status' => RabStatus::SELESAI,
            'completed_at' => '2026-06-06 10:00:00',
        ]);

        RabReceipt::create([
            'rab_id' => $rab->id,
            'uploaded_by' => $this->admin->id,
            'validated_by' => $this->manajer->id,
            'receipt_date' => '2026-06-05',
            'store_name' => 'PT Supplier Valid',
            'receipt_number' => 'INV-001',
            'total_amount' => 500000,
            'receipt_file' => 'rab-receipts/nota.jpg',
            'status' => RabReceiptStatus::VALID,
            'validated_at' => '2026-06-06 10:00:00',
        ]);

        $preview = $this->actingAs($this->manajer)
            ->get(route('rab.payments.recap.pdf', [
                'month' => 6,
                'year' => 2026,
                'range' => 1,
                'mode' => 'preview',
            ]));

        $preview->assertOk();
        $this->assertStringContainsString('application/pdf', $preview->headers->get('content-type'));

        $download = $this->actingAs($this->manajer)
            ->get(route('rab.payments.recap.pdf', [
                'month' => 6,
                'year' => 2026,
                'range' => 1,
                'mode' => 'download',
            ]));

        $download->assertOk();
        $this->assertStringContainsString('application/pdf', $download->headers->get('content-type'));
        $this->assertStringContainsString('attachment', $download->headers->get('content-disposition'));
    }

    public function test_direktur_can_preview_lpj_recap_pdf_without_validating_receipt(): void
    {
        $rab = $this->createDirectorApprovedRabWithPayment();
        RabReceipt::create([
            'rab_id' => $rab->id,
            'uploaded_by' => $this->admin->id,
            'validated_by' => $this->manajer->id,
            'receipt_date' => '2026-06-05',
            'store_name' => 'PT Supplier Valid',
            'receipt_number' => 'INV-001',
            'total_amount' => 500000,
            'receipt_file' => 'rab-receipts/nota.jpg',
            'status' => RabReceiptStatus::VALID,
            'validated_at' => '2026-06-06 10:00:00',
        ]);

        $response = $this->actingAs($this->direktur)
            ->get(route('rab.payments.recap.pdf', [
                'month' => 6,
                'year' => 2026,
                'range' => 1,
                'mode' => 'preview',
            ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }
}
