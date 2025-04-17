<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Kasra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KasraController extends Controller
{
    /**
     * Display a listing of kasra.
     */
    public function index(Request $request)
    {
        try {
            $query = Kasra::query();

            // Filter berdasarkan status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter berdasarkan gedung
            if ($request->has('gedung')) {
                $query->where('gedung', $request->gedung);
            }

            // Filter berdasarkan jenis kelamin
            if ($request->has('jenis_kelamin')) {
                $query->where('jenis_kelamin', $request->jenis_kelamin);
            }

            $kasra = $query->get();

            return response()->json([
                'status' => true,
                'message' => 'Data kasra berhasil diambil',
                'data' => $kasra
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data kasra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created kasra.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nim' => 'required|unique:kasra,nim',
                'nama' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'gedung' => ['required', Rule::in(['tb1', 'tb2', 'tb3', 'tb4', 'tb5'])],
                'no_kamar' => 'required|string',
                'email' => 'required|email|unique:kasra,email',
                'tanggal_lahir' => 'required|date',
                'tempat_lahir' => 'required|string',
                'asal' => 'required|string',
                'status' => ['required', Rule::in(['Aktif Tinggal', 'Checkout'])],
                'golongan_ukt' => 'required|string',
                'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
                'password' => 'required|min:6'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $kasra = Kasra::create([
                ...$request->except('password'),
                'password' => Hash::make($request->password),
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data kasra berhasil ditambahkan',
                'data' => $kasra
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan data kasra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified kasra.
     */
    public function show($id)
    {
        try {
            $kasra = Kasra::findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $kasra
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data kasra tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified kasra.
     */
    public function update(Request $request, $id)
    {
        try {
            $kasra = Kasra::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nim' => ['required', Rule::unique('kasra')->ignore($id)],
                'nama' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'gedung' => ['required', Rule::in(['tb1', 'tb2', 'tb3', 'tb4', 'tb5'])],
                'no_kamar' => 'required|string',
                'email' => ['required', 'email', Rule::unique('kasra')->ignore($id)],
                'tanggal_lahir' => 'required|date',
                'tempat_lahir' => 'required|string',
                'asal' => 'required|string',
                'status' => ['required', Rule::in(['Aktif Tinggal', 'Checkout'])],
                'golongan_ukt' => 'required|string',
                'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->except('password');
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $kasra->update($updateData);

            return response()->json([
                'status' => true,
                'message' => 'Data kasra berhasil diupdate',
                'data' => $kasra
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengupdate data kasra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified kasra.
     */
    public function destroy($id)
    {
        try {
            $kasra = Kasra::findOrFail($id);
            $kasra->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data kasra berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data kasra',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}