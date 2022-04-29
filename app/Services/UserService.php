<?php

namespace App\Services;

use App\Models\RequestApproval;
use App\Models\User;

class UserService
{
    public static function createUser(array $data, $event)
    {
        return match ($event) {
            false => User::withoutEvents(function () use ($data) {
                return User::create($data);
            }),
            default => User::create($data)
        };
    }

    public static function updateUser(User|int $user, array $data, $event)
    {
        return match ($event) {
            false => User::withoutEvents(function () use ($user, $data) {
                $user = User::find($user);
                return $user->update($data);
            }),
            default => $user->update($data)
        };
    }

    public static function deleteUser(User|int $user, $event)
    {
        return match ($event) {
            false => User::withoutEvents(function () use ($user) {
                $user = User::find($user);
                return $user->delete();
            }),
            default => $user->delete()
        };
    }

    /**
     * @param RequestApproval $requestApproval
     */
    public static function approveRequest(RequestApproval $requestApproval): void
    {
        match ($requestApproval['request_type']) {
            RequestApproval::REQUEST_TYPE_CREATING
            => self::createUser($requestApproval['data'], false),
            RequestApproval::REQUEST_TYPE_UPDATING
            => self::updateUser($requestApproval['approvable_id'], $requestApproval['data'], false),
            RequestApproval::REQUEST_TYPE_DELETING
            => self::deleteUser($requestApproval['approvable_id'], false),
        };
    }
}
