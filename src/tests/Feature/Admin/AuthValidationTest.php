<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthValidationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function email_is_required_for_admin_login()
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);

        $this->assertGuest('admin');
    }

    /** @test */
    public function password_is_required_for_admin_login()
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);

        $this->assertGuest('admin');
    }

    /** @test */
    public function admin_login_fails_with_unregistered_email()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('correct-password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'wrong@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

        $this->assertGuest('admin');
    }

    /** @test */
    public function admin_login_fails_when_user_is_not_admin()
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_USER,
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

        $this->assertGuest('admin');
    }

    /** @test */
    public function admin_can_login_successfully_with_correct_credentials()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin/attendance/list');
        $this->assertAuthenticatedAs($admin, 'admin');
    }
}