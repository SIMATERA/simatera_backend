<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mahasiswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'nim';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'nim',
        'nama',
        'email',
        'prodi',
        'kamar_id',
        'tanggal_lahir',
        'tempat_lahir',
        'asal',
        'jenis_kelamin',  // Sesuai dengan kolom di migrasi, bukan 'gender'
        'golongan_ukt',
        'status',
        'user_id',
        'password',      // Tambahkan jika memang dibutuhkan
        'created_by'     // Tambahkan karena ada di migrasi
    ];

    protected $hidden = [
        'password',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke kamar (perbaikan relasi menggunakan kamar_id)
    public function kamar()
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }

    // Relasi ke user yang membuat data
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke pelanggaran
    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class, 'nim', 'nim');
    }

    // Relasi ke pembayaran
    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'nim', 'nim');
    }

    // Relasi ke pengaduan
    public function pengaduan()
    {
        return $this->hasMany(Pengaduan::class, 'nim', 'nim');
    }
    
    // Helper method untuk mendapatkan gedung
    public function getGedungAttribute()
    {
        return $this->kamar ? $this->kamar->gedung : null;
    }
    
    // Helper method untuk mendapatkan no_kamar
    public function getNoKamarAttribute()
    {
        return $this->kamar ? $this->kamar->no_kamar : null;
    }
}