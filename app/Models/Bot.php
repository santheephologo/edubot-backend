<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bot extends Model
{
    use HasFactory;

    protected $table = 'bots';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'assistant_id',
        'is_active',
        'default_tokens',
    ];

    protected $dates = ['created_at', 'updated_at'];

    // Automatically generate UUIDs for new records
    public static function boot()
    {
        parent::boot();

        static::creating(function ($bot) {
            if (!$bot->id) {
                $bot->id = (string) Str::uuid(); // Generate UUID if it's not set
            }
        });
    }
}
