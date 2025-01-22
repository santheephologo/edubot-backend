<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Addon extends Model
{
    use HasFactory;

    protected $table = 'addons';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'tokens',
        'price',
    ];

    protected $dates = ['created_at', 'updated_at'];

        // Automatically generate UUIDs for new records
    public static function boot()
    {
        parent::boot();

        static::creating(function ($addon) {
            if (!$addon->id) {
                $addon->id = (string) Str::uuid(); // Generate UUID if it's not set
            }
        });
    }
}
