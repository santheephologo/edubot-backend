<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserBot extends Model
{
    use HasFactory;

    protected $table = 'user_bots';

    protected $primaryKey = 'id';

    public $incrementing = false;


    protected $fillable = [
        'user_id',
        'bot_id',
        'name',
        'tokens_remaining',
        'tokens_used',
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bot()
    {
        return $this->belongsTo(Bot::class, 'bot_id');
    }

            // Automatically generate UUIDs for new records
    public static function boot()
    {
        parent::boot();

        static::creating(function ($usetBot) {
            if (!$usetBot->id) {
                $usetBot->id = (string) Str::uuid(); // Generate UUID if it's not set
            }
        });
    }
}
