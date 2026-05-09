<?php

namespace App\Models;

use App\Tec\Traits\LogActivity;
use App\Tec\Traits\Paginatable;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\SchemalessAttributes\SchemalessAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    use HasFactory;
    use LogActivity;
    use Paginatable;
    use SoftDeletes;

    public $casts = ['extra_attributes' => 'array'];

    public static $hasReference = false;

    public static $hasRegister = false;

    public static $hasStore = false;

    public static $hasSku = false;

    public static $hasUser = false;

    public static $userRecords = false;

    protected $guarded = [];

    protected static $logAttributesToIgnore = ['team_id'];

    protected static $logOnlyDirty = true;

    protected $setHash = false;

    protected static $submitEmptyLogs = false;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function getExtraAttributesAttribute(): SchemalessAttributes
    {
        return SchemalessAttributes::createForModel($this, 'extra_attributes');
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($this->getRouteKeyName(), $value)->withTrashed()->first();
    }

    public function scopeWithExtraAttributes(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('extra_attributes');
    }

    public function scopeSort($query, $sort)
    {
        if ($sort == 'latest') {
            $query->latest('id');
        } elseif (str($sort)->contains('.')) {
            [$relation, $column] = explode('.', $sort);
            [$column, $direction] = explode(':', $column);
            $query->withAggregate($relation, str($column)->replace(' ', '_'))->orderBy($relation . '_' . str($column)->replace(' ', '_'), $direction);
        } elseif ($sort) {
            [$column, $direction] = explode(':', $sort);
            $query->orderBy(str($column)->replace(' ', '_'), $direction ?: 'asc');
        } else {
            $query->latest('id');
        }
    }

    public function scopeTrashed($query, $value)
    {
        if (in_array($value, ['with', 'only'])) {
            return $query->{$value . 'Trashed'}();
        }

        return $query;
    }

    protected static function booted()
    {
        if (static::$userRecords) {
            $user = auth()->user();
            if ($user && ! $user->hasRole(['Super Admin', 'Customer', 'Supplier']) && $user->cant('read-all')) {
                static::addGlobalScope('mine', fn ($q) => $q->where('user_id', $user->id));
            }
        }

        static::creating(function ($model) {
            if ($model::$hasReference && ! $model->reference) {
                $model->reference = get_reference($model);
            }
            if ($model::$hasRegister && ! $model->register_id) {
                $model->register_id = session('open_register_id') ?: auth()->user()?->openedRegister?->id;
            }
            if ($model::$hasSku && ! $model->sku) {
                $model->sku = ulid();
            }
            if ($model::$hasStore && ! $model->store_id) {
                $model->store_id = session('selected_store_id');
            }
            if ($model->setHash && ! $model->hash) {
                $model->hash = uuid4();
            }
            if ($model::$hasUser && ! $model->user_id) {
                $model->user_id = auth()->id();
            }
        });
    }
}
