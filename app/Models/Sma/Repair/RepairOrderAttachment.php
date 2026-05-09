<?php

namespace App\Models\Sma\Repair;

use App\Models\User;
use App\Models\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RepairOrderAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_order_id',
        'filename',
        'original_filename',
        'mime_type',
        'path',
        'size',
        'description',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function repairOrder()
    {
        return $this->belongsTo(RepairOrder::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    // public function getFormattedSizeAttribute(): string
    // {
    //     $bytes = $this->size;
    //     $units = ['B', 'KB', 'MB', 'GB'];

    //     for ($i = 0; $bytes > 1024; $i++) {
    //         $bytes /= 1024;
    //     }

    //     return round($bytes, 2) . ' ' . $units[$i];
    // }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($attachment) {
            Storage::delete($attachment->path);
        });
    }
}
