<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PembayaranController extends Controller
{
    /**
     * Display a listing of pembayaran.
     */
    public function index(Request $request)
    {
        try {
            $query = Pembayaran::with('mahasiswa');

            // Filter berdasarkan NIM
            if ($request->has('nim')) {
                $query->where('nim', $request->nim);
            }

            // Filter berdasarkan status pembayaran
            if ($request->has('status_pembayaran')) {
                $query->where('status_pembayaran', $request->status_pembayaran);
            }

            // Filter berdasarkan gedung
            if ($request->has('gedung')) {
                $query->where('gedung', $request->gedung);
            }

            // Filter berdasarkan periode
            if ($request->has('periode_pembayaran')) {
                $query->where('periode_pembayaran', $request->periode_pembayaran);
            }

            // Filter berdasarkan range tanggal
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('tanggal_pembayaran', [$request->start_date, $request->end_date]);
            }

            $pembayaran = $query->latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data' => $pembayaran
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created pembayaran.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nim' => 'required|exists:mahasiswa,nim',
                'gedung' => ['required', Rule::in(['tb1', 'tb2', 'tb3', 'tb4', 'tb5'])],
                'no_kamar' => 'required|string',
                'status_pembayaran' => ['required', Rule::in(['Lunas', 'Belum Lunas'])],
                'periode_pembayaran' => 'required|string',
                'nominal_pembayaran' => 'required|numeric|min:0',
                'metode_pembayaran' => ['required', Rule::in(['Transfer', 'Tunai'])],
                'tanggal_pembayaran' => 'required|date',
                'catatan_pembayaran' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify mahasiswa exists
            $mahasiswa = Mahasiswa::where('nim', $request->nim)->first();
            if (!$mahasiswa) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mahasiswa tidak ditemukan'
                ], 404);
            }

            $pembayaran = Pembayaran::create($request->all());
            $pembayaran->load('mahasiswa');

            return response()->json([
                'status' => true,
                'message' => 'Data pembayaran berhasil ditambahkan',
                'data' => $pembayaran
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan data pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified pembayaran.
     */
    public function show($id)
    {
        try {
            $pembayaran = Pembayaran::with('mahasiswa')->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $pembayaran
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data pembayaran tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified pembayaran.
     */
    public function update(Request $request, $id)
    {
        try {
            $pembayaran = Pembayaran::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status_pembayaran' => ['required', Rule::in(['Lunas', 'Belum Lunas'])],
                'nominal_pembayaran' => 'required|numeric|min:0',
                'metode_pembayaran' => ['required', Rule::in(['Transfer', 'Tunai'])],
                'tanggal_pembayaran' => 'required|date',
                'catatan_pembayaran' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $pembayaran->update($request->all());
            $pembayaran->load('mahasiswa');

            return response()->json([
                'status' => true,
                'message' => 'Data pembayaran berhasil diupdate',
                'data' => $pembayaran
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengupdate data pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified pembayaran.
     */
    public function destroy($id)
    {
        try {
            $pembayaran = Pembayaran::findOrFail($id);
            $pembayaran->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data pembayaran berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pembayaran history by mahasiswa NIM
     */
    public function getByNim($nim)
    {
        try {
            $pembayaran = Pembayaran::with('mahasiswa')
                ->where('nim', $nim)
                ->latest()
                ->get();

            return response()->json([
                'status' => true,
                'data' => $pembayaran
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}