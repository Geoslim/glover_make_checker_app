<?php

namespace Tests\Feature\UserRequestApproval;

use App\Models\Admin;
use App\Models\RequestApproval;
use App\Models\User;
use App\Notifications\Admin\RequestApproval\RequestApprovalNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Auth\AuthTest;

class UserRequestApprovalTest extends AuthTest
{
    /**
     * @var void
     */
    public $user;
    public $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
    }

    public function testMakeUserCreateRequest()
    {
        Notification::fake();
        $this->authenticatedUser();
        $response = $this->postJson('api/admin/user/create', [
            'first_name' => 'George',
            'last_name' => 'Smith',
            'email' => 'george@example.com'
        ]);
        $response->assertOk();
        $response = $response->json();
        $this->assertDatabaseHas('request_approvals', [
            'approvable_type' => User::class,
            'request_type' => RequestApproval::REQUEST_TYPE_CREATING,
            'data->last_name' => ($response['data']['last_name']),
        ]);
        $this->assertDatabaseMissing('users', [
            'first_name' => $response['data']['first_name'],
        ]);
        Notification::assertSentTo($this->admin, RequestApprovalNotification::class);
    }

    public function testMakeUserUpdateRequest()
    {
        Notification::fake();
        $this->authenticatedUser();
        $this->user = $this->createUserWithoutEvents();

        $response = $this->patchJson('api/admin/user/update', [
            'user_id' => $this->user->id,
            'first_name' => 'George',
            'last_name' => 'Smith',
            'email' => 'george@example.com'
        ]);
        $this->assertEquals(false, $this->user->isDirty());
        $this->assertModelExists($this->user);
        $response->assertOk();
        $this->assertDatabaseHas('request_approvals', [
            'request_type' => RequestApproval::REQUEST_TYPE_UPDATING,
            'approvable_id' => $this->user->id,
            'data->last_name' => 'Smith'
        ]);
        Notification::assertSentTo($this->admin, RequestApprovalNotification::class);
    }

    public function testMakeUserDeleteRequest()
    {
        Notification::fake();
        $this->authenticatedUser();
        $this->user = $this->createUserWithoutEvents();

        $response = $this->deleteJson('api/admin/user/delete', [
            'user_id' => $this->user->id,
        ]);

        $this->assertEquals(false, $this->user->isDirty());
        $this->assertModelExists($this->user);
        $response->assertOk();
        $this->assertDatabaseHas('request_approvals', [
            'request_type' => RequestApproval::REQUEST_TYPE_DELETING,
            'approvable_id' => $this->user->id,
            'data->last_name' => $this->user->last_name,
        ]);

        Notification::assertSentTo($this->admin, RequestApprovalNotification::class);
    }

    public function testRequestTypeAlreadyExists()
    {
        $this->user = $this->createUserWithoutEvents();
        $this->createUserRequest(
            $this->user,
            RequestApproval::REQUEST_TYPE_DELETING,
            $this->authenticatedUser()
        );
        $response = $this->deleteJson('api/admin/user/delete', [
            'user_id' => $this->user->id,
        ]);
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function testCreatingUserRequestCanBeApproved()
    {
        Event::fake();
        $this->authenticatedApproverUser();
        $this->user = User::factory()->make();
        $requestApproval = $this->createUserRequest(
            $this->user,
            RequestApproval::REQUEST_TYPE_CREATING,
            $this->authenticatedApproverUser()
        );
        $response = $this->postJson('api/admin/requests/action', [
            'request_approval_id' => $requestApproval->id,
            'action' => RequestApproval::ACTION_APPROVE,
        ]);
        $this->assertModelExists($requestApproval);
        $response->assertOk();
        Event::assertNotDispatched(User::class);
    }

    public function testUpdatingUserRequestCanBeApproved()
    {
        $this->authenticatedApproverUser();
        Event::fake();
        $this->user = $this->createUserWithoutEvents();
        $requestApproval = $this->createUserRequest(
            $this->user,
            RequestApproval::REQUEST_TYPE_UPDATING,
            $this->authenticatedApproverUser()
        );
        $response = $this->postJson('api/admin/requests/action', [
            'request_approval_id' => $requestApproval->id,
            'action' => RequestApproval::ACTION_APPROVE,
        ]);
        $this->assertModelExists($this->user);
        $response->assertOk();
        Event::assertNotDispatched(User::class);
    }

    public function testDeletingUserRequestCanBeApproved()
    {
        $this->authenticatedApproverUser();
        Event::fake();
        $this->user = $this->createUserWithoutEvents();
        $requestApproval = $this->createUserRequest(
            $this->user,
            RequestApproval::REQUEST_TYPE_DELETING,
            $this->authenticatedApproverUser()
        );
        $response = $this->postJson('api/admin/requests/action', [
            'request_approval_id' => $requestApproval->id,
            'action' => RequestApproval::ACTION_APPROVE,
        ]);
        $this->assertModelMissing($this->user);
        $response->assertOk();
        Event::assertNotDispatched(User::class);
    }

    public function testUserRequestCanBeDeclined()
    {
        $this->authenticatedApproverUser();
        Event::fake();
        $this->user = $this->createUserWithoutEvents();
        $requestApproval = $this->createUserRequest(
            $this->user,
            RequestApproval::REQUEST_TYPE_DELETING,
            $this->authenticatedApproverUser()
        );
        $response = $this->postJson('api/admin/requests/action', [
            'request_approval_id' => $requestApproval->id,
            'action' => RequestApproval::ACTION_DECLINE,
        ]);
        $this->assertModelExists($this->user);
        $this->assertDeleted($requestApproval);
        $this->assertModelMissing($requestApproval);
        $response->assertOk();
    }

    protected function createUserWithoutEvents()
    {
        return User::withoutEvents(function () {
            return User::factory()->create();
        });
    }

    protected function createUserRequest($user, $type, $admin)
    {
        return $user->approvals()->create([
            'request_type' => $type,
            'requested_id' => $admin->id,
            'data' => $this->user,
        ]);
    }
}
