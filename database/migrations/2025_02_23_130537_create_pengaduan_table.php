<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['mahasiswa', 'kasra']);
            $table->string('nim');
            $table->text('keterangan');
            $table->date('tanggal');
            $table->enum('status', ['belum_dikerjakan', 'sedang_dikerjakan', 'selesai'])->default('belum_dikerjakan');
            $table->string('gambar')->nullable();
            $table->foreignId('kamar_id')->constrained('kamar'); // Tambahkan referensi ke kamar
            $table->foreignId('created_by')->constrained('users'); // Tambahkan created_by
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
            $table->foreign('nim')->references('nim')->on('mahasiswa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaduan');
    }
};