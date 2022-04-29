<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function testUserCanCreateAccount()
    {
        $response = $this->registerAdmin();
        $response->assertOk();
        $response = $response->json();
        $this->assertDatabaseHas('admins', [
            'email' => $response['user']['email'],
        ]);
    }

    public function testInvalidLoginCredentials()
    {
        $admin = $this->registerAdmin()->json();
        $this->postJson('api/admin/auth/login', [
            'email' => $admin['user']['email'],
            'password' => 'dumbeeee'
        ])->assertStatus(400);
    }

    public function testValidLoginCredentials()
    {
        $admin = $this->registerAdmin()->json();
        return $this->postJson('api/admin/auth/login', [
            'email' => $admin['user']['email'],
            'password' => 'password'
        ])->assertOk();
    }

    public function testLogout()
    {
        $this->authenticatedUser();
        $this->postJson('api/admin/auth/logout')->assertSuccessful();
        $this->assertEquals(0, $this->authenticatedUser()->tokens()->count());
    }

    protected function registerAdmin()
    {
        return $this->postJson(
            'api/admin/auth/register',
            [
                'name' => 'Harry Potter',
                'email' => 'harry@gmail.com',
                'password' => 'password',
            ]
        );
    }

    protected function authenticatedUser()
    {
        $creatorRole = Role::whereSlug(Admin::ROLE_CAN_CREATE)->first();
        return Sanctum::actingAs(Admin::factory()->create(['role_id' => $creatorRole['id']]), ['*'], 'admin');
    }

    protected function authenticatedApproverUser()
    {
        $approverRole = Role::whereSlug(Admin::ROLE_CAN_APPROVE)->first();
        return Sanctum::actingAs(Admin::factory()->create(['role_id' => $approverRole['id']]), ['*'], 'admin');
    }
}
