<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KamarController extends Controller
{
    /**
     * Display a listing of kamar.
     */
    public function index(Request $request)
    {
        try {
            $query = Kamar::query();

            // Filter berdasarkan gedung
            if ($request->has('gedung')) {
                $query->where('gedung', $request->gedung);
            }

            // Filter berdasarkan lantai
            if ($request->has('lantai')) {
                $query->where('lantai', $request->lantai);
            }

            // Filter berdasarkan status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter kamar tersedia
            if ($request->has('available') && $request->available) {
                $query->where('status', 'tersedia')
                      ->whereRaw('terisi < kapasitas');
            }

            $kamar = $query->get();

            return response()->json([
                'status' => true,
                'message' => 'Data kamar berhasil diambil',
                'data' => $kamar
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data kamar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created kamar.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'gedung' => ['required', Rule::in(['tb1', 'tb2', 'tb3', 'tb4', 'tb5'])],
                'no_kamar' => [
                    'required',
                    Rule::unique('kamar')->where(function ($query) use ($request) {
                        return $query->where('gedung', $request->gedung);
                    })
                ],
                'lantai' => 'required|integer|min:1',
                'status' => ['required', Rule::in(['tersedia', 'terisi', 'perbaikan', 'tidak_tersedia'])],
                'kapasitas' => 'required|integer|min:1',
                'terisi' => 'required|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate terisi tidak melebihi kapasitas
            if ($request->terisi > $request->kapasitas) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jumlah terisi tidak boleh melebihi kapasitas kamar'
                ], 422);
            }

            $kamar = Kamar::create($request->all());

            return response()->json([
                'status' => true,
                'message' => 'Data kamar berhasil ditambahkan',
                'data' => $kamar
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan data kamar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified kamar.
     */
    public function show($id)
    {
        try {
            $kamar = Kamar::with(['mahasiswa', 'kasra'])->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $kamar
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data kamar tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified kamar.
     */
    public function update(Request $request, $id)
{
    try {
        $kamar = Kamar::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'gedung' => ['required', Rule::in(['tb1', 'tb2', 'tb3', 'tb4', 'tb5'])],
            'no_kamar' => [
                'required',
                Rule::unique('kamar')->where(function ($query) use ($request, $id) {
                    return $query->where('gedung', $request->gedung)
                                 ->where('id', '!=', $id);
                })
            ],
            'lantai' => 'required|integer|min:1',
            'status' => ['required', Rule::in(['tersedia', 'terisi', 'perbaikan', 'tidak_tersedia'])],
            'kapasitas' => 'required|integer|min:1',
            'terisi' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate terisi tidak melebihi kapasitas
        if ($request->terisi > $request->kapasitas) {
            return response()->json([
                'status' => false,
                'message' => 'Jumlah terisi tidak boleh melebihi kapasitas kamar'
            ], 422);
        }

        $kamar->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Data kamar berhasil diupdate',
            'data' => $kamar
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Gagal mengupdate data kamar',
            'error' => $e->getMessage()
        ], 500);
    }
}


    /**
 * Update the status of a specified kamar.
 */
public function updateStatus(Request $request, $id)
{
    try {
        $kamar = Kamar::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['tersedia', 'terisi', 'perbaikan', 'tidak_tersedia'])],
            'keterangan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi error',
                'errors' => $validator->errors()
            ], 422);
        }

        $kamar->status = $request->status; // Ensure status is updated

        // If there's a keterangan (for "perbaikan"), update it
        if ($request->has('keterangan')) {
            $kamar->keterangan = $request->keterangan;
        }

        $kamar->save();

        return response()->json([
            'status' => true,
            'message' => 'Status kamar berhasil diupdate',
            'data' => $kamar
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Gagal mengupdate status kamar',
            'error' => $e->getMessage()
        ], 500);
    }
}


    /**
     * Remove the specified kamar.
     */
    public function destroy($id)
    {
        try {
            $kamar = Kamar::findOrFail($id);
            
            // Check if kamar masih terisi
            if ($kamar->terisi > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kamar tidak dapat dihapus karena masih terisi'
                ], 422);
            }

            $kamar->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data kamar berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data kamar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available rooms by gender.
     */
    public function getAvailableRooms($gender)
    {
        try {
            $gedungByGender = $gender === 'Laki-laki' ? ['tb2', 'tb3'] : ['tb1', 'tb4', 'tb5'];

            $availableRooms = Kamar::whereIn('gedung', $gedungByGender)
                ->where('status', 'tersedia')
                ->whereRaw('terisi < kapasitas')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $availableRooms
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data kamar tersedia',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}