<?php

namespace App\Http\Controllers;

use App\Models\JadwalKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Exception;

class JadwalKegiatanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = JadwalKegiatan::with(['creator', 'updater']);

            // Filter berdasarkan tanggal
            if ($request->has('filter')) {
                switch ($request->filter) {
                    case 'upcoming':
                        $query->upcoming();
                        break;
                    case 'past':
                        $query->past();
                        break;
                }
            }

            $jadwalKegiatan = $query->latest()->paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => $jadwalKegiatan
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data jadwal kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'judul_kegiatan' => 'required|string|max:255',
                'deskripsi_kegiatan' => 'required|string',
                'tanggal_kegiatan' => 'required|date',
                'file_kegiatan' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
            ]);

            $data = $request->all();
            $data['created_by'] = Auth::id();

            if ($request->hasFile('file_kegiatan')) {
                $path = $request->file('file_kegiatan')->store('kegiatan');
                $data['file_kegiatan'] = $path;
            }

            $jadwalKegiatan = JadwalKegiatan::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Jadwal kegiatan berhasil ditambahkan',
                'data' => $jadwalKegiatan
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menambahkan jadwal kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $jadwalKegiatan = JadwalKegiatan::with(['creator', 'updater'])
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $jadwalKegiatan
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jadwal kegiatan tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $jadwalKegiatan = JadwalKegiatan::findOrFail($id);

            $request->validate([
                'judul_kegiatan' => 'sometimes|required|string|max:255',
                'deskripsi_kegiatan' => 'sometimes|required|string',
                'tanggal_kegiatan' => 'sometimes|required|date',
                'file_kegiatan' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
            ]);

            $data = $request->except('file_kegiatan');
            $data['updated_by'] = Auth::id();

            if ($request->hasFile('file_kegiatan')) {
                // Hapus file lama jika ada
                if ($jadwalKegiatan->file_kegiatan) {
                    Storage::delete($jadwalKegiatan->file_kegiatan);
                }
                $path = $request->file('file_kegiatan')->store('kegiatan');
                $data['file_kegiatan'] = $path;
            }

            $jadwalKegiatan->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Jadwal kegiatan berhasil diperbarui',
                'data' => $jadwalKegiatan
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui jadwal kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $jadwalKegiatan = JadwalKegiatan::findOrFail($id);

            // Hapus file jika ada
            if ($jadwalKegiatan->file_kegiatan) {
                Storage::delete($jadwalKegiatan->file_kegiatan);
            }

            $jadwalKegiatan->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Jadwal kegiatan berhasil dihapus'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus jadwal kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadFile($id)
    {
        try {
            $jadwalKegiatan = JadwalKegiatan::findOrFail($id);

            if (!$jadwalKegiatan->file_kegiatan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            return Storage::download($jadwalKegiatan->file_kegiatan);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengunduh file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}