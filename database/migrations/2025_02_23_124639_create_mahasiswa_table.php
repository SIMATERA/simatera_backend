<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('mahasiswa');
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->string('nim')->primary();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('prodi');
            // Hapus kolom gedung dan no_kamar, ganti dengan foreign key ke table kamar
            $table->foreignId('kamar_id')->constrained('kamar');
            $table->date('tanggal_lahir');
            $table->string('tempat_lahir');
            $table->string('asal');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->enum('golongan_ukt', ['1', '2', '3', '4', '5', '6', '7', '8']);
            $table->enum('status', ['Aktif Tinggal', 'Tidak Aktif'])->default('Aktif Tinggal');
            $table->string('password')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            // Hapus unique constraint untuk gedung dan no_kamar karena sudah menggunakan kamar_id
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};