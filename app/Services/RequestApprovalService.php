<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\RequestApproval;
use App\Models\User;
use App\Notifications\Admin\RequestApproval\RequestApprovalNotification;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RequestApprovalService
{
    /**
     * @param string $requestType
     * @param Model $data
     * @throws Exception
     */
    public static function storeRequest(string $requestType, Model $data): void
    {
        self::abortIfRequestExists($data);
        $data->approvals()->create([
            'request_type' => $requestType,
            'requested_id' => Auth::id(),
            'data' => $data,
        ]);

        $admins = Admin::whereNotIn('email', [auth()->user()->email])->get();
        foreach ($admins as $admin) {
            $admin->notify(new RequestApprovalNotification(auth()->user()));
        }
    }

    /**
     * @param RequestApproval $requestApproval
     * @return void
     */
    public static function approveRequest(RequestApproval $requestApproval): void
    {
        match ($requestApproval['approvable_type']) {
            User::class => UserService::approveRequest($requestApproval),
            default => false
        };
    }

    /**
     * @param object $data
     * @throws Exception
     */
    protected static function abortIfRequestExists(object $data): void
    {
        if (
            RequestApproval::whereApprovableId($data['id'])
            ->whereStatus(RequestApproval::STATUS_PENDING)
            ->exists()
        ) {
            throw new Exception('A similar request exists and is still pending.');
        }
    }
}
