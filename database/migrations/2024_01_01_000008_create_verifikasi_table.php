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
        Schema::create('verifikasi', function (Blueprint $table) {
            $table->string('id_verifikasi', 36)->primary();
            $table->string('id_pengajuan', 36);
            $table->string('id_petugas', 36);
            $table->timestamp('tanggal_verifikasi')->useCurrent();
            $table->enum('hasil', ['TERVERIFIKASI', 'REVISI', 'DITOLAK']);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan')->onDelete('cascade');
            $table->foreign('id_petugas')->references('id_petugas')->on('petugas')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifikasi');
    }
};
