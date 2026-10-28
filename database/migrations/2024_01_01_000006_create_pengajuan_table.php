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
        Schema::create('pengajuan', function (Blueprint $table) {
            $table->string('id_pengajuan', 36)->primary();
            $table->string('nomor_tiket', 30)->unique();
            $table->string('id_masyarakat', 36);
            $table->string('id_pelayanan', 50);
            $table->timestamp('tanggal_pengajuan')->useCurrent();
            $table->enum('status', [
                'DIAJUKAN',
                'DIPERIKSA',
                'REVISI',
                'DIVERIFIKASI',
                'MENUNGGU_PERSETUJUAN',
                'DISETUJUI',
                'DITOLAK',
                'DIPROSES',
                'SELESAI',
            ])->default('DIAJUKAN');
            $table->json('data_formulir')->nullable();
            $table->text('catatan')->nullable();
            $table->text('catatan_revisi')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();

            $table->foreign('id_masyarakat')->references('id_masyarakat')->on('masyarakat')->onDelete('restrict');
            $table->foreign('id_pelayanan')->references('id_pelayanan')->on('jenis_pelayanan')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan');
    }
};
