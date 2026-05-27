<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 64)->unique()->nullable(); // ID from source system
            $table->string('nisn', 20)->unique()->nullable();         // NISN (student number)
            $table->string('nis', 20)->nullable();                    // NIS (local school number)
            $table->string('name', 128);
            $table->string('email', 128)->unique()->nullable();
            $table->string('phone', 24)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 1)->nullable();                  // 'M' or 'F'
            $table->foreignId('school_class_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_class_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
