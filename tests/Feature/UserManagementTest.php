<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $direktur;
    private User $admin;
    private User $manajer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->direktur = User::factory()->create([
            'role' => UserRole::DIREKTUR,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => UserRole::ADMIN_KEUANGAN,
            'is_active' => true,
        ]);

        $this->manajer = User::factory()->create([
            'role' => UserRole::MANAJER_OPERASIONAL,
            'is_active' => true,
        ]);
    }

    // ── Access Control ──

    public function test_only_direktur_can_access_user_management(): void
    {
        $this->actingAs($this->direktur)->get('/direktur/users')
            ->assertStatus(200);

        $this->actingAs($this->admin)->get('/direktur/users')
            ->assertStatus(403);

        $this->actingAs($this->manajer)->get('/direktur/users')
            ->assertStatus(403);
    }

    // ── Create User ──

    public function test_direktur_can_create_user(): void
    {
        $response = $this->actingAs($this->direktur)->post('/direktur/users', [
            'name' => 'User Baru',
            'email' => 'baru@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::ADMIN_KEUANGAN->value,
        ]);

        $response->assertRedirect(route('direktur.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'User Baru',
            'email' => 'baru@test.com',
            'role' => UserRole::ADMIN_KEUANGAN->value,
            'is_active' => true,
        ]);
    }

    public function test_create_user_requires_password_confirmation(): void
    {
        $response = $this->actingAs($this->direktur)->post('/direktur/users', [
            'name' => 'User Baru',
            'email' => 'baru@test.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
            'role' => UserRole::ADMIN_KEUANGAN->value,
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_create_user_requires_unique_email(): void
    {
        $response = $this->actingAs($this->direktur)->post('/direktur/users', [
            'name' => 'Duplicate',
            'email' => $this->admin->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::ADMIN_KEUANGAN->value,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_cannot_create_direktur_user(): void
    {
        $response = $this->actingAs($this->direktur)->post('/direktur/users', [
            'name' => 'New Direktur',
            'email' => 'newdirektur@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::DIREKTUR->value,
        ]);

        $response->assertSessionHasErrors('role');
    }

    // ── Edit User ──

    public function test_direktur_can_access_edit_page(): void
    {
        $response = $this->actingAs($this->direktur)->get("/direktur/users/{$this->admin->id}/edit");
        $response->assertStatus(200);
    }

    public function test_direktur_can_update_user_info(): void
    {
        $response = $this->actingAs($this->direktur)->put("/direktur/users/{$this->admin->id}", [
            'name' => 'Admin Updated',
            'email' => $this->admin->email,
            'role' => UserRole::ADMIN_KEUANGAN->value,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('direktur.users.index'));

        $this->admin->refresh();
        $this->assertEquals('Admin Updated', $this->admin->name);
    }

    public function test_direktur_can_change_user_password(): void
    {
        $response = $this->actingAs($this->direktur)->put("/direktur/users/{$this->admin->id}", [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => UserRole::ADMIN_KEUANGAN->value,
            'is_active' => true,
            'password' => 'newsecurepassword',
        ]);

        $response->assertRedirect(route('direktur.users.index'));

        // Verify new password works
        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('newsecurepassword', $this->admin->fresh()->password)
        );
    }

    // ── Toggle Active ──

    public function test_direktur_can_deactivate_user(): void
    {
        $response = $this->actingAs($this->direktur)->patch("/direktur/users/{$this->admin->id}/toggle");

        $response->assertRedirect();
        $this->assertFalse($this->admin->fresh()->is_active);
    }

    public function test_direktur_can_reactivate_user(): void
    {
        $this->admin->update(['is_active' => false]);

        $response = $this->actingAs($this->direktur)->patch("/direktur/users/{$this->admin->id}/toggle");

        $response->assertRedirect();
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_direktur_cannot_deactivate_self(): void
    {
        $response = $this->actingAs($this->direktur)->patch("/direktur/users/{$this->direktur->id}/toggle");

        $response->assertSessionHas('error');
        $this->assertTrue($this->direktur->fresh()->is_active);
    }

    // ── Delete User ──

    public function test_direktur_can_delete_admin_user(): void
    {
        $adminId = $this->admin->id;

        $response = $this->actingAs($this->direktur)->delete("/direktur/users/{$adminId}");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $adminId]);
    }

    public function test_direktur_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->direktur)->delete("/direktur/users/{$this->direktur->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->direktur->id]);
    }

    public function test_direktur_cannot_delete_another_direktur(): void
    {
        $anotherDirektur = User::factory()->create([
            'role' => UserRole::DIREKTUR,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->direktur)->delete("/direktur/users/{$anotherDirektur->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $anotherDirektur->id]);
    }

    // ── User Model Helpers ──

    public function test_user_role_helpers_work_correctly(): void
    {
        $this->assertTrue($this->admin->isAdmin());
        $this->assertFalse($this->admin->isManajer());
        $this->assertFalse($this->admin->isDirektur());

        $this->assertFalse($this->manajer->isAdmin());
        $this->assertTrue($this->manajer->isManajer());
        $this->assertFalse($this->manajer->isDirektur());

        $this->assertFalse($this->direktur->isAdmin());
        $this->assertFalse($this->direktur->isManajer());
        $this->assertTrue($this->direktur->isDirektur());
    }
}
