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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 50); // e.g. buat, ubah, aktifkan, tutup, hapus, tambah_mahasiswa, keluarkan, pulihkan, impor
            $table->string('kategori', 30); // semester, mata_kuliah, kelas, pendaftaran, pengguna
            $table->string('subjek_type', 100)->nullable();
            $table->unsignedBigInteger('subjek_id')->nullable();
            $table->text('deskripsi');
            $table->json('properti')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            // Indexes for fast administrative audit lookups
            $table->index('user_id');
            $table->index('kategori');
            $table->index(['subjek_type', 'subjek_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
