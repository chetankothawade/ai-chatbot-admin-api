<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Module extends Model
{
    use LogsActivity;

    protected $fillable = [
        'uuid',
        'parent_id',
        'name',
        'url',
        'icon',
        'seq_no',
        'is_sub_module',
        'status',
        'is_permission'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function subModules()
    {
        return $this->hasMany(Module::class, 'parent_id');
    }

    public function modulePermissions()
    {
        return $this->hasMany(ModulePermission::class);
    }

    public function roleModules()
    {
        return $this->hasMany(RoleModule::class, 'module_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('module')
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => $this->getActivityDescription($eventName));
    }

    public function getActivityDescription(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'Module created',
            'updated' => 'Module updated',
            'deleted' => 'Module deleted',
            'restored' => 'Module restored',
            'activated' => 'Module activated',
            'deactivated' => 'Module deactivated',
            default => "Module {$eventName}",
        };
    }
}
