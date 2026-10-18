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
        Schema::table('terms', function (Blueprint $table) {
            if (! Schema::hasColumn('terms', 'status')) {
                $table->string('status', 20)->default('belum_aktif')->after('is_active');
            }
            if (! Schema::hasColumn('terms', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('status');
            }
        });

        // Sync status with existing is_active
        DB::table('terms')->where('is_active', true)->update(['status' => 'aktif']);

        // Safely add unique constraint on academic_year and semester_type if not present
        if (! Schema::hasIndex('terms', ['academic_year', 'semester_type'])) {
            $duplicates = DB::table('terms')
                ->select('academic_year', 'semester_type', DB::raw('count(*) as total'))
                ->groupBy('academic_year', 'semester_type')
                ->having('total', '>', 1)
                ->exists();

            if (! $duplicates) {
                Schema::table('terms', function (Blueprint $table) {
                    $table->unique(['academic_year', 'semester_type']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terms', function (Blueprint $table) {
            if (Schema::hasColumn('terms', 'status')) {
                $table->dropColumn(['status']);
            }
            if (Schema::hasColumn('terms', 'closed_at')) {
                $table->dropColumn(['closed_at']);
            }
            if (Schema::hasIndex('terms', ['academic_year', 'semester_type'])) {
                $table->dropUnique(['academic_year', 'semester_type']);
            }
        });
    }
};
