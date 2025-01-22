<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatHistory extends Model
{
    use HasFactory;

    protected $table = 'chat_histories';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'session_id',
        'thread_id',
        'messages',
    ];

    protected $casts = [
        'messages' => 'array',
    ];

    protected $dates = ['created_at', 'updated_at'];

            // Automatically generate UUIDs for new records
    public static function boot()
    {
        parent::boot();

        static::creating(function ($chatHistory) {
            if (!$chatHistory->id) {
                $chatHistory->id = (string) Str::uuid(); // Generate UUID if it's not set
            }
        });
    }
}
