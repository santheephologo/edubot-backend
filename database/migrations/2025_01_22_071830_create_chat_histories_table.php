<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chat_histories', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID as the primary key
            $table->string('session_id');   // Session ID
            $table->string('thread_id');    // Thread ID
            $table->longText('messages');   // Messages (JSON encoded)
            $table->timestamps();           // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_histories');
    }
};
