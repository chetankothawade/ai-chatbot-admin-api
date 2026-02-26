<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulePermission extends Model
{
    protected $fillable = ['module_id', 'permission_id'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class);
    }
}
