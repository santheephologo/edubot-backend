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
    Schema::create('bots', function (Blueprint $table) {
        $table->uuid('id')->primary(); // UUID as the primary key
        $table->string('name');
        $table->string('assistant_id');
        $table->boolean('is_active')->default(true);
        $table->integer('default_tokens')->default(0);
        $table->timestamps(); // created_at and updated_at
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bots');
    }
};
