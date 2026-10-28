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
        Schema::create('surat', function (Blueprint $table) {
            $table->string('id_surat', 36)->primary();
            $table->string('id_pengajuan', 36)->unique();
            $table->string('nomor_surat', 100)->unique();
            $table->date('tanggal_surat');
            $table->string('id_pimpinan_penandatangan', 36);
            $table->string('file_pdf_path', 255)->nullable();
            $table->string('qr_token', 64)->unique();
            $table->timestamps();

            $table->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan')->onDelete('restrict');
            $table->foreign('id_pimpinan_penandatangan')->references('id_pimpinan')->on('kepala_desa_lurah')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
