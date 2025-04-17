<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalKegiatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jadwal_kegiatan';

    protected $fillable = [
        'judul_kegiatan',
        'deskripsi_kegiatan',
        'tanggal_kegiatan',
        'file_kegiatan',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'date'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_kegiatan', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('tanggal_kegiatan', '<', now());
    }
}