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
        Schema::create('kepala_desa_lurah', function (Blueprint $table) {
            $table->string('id_pimpinan', 36)->primary();
            $table->string('id_user', 36)->unique();
            $table->string('jabatan', 100)->default('Kepala Desa');
            $table->string('nip', 30)->nullable();
            $table->string('tanda_tangan_digital', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kepala_desa_lurah');
    }
};
