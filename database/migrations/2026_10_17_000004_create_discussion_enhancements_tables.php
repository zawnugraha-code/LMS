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
        // 1. Extend discussion_threads with kategori and is_private
        if (Schema::hasTable('discussion_threads')) {
            Schema::table('discussion_threads', function (Blueprint $table) {
                if (! Schema::hasColumn('discussion_threads', 'kategori')) {
                    $table->string('kategori', 50)->nullable()->after('category_id')->index();
                }
                if (! Schema::hasColumn('discussion_threads', 'is_private')) {
                    $table->boolean('is_private')->default(false)->after('is_locked');
                }
            });
        }

        // 2. Create discussion_reads for tracking read status per user
        if (! Schema::hasTable('discussion_reads')) {
            Schema::create('discussion_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('thread_id')->constrained('discussion_threads')->cascadeOnDelete();
                $table->timestamp('read_at')->useCurrent();
                $table->unique(['user_id', 'thread_id']);
            });
        }

        // 3. Create discussion_favorites for starred/bookmarked threads
        if (! Schema::hasTable('discussion_favorites')) {
            Schema::create('discussion_favorites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('thread_id')->constrained('discussion_threads')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'thread_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussion_favorites');
        Schema::dropIfExists('discussion_reads');

        if (Schema::hasTable('discussion_threads')) {
            Schema::table('discussion_threads', function (Blueprint $table) {
                if (Schema::hasColumn('discussion_threads', 'kategori')) {
                    $table->dropColumn('kategori');
                }
                if (Schema::hasColumn('discussion_threads', 'is_private')) {
                    $table->dropColumn('is_private');
                }
            });
        }
    }
};
