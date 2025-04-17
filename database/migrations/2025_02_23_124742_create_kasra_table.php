<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kasra', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('nama');
            $table->string('prodi');
            // Hapus kolom gedung dan no_kamar, ganti dengan foreign key ke table kamar
            $table->foreignId('kamar_id')->constrained('kamar');
            $table->string('email')->unique(); // Tambahkan unique constraint
            $table->date('tanggal_lahir');
            $table->string('tempat_lahir');
            $table->string('asal');
            $table->enum('status', ['Aktif Tinggal', 'Checkout']);
            $table->enum('golongan_ukt', ['1', '2', '3', '4', '5', '6', '7', '8']); // Sesuaikan dengan format yang sama seperti mahasiswa
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('password');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Tambahkan relasi ke users
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes(); // Tambahkan soft delete
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kasra');
    }
};