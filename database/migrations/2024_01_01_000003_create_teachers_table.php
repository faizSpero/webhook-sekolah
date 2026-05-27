<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 64)->unique()->nullable(); // ID from source system
            $table->string('nip', 32)->unique()->nullable();          // NIP (National ID)
            $table->string('name', 128);
            $table->string('email', 128)->unique()->nullable();
            $table->string('phone', 24)->nullable();
            $table->string('subject', 128)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
