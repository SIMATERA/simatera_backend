<?php

namespace App\Http\Controllers;

use App\Models\Pelanggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class PelanggaranController extends Controller
{
    public function index()
    {
        try {
            $pelanggaran = Pelanggaran::with(['mahasiswa', 'creator', 'approver'])
                ->latest()
                ->paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => $pelanggaran
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nim' => 'required|exists:mahasiswa,nim',
                'tanggal_pelanggaran' => 'required|date',
                'keterangan_pelanggaran' => 'required|string'
            ]);

            $pelanggaran = Pelanggaran::create([
                'nim' => $request->nim,
                'tanggal_pelanggaran' => $request->tanggal_pelanggaran,
                'keterangan_pelanggaran' => $request->keterangan_pelanggaran,
                'status' => 'pending',
                'created_by' => Auth::id()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pelanggaran berhasil ditambahkan',
                'data' => $pelanggaran
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menambahkan pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $pelanggaran = Pelanggaran::with(['mahasiswa', 'creator', 'approver'])
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $pelanggaran
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pelanggaran tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $pelanggaran = Pelanggaran::findOrFail($id);

            $request->validate([
                'tanggal_pelanggaran' => 'sometimes|required|date',
                'keterangan_pelanggaran' => 'sometimes|required|string'
            ]);

            $pelanggaran->update($request->only([
                'tanggal_pelanggaran',
                'keterangan_pelanggaran'
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Pelanggaran berhasil diperbarui',
                'data' => $pelanggaran
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $pelanggaran = Pelanggaran::findOrFail($id);
            $pelanggaran->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Pelanggaran berhasil dihapus'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        try {
            $pelanggaran = Pelanggaran::findOrFail($id);

            if ($pelanggaran->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pelanggaran sudah diproses sebelumnya'
                ], 400);
            }

            $pelanggaran->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pelanggaran berhasil disetujui',
                'data' => $pelanggaran
            ]);
        } catch (Exception $e) {
            return response()->json([   
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyetujui pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            $pelanggaran = Pelanggaran::findOrFail($id);

            if ($pelanggaran->status !== 'pending') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pelanggaran sudah diproses sebelumnya'
                ], 400);
            }

            $pelanggaran->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pelanggaran berhasil ditolak',
                'data' => $pelanggaran
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menolak pelanggaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}