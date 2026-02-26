<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $fillable = ['user_id', 'module_permission_id'];

    public function modulePermission()
    {
        return $this->belongsTo(ModulePermission::class);
    }
}
