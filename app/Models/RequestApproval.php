<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};

class RequestApproval extends Model
{
    use HasFactory;

    public const REQUEST_TYPE_CREATING = 'creating';
    public const REQUEST_TYPE_UPDATING = 'updating';
    public const REQUEST_TYPE_DELETING = 'deleting';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';

    public const ACTION_DECLINE = 'decline';
    public const ACTION_APPROVE = 'approve';

    protected $guarded = [];

    protected $casts = ['data' => 'json'];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'requested_id');
    }
}
