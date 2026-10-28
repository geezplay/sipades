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
        Schema::create('dokumen', function (Blueprint $table) {
            $table->string('id_dokumen', 36)->primary();
            $table->string('id_pengajuan', 36);
            $table->string('nama_dokumen', 150);
            $table->string('file_path', 255);
            $table->unsignedInteger('file_size');
            $table->string('mime_type', 100);
            $table->timestamp('tanggal_upload')->useCurrent();
            $table->timestamps();

            $table->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen');
    }
};
