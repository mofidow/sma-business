<?php

namespace App\Models;

use App\Tec\Casts\AppDate;
use App\Tec\Traits\LogActivity;
use App\Tec\Traits\Paginatable;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as BaseRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends BaseRole
{
    use HasFactory;
    use LogActivity;
    use Paginatable;
    use SoftDeletes;

    // protected $hidden = ['pivot'];

    protected function casts(): array
    {
        return [
            'created_at' => AppDate::class . ':time',
            'updated_at' => AppDate::class . ':time',
        ];
    }

    public function savePermissions($permissions)
    {
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->syncPermissions($permissions);
    }

    public function delete()
    {
        if ($this->users()->exists() || in_array($this->name, ['Super Admin', 'Customer', 'Supplier'])) {
            return false;
        }

        return parent::delete();
    }

    public function forceDelete()
    {
        if ($this->name == 'Super Admin') {
            return false;
        }
        log_activity(__('{model} has been successfully {action}.', [
            'model'  => __('Role'),
            'action' => __('deleted'),
        ]), $this, $this, 'Role');
        $this->users()->detach();

        return parent::forceDelete();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return in_array(SoftDeletes::class, class_uses($this))
            ? $this->where($this->getRouteKeyName(), $value)->withTrashed()->first()
            : parent::resolveRouteBinding($value);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }
}
