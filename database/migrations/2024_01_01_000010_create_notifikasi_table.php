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
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->string('id_notifikasi', 36)->primary();
            $table->string('id_user', 36);
            $table->string('judul', 150);
            $table->text('pesan');
            $table->string('link_url', 255)->nullable();
            $table->boolean('status_baca')->default(false);
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
