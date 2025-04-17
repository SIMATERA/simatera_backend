<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'nim',
        'gedung',
        'no_kamar',
        'status_pembayaran',
        'periode_pembayaran',
        'nominal_pembayaran',
        'metode_pembayaran',
        'tanggal_pembayaran',
        'catatan_pembayaran'
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'date',
        'nominal_pembayaran' => 'double'
    ];

    /**
     * Get the mahasiswa that owns the pembayaran
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }
}