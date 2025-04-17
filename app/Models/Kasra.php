<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kasra extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'nim';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nim',
        'nama',
        'prodi',
        'gedung',
        'no_kamar',
        'email',
        'tanggal_lahir',
        'tempat_lahir',
        'asal',
        'status',
        'golongan_ukt',
        'user_id'
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke gedung yang dikelola
    public function gedungDikelola()
    {
        return $this->hasOne(Kamar::class, 'gedung', 'gedung');
    }
}