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
        Schema::table('enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('enrollments', 'dikeluarkan_at')) {
                $table->timestamp('dikeluarkan_at')->nullable()->after('letter_grade');
            }
            if (! Schema::hasColumn('enrollments', 'dikeluarkan_oleh')) {
                $table->foreignId('dikeluarkan_oleh')->nullable()->after('dikeluarkan_at')->constrained('users')->nullOnDelete();
            }
        });

        // Set status 'aktif' for rows that were 'enrolled'
        DB::table('enrollments')->where('status', 'enrolled')->update(['status' => 'aktif']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['dikeluarkan_oleh']);
            $table->dropColumn(['dikeluarkan_at', 'dikeluarkan_oleh']);
        });
    }
};
