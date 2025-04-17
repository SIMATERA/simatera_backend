<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    use HasFactory;

    protected $table = 'pengaduan';

    protected $fillable = [
        'tipe',
        'nim',
        'nama',
        'gedung',
        'no_kamar',
        'deskripsi_pengaduan',
        'tanggal_pengaduan',
        'status_pengaduan',
        'foto_pengaduan'
    ];

    protected $casts = [
        'tanggal_pengaduan' => 'date'
    ];

    /**
     * Get the mahasiswa that owns the pengaduan
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }
}