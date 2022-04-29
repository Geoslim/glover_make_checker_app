<?php

namespace App\Traits;

use App\Models\RequestApproval;
use App\Services\RequestApprovalService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Approvable
{
    public static function bootApprovable()
    {
        static::creating(function ($model) {
            RequestApprovalService::storeRequest(
                RequestApproval::REQUEST_TYPE_CREATING,
                $model
            );
            return false;
        });

        static::updating(function ($model) {
            RequestApprovalService::storeRequest(
                RequestApproval::REQUEST_TYPE_UPDATING,
                $model
            );
            return false;
        });

        static::deleting(function ($model) {
            RequestApprovalService::storeRequest(
                RequestApproval::REQUEST_TYPE_DELETING,
                $model
            );
            return false;
        });
    }

    public function approvals(): MorphMany
    {
        return $this->morphMany(RequestApproval::class, 'approvable');
    }
}
