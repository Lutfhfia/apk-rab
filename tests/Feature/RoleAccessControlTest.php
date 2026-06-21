<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessControlTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manajer;
    private User $direktur;

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
    }

    // ── Unauthenticated Access ──

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_manajer_dashboard(): void
    {
        $response = $this->get('/manajer/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_direktur_dashboard(): void
    {
        $response = $this->get('/direktur/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_rab_index(): void
    {
        $response = $this->get('/rab');
        $response->assertRedirect(route('login'));
    }

    // ── Admin Keuangan Access ──

    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_rab_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/rab');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_rab_create(): void
    {
        // Note: The view 'rab.create' doesn't exist as a standalone file (uses modal in index).
        // The controller should redirect to the index route.
        $response = $this->actingAs($this->admin)->get('/rab/create');
        $response->assertRedirect(route('rab.index'));
    }

    public function test_admin_cannot_access_manajer_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/manajer/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_direktur_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/direktur/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_manajer_rab_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/manajer/rab');
        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_direktur_rab_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/direktur/rab');
        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_cash_flow(): void
    {
        $response = $this->actingAs($this->admin)->get('/manajer/cash-flow');
        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->admin)->get('/direktur/users');
        $response->assertStatus(403);
    }

    // ── Manajer Keuangan Access ──

    public function test_manajer_can_access_manajer_dashboard(): void
    {
        $response = $this->actingAs($this->manajer)->get('/manajer/dashboard');
        $response->assertStatus(200);
    }

    public function test_manajer_can_access_manajer_rab_list(): void
    {
        $response = $this->actingAs($this->manajer)->get('/manajer/rab');
        $response->assertStatus(200);
    }

    public function test_manajer_can_access_cash_flow(): void
    {
        $response = $this->actingAs($this->manajer)->get('/manajer/cash-flow');
        $response->assertStatus(200);
    }

    public function test_manajer_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->manajer)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_manajer_cannot_access_rab_create(): void
    {
        $response = $this->actingAs($this->manajer)->get('/rab');
        $response->assertStatus(403);
    }

    public function test_manajer_cannot_access_direktur_dashboard(): void
    {
        $response = $this->actingAs($this->manajer)->get('/direktur/dashboard');
        $response->assertStatus(403);
    }

    public function test_manajer_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->manajer)->get('/direktur/users');
        $response->assertStatus(403);
    }

    // ── Direktur Access ──

    public function test_direktur_can_access_direktur_dashboard(): void
    {
        $response = $this->actingAs($this->direktur)->get('/direktur/dashboard');
        $response->assertStatus(200);
    }

    public function test_direktur_can_access_direktur_rab_list(): void
    {
        $response = $this->actingAs($this->direktur)->get('/direktur/rab');
        $response->assertStatus(200);
    }

    public function test_direktur_can_access_user_management(): void
    {
        $response = $this->actingAs($this->direktur)->get('/direktur/users');
        $response->assertStatus(200);
    }

    public function test_direktur_can_access_cash_flow(): void
    {
        $response = $this->actingAs($this->direktur)->get('/direktur/cash-flow');
        $response->assertStatus(200);
    }

    public function test_direktur_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->direktur)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_direktur_cannot_access_rab_create(): void
    {
        $response = $this->actingAs($this->direktur)->get('/rab');
        $response->assertStatus(403);
    }

    public function test_direktur_cannot_access_manajer_dashboard(): void
    {
        $response = $this->actingAs($this->direktur)->get('/manajer/dashboard');
        $response->assertStatus(403);
    }

    // ── EnsureUserIsActive Middleware ──

    public function test_inactive_user_is_logged_out_when_accessing_protected_route(): void
    {
        $inactiveUser = User::factory()->create([
            'role' => UserRole::ADMIN_KEUANGAN,
            'is_active' => false,
        ]);

        $response = $this->actingAs($inactiveUser)->get('/admin/dashboard');
        $response->assertRedirect(route('login'));
    }

    // ── Report Access ──

    public function test_manajer_can_access_reports(): void
    {
        $response = $this->actingAs($this->manajer)->get('/report');
        $response->assertStatus(200);
    }

    public function test_direktur_can_access_reports(): void
    {
        $response = $this->actingAs($this->direktur)->get('/report');
        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_reports(): void
    {
        $response = $this->actingAs($this->admin)->get('/report');
        $response->assertStatus(403);
    }
}
