<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasIndex('classes', ['course_id', 'term_id', 'section'])) {
            $duplicates = DB::table('classes')
                ->select('course_id', 'term_id', 'section', DB::raw('count(*) as total'))
                ->groupBy('course_id', 'term_id', 'section')
                ->having('total', '>', 1)
                ->exists();

            if (! $duplicates) {
                Schema::table('classes', function (Blueprint $table) {
                    $table->unique(['course_id', 'term_id', 'section']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasIndex('classes', ['course_id', 'term_id', 'section'])) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropUnique(['course_id', 'term_id', 'section']);
            });
        }
    }
};
