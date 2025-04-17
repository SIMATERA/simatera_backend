<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kamar extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'no_kamar',
        'gedung',
        'lantai',
        'status',
        'kapasitas',
        'terisi',
        'keterangan',
        'created_by',
        'updated_by'
    ];

    protected $table = 'kamar';

    protected static function boot()
{
    parent::boot();
    
    static::saving(function ($kamar) {
        if ($kamar->terisi > $kamar->kapasitas) {
            throw new \Exception("Jumlah terisi tidak boleh melebihi kapasitas kamar");
        }

        // If status is not explicitly set in the request, update it automatically
        if (!$kamar->status) {  // Only update if the status is not explicitly set
            if ($kamar->terisi >= $kamar->kapasitas) {
                $kamar->status = 'terisi';
            } elseif ($kamar->terisi == 0) {
                $kamar->status = 'tersedia';
            } elseif ($kamar->status == 'tidak_tersedia') {
                $kamar->status = 'tidak_tersedia'; // Fallback for other cases
            }else {
                $kamar->status = 'perbaikan';
            }
        }
        
        return true;
    });
}

    // Relasi ke mahasiswa yang menempati
    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'kamar_id', 'gedung');
    }

    // Relasi ke kasra yang mengelola
    public function kasra()
    {
        return $this->belongsTo(Kasra::class, 'kamar_id', 'gedung');
    }
}