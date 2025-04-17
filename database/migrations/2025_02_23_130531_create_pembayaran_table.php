<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->string('nim');
            $table->foreign('nim')->references('nim')->on('mahasiswa')->onDelete('cascade');
            $table->enum('status_pembayaran', ['belum_lunas', 'lunas'])->default('belum_lunas');
            $table->string('periode');
            $table->decimal('nominal', 10, 2);
            $table->enum('metode_pembayaran', ['transfer', 'tunai']);
            $table->date('tanggal_pembayaran');
            $table->string('bukti_pembayaran')->nullable(); // Tambahkan kolom bukti pembayaran
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users'); // Tambahkan created_by
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};