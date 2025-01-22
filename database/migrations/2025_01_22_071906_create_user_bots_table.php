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
        Schema::create('user_bots', function (Blueprint $table) {
            $table->uuid('id')->primary();  // UUID as the primary key
            $table->uuid('user_id');        // Foreign key to users table
            $table->uuid('bot_id');         // Foreign key to bots table
            $table->string('name');         // Name of the bot
            $table->integer('tokens_remaining');
            $table->integer('tokens_used');
            $table->timestamps();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bots');
    }
};
