<?php

namespace Tests\Feature;

use App\Enums\RabStatus;
use App\Enums\UserRole;
use App\Models\ExpenseType;
use App\Models\Rab;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardChartDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_chart_data_only_uses_completed_rabs_for_budget_and_default_comparison(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN_KEUANGAN,
            'is_active' => true,
        ]);

        $listrik = ExpenseType::updateOrCreate(
            ['code' => 'listrik'],
            ['name' => 'Biaya Listrik', 'description' => 'Listrik', 'is_active' => true]
        );
        $airPam = ExpenseType::updateOrCreate(
            ['code' => 'air_pam'],
            ['name' => 'Biaya Air PAM', 'description' => 'Air PAM', 'is_active' => true]
        );
        $gaji = ExpenseType::updateOrCreate(
            ['code' => 'gaji'],
            ['name' => 'Biaya Gaji', 'description' => 'Gaji', 'is_active' => true]
        );

        Rab::create([
            'rab_number' => '001/RAB/SBK/VI/2026',
            'request_date' => now(),
            'period_month' => now()->month,
            'period_year' => now()->year,
            'user_id' => $admin->id,
            'expense_type_id' => $listrik->id,
            'status' => RabStatus::SELESAI,
            'total_amount' => 1000000,
        ]);

        Rab::create([
            'rab_number' => '002/RAB/SBK/VI/2026',
            'request_date' => now(),
            'period_month' => now()->month,
            'period_year' => now()->year,
            'user_id' => $admin->id,
            'expense_type_id' => $airPam->id,
            'status' => RabStatus::DIAJUKAN,
            'total_amount' => 2000000,
        ]);

        Rab::create([
            'rab_number' => '003/RAB/SBK/VI/2026',
            'request_date' => now(),
            'period_month' => now()->month,
            'period_year' => now()->year,
            'user_id' => $admin->id,
            'expense_type_id' => $gaji->id,
            'status' => RabStatus::SELESAI,
            'total_amount' => 3000000,
        ]);

        $response = $this->actingAs($admin)->getJson(route('dashboard.chart-data'));

        $response->assertOk();
        $payload = $response->json();

        $this->assertEquals(4000000, array_sum($payload['budget']['anggaran']));
        $this->assertSame(['Selesai'], $payload['status']['labels']);
        $this->assertSame([2], $payload['status']['data']);

        $comparisonLabels = collect($payload['comparison']['datasets'])->pluck('label')->all();
        $this->assertContains('Biaya Listrik', $comparisonLabels);
        $this->assertContains('Biaya Air PAM', $comparisonLabels);
        $this->assertNotContains('Biaya Gaji', $comparisonLabels);
    }
}
