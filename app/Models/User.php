<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',          // Tambahkan kolom nama
        'username',
        'email',
        'password',
        'role',
        'is_active'      // Tambahkan kolom is_active
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    // Relasi one-to-one dengan mahasiswa
    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    // Relasi one-to-one dengan kasra
    public function kasra()
    {
        return $this->hasOne(Kasra::class);
    }

    // Relasi untuk pengumuman yang dibuat
    public function pengumuman()
    {
        return $this->hasMany(Pengumuman::class, 'created_by');
    }

    // Relasi untuk jadwal kegiatan yang dibuat
    public function jadwalKegiatan()
    {
        return $this->hasMany(JadwalKegiatan::class, 'created_by');
    }

    // Relasi untuk pelanggaran yang dibuat
    public function pelanggaranDibuat()
    {
        return $this->hasMany(Pelanggaran::class, 'created_by');
    }

    // Relasi untuk pelanggaran yang disetujui
    public function pelanggaranDisetujui()
    {
        return $this->hasMany(Pelanggaran::class, 'approved_by');
    }

    // Helper methods untuk cek role
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isKasra()
    {
        return $this->role === 'kasra';
    }

    public function isMahasiswa()
    {
        return $this->role === 'mahasiswa';
    }
}