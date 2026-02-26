<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;

class RoleModule extends Model
{
    protected $table = 'role_modules';

    protected $fillable = [
        'role',
        'module_id',
    ];

    /**
     * Disable guarded timestamps handling
     * (keep enabled if you use created_at / updated_at)
     */
    public $timestamps = true;

    /**
     * Role constants (avoid magic strings)
     */
    public const ROLE_SUPER_ADMIN = UserRole::SUPER_ADMIN->value;
    public const ROLE_ADMIN = UserRole::ADMIN->value;
    public const ROLE_USER = UserRole::USER->value;

    /**
     * Get all available roles
     */
    public static function roles(): array
    {
        return UserRole::values();
    }

    /**
     * Relation: RoleModule -> Module
     */
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}

