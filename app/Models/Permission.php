<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['action', 'status'];

    public function modulePermissions()
    {
        return $this->hasMany(ModulePermission::class);
    }
}
