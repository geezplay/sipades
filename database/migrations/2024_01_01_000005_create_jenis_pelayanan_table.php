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
        Schema::create('jenis_pelayanan', function (Blueprint $table) {
            $table->string('id_pelayanan', 50)->primary();
            $table->string('nama_pelayanan', 150);
            $table->text('deskripsi')->nullable();
            $table->text('persyaratan')->nullable();
            $table->string('icon', 50)->nullable()->default('fa-file-lines');
            $table->json('formulir_schema')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_pelayanan');
    }
};
