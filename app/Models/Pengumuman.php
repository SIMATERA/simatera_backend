<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    protected $fillable = [
        'judul_pengumuman',
        'isi_pengumuman',
        'tanggal_pengumuman',
        'file_pengumuman',
        'created_by'
    ];

    protected $casts = [
        'tanggal_pengumuman' => 'date'
    ];

    /**
     * Get the user that created the pengumuman
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}