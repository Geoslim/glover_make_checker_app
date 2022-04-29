<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\HasMany};

class Role extends Model
{
    use HasFactory;

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class);
    }
}
