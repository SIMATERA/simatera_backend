<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->string('no_kamar');
            $table->enum('gedung', ['tb1', 'tb2', 'tb3', 'tb4', 'tb5']);
            $table->integer('lantai');
            $table->enum('status', ['tersedia', 'tidak_tersedia', 'terisi', 'perbaikan'])->default('tersedia');
            $table->integer('kapasitas');
            $table->integer('terisi')->default(0);
            $table->text('keterangan')->nullable(); // Tambahan untuk mencatat informasi tambahan
            $table->foreignId('created_by')->constrained('users'); // Tambahan untuk audit trail
            $table->foreignId('updated_by')->nullable()->constrained('users'); // Tambahan untuk audit trail
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint
            $table->unique(['gedung', 'no_kamar']);
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};