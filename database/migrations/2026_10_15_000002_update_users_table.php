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
        Schema::table('users', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable()->unique()->after('id');
            $table->foreignId('campus_id')->nullable()->after('ulid')->constrained('campuses')->nullOnDelete();
            $table->string('role')->default('student')->after('email');
            $table->string('identifier')->nullable()->after('role'); // NIM / NIDN / NIP
            $table->string('avatar_path')->nullable()->after('identifier');
            $table->string('phone', 30)->nullable()->after('avatar_path');
            $table->string('locale', 10)->default('id')->after('phone');
            $table->string('timezone', 50)->default('Asia/Jakarta')->after('locale');
            $table->json('settings')->nullable()->after('timezone');
            $table->boolean('is_active')->default(true)->after('settings');
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['campus_id']);
            $table->dropColumn([
                'ulid',
                'campus_id',
                'role',
                'identifier',
                'avatar_path',
                'phone',
                'locale',
                'timezone',
                'settings',
                'is_active',
                'deleted_at',
            ]);
        });
    }
};
