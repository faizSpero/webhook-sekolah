<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_scores', function (Blueprint $table) {
            $table->string('semester')->nullable()->after('score_date');
            $table->string('tahun_akademik')->nullable()->after('semester');
        });
    }

    public function down(): void
    {
        Schema::table('student_scores', function (Blueprint $table) {
            $table->dropColumn(['semester', 'tahun_akademik']);
        });
    }
};
