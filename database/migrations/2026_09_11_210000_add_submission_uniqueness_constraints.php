<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->unique(['assignment_id', 'student_id'], 'assignment_submissions_assignment_student_unique');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->unique(['exam_id', 'student_id'], 'exam_results_exam_student_unique');
        });
    }

    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropUnique('assignment_submissions_assignment_student_unique');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropUnique('exam_results_exam_student_unique');
        });
    }
};
