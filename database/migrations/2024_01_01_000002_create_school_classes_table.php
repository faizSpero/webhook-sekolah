<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name', 128);
            $table->string('grade_level', 16)->nullable();  // e.g. '10', '11', '12'
            $table->string('academic_year', 16)->nullable(); // e.g. '2024/2025'
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
