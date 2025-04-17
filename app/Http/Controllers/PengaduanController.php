<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class PengaduanController extends Controller
{
    /**
     * Display a listing of pengaduan.
     */
    public function index(Request $request)
    {
        try {
            $query = Pengaduan::with('mahasiswa');

            // Filter berdasarkan tipe
            if ($request->has('tipe')) {
                $query->where('tipe', $request->tipe);
            }

            // Filter berdasarkan NIM
            if ($request->has('nim')) {
                $query->where('nim', $request->nim);
            }

            // Filter berdasarkan gedung
            if ($request->has('gedung')) {
                $query->where('gedung', $request->gedung);
            }

            // Filter berdasarkan status
            if ($request->has('status_pengaduan')) {
                $query->where('status_pengaduan', $request->status_pengaduan);
            }

            // Filter berdasarkan range tanggal
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('tanggal_pengaduan', [$request->start_date, $request->end_date]);
            }

            $pengaduan = $query->latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'Data pengaduan berhasil diambil',
                'data' => $pengaduan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data pengaduan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created pengaduan.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tipe' => ['required', Rule::in(['kasra', 'mahasiswa'])],
                'nim' => 'required|exists:mahasiswa,nim',
                'nama' => 'required|string',
                'gedung' => ['required', Rule::in(['tb1', 'tb2', 'tb3', 'tb4', 'tb5'])],
                'no_kamar' => 'required|string',
                'deskripsi_pengaduan' => 'required|string',
                'tanggal_pengaduan' => 'required|date',
                'status_pengaduan' => ['required', Rule::in(['Diterima', 'Diproses', 'Selesai'])],
                'foto_pengaduan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle file upload if photo exists
            $foto_path = null;
            if ($request->hasFile('foto_pengaduan')) {
                $foto = $request->file('foto_pengaduan');
                $foto_path = $foto->store('pengaduan', 'public');
            }

            $pengaduan = Pengaduan::create([
                ...$request->except('foto_pengaduan'),
                'foto_pengaduan' => $foto_path
            ]);

            $pengaduan->load('mahasiswa');

            return response()->json([
                'status' => true,
                'message' => 'Pengaduan berhasil ditambahkan',
                'data' => $pengaduan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan pengaduan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified pengaduan.
     */
    public function show($id)
    {
        try {
            $pengaduan = Pengaduan::with('mahasiswa')->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $pengaduan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Pengaduan tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified pengaduan.
     */
    public function update(Request $request, $id)
    {
        try {
            $pengaduan = Pengaduan::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status_pengaduan' => ['required', Rule::in(['Diterima', 'Diproses', 'Selesai'])],
                'deskripsi_pengaduan' => 'required|string',
                'foto_pengaduan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle file upload if new photo exists
            if ($request->hasFile('foto_pengaduan')) {
                // Delete old photo if exists
                if ($pengaduan->foto_pengaduan) {
                    Storage::disk('public')->delete($pengaduan->foto_pengaduan);
                }
                
                $foto = $request->file('foto_pengaduan');
                $foto_path = $foto->store('pengaduan', 'public');
                $pengaduan->foto_pengaduan = $foto_path;
            }

            $pengaduan->status_pengaduan = $request->status_pengaduan;
            $pengaduan->deskripsi_pengaduan = $request->deskripsi_pengaduan;
            $pengaduan->save();

            $pengaduan->load('mahasiswa');

            return response()->json([
                'status' => true,
                'message' => 'Pengaduan berhasil diupdate',
                'data' => $pengaduan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengupdate pengaduan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified pengaduan.
     */
    public function destroy($id)
    {
        try {
            $pengaduan = Pengaduan::findOrFail($id);
            
            // Delete photo if exists
            if ($pengaduan->foto_pengaduan) {
                Storage::disk('public')->delete($pengaduan->foto_pengaduan);
            }
            
            $pengaduan->delete();

            return response()->json([
                'status' => true,
                'message' => 'Pengaduan berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus pengaduan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pengaduan by mahasiswa NIM
     */
    public function getByNim($nim)
    {
        try {
            $pengaduan = Pengaduan::with('mahasiswa')
                ->where('nim', $nim)
                ->latest()
                ->get();

            return response()->json([
                'status' => true,
                'data' => $pengaduan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data pengaduan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}