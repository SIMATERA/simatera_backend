<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Kamar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class MahasiswaController extends Controller
{
    /**
     * Display a listing of mahasiswa.
     */
    public function index(Request $request)
    {
        try {
            $query = Mahasiswa::query();

            // Filter berdasarkan status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter berdasarkan kamar_id
            if ($request->has('kamar_id')) {
                $query->where('kamar_id', $request->kamar_id);
            }

            // Filter berdasarkan jenis_kelamin
            if ($request->has('jenis_kelamin')) {
                $query->where('jenis_kelamin', $request->jenis_kelamin);
            }

            $mahasiswa = $query->get();

            return response()->json([
                'status' => true,
                'message' => 'Data mahasiswa berhasil diambil',
                'data' => $mahasiswa
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created mahasiswa.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nim' => 'required|unique:mahasiswa,nim',
                'nama' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'kamar_id' => 'required|exists:kamar,id',
                'email' => 'required|email|unique:mahasiswa,email',
                'tanggal_lahir' => 'required|date',
                'tempat_lahir' => 'required|string',
                'asal' => 'required|string',
                'status' => ['required', Rule::in(['Aktif Tinggal', 'Tidak Aktif'])],
                'golongan_ukt' => ['required', Rule::in(['1', '2', '3', '4', '5', '6', '7', '8'])],
                'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
                'password' => 'required|min:6',
                'username' => 'required|unique:users,username'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cari kamar berdasarkan kamar_id
            $kamar = Kamar::findOrFail($request->kamar_id);
            
            // Check apakah kamar masih tersedia
            if ($kamar->terisi >= $kamar->kapasitas) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kamar sudah penuh'
                ], 400);
            }
            
            // Buat user untuk mahasiswa
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'nama' => $request->nama,
                'password' => Hash::make($request->password),
                'role' => 'mahasiswa',
                'is_active' => true
            ]);

            // Buat mahasiswa
            $mahasiswa = Mahasiswa::create([
                'nim' => $request->nim,
                'nama' => $request->nama,
                'prodi' => $request->prodi,
                'kamar_id' => $request->kamar_id,
                'email' => $request->email,
                'tanggal_lahir' => $request->tanggal_lahir,
                'tempat_lahir' => $request->tempat_lahir,
                'asal' => $request->asal,
                'status' => $request->status,
                'golongan_ukt' => $request->golongan_ukt,
                'jenis_kelamin' => $request->jenis_kelamin,
                'password' => Hash::make($request->password),
                'user_id' => $user->id,
                'created_by' => auth()->id() ?? 1
            ]);

            // Update terisi di kamar
            $kamar->terisi = $kamar->terisi + 1;
            if ($kamar->terisi >= $kamar->kapasitas) {
                $kamar->status = 'terisi';
            }
            $kamar->save();

            return response()->json([
                'status' => true,
                'message' => 'Data mahasiswa berhasil ditambahkan',
                'data' => $mahasiswa
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan data mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified mahasiswa.
     */
    public function show($nim)
    {
        try {
            $mahasiswa = Mahasiswa::where('nim', $nim)->firstOrFail();

            return response()->json([
                'status' => true,
                'data' => $mahasiswa
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data mahasiswa tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified mahasiswa.
     */
    public function update(Request $request, $nim)
    {
        try {
            $mahasiswa = Mahasiswa::where('nim', $nim)->firstOrFail();

            $validator = Validator::make($request->all(), [
                'nama' => 'string|max:255',
                'prodi' => 'string|max:255',
                'kamar_id' => 'exists:kamar,id',
                'email' => ['email', Rule::unique('mahasiswa')->ignore($nim, 'nim')],
                'tanggal_lahir' => 'date',
                'tempat_lahir' => 'string',
                'asal' => 'string',
                'status' => [Rule::in(['Aktif Tinggal', 'Tidak Aktif'])],
                'golongan_ukt' => [Rule::in(['1', '2', '3', '4', '5', '6', '7', '8'])],
                'jenis_kelamin' => [Rule::in(['Laki-laki', 'Perempuan'])]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->except(['password', 'nim']);
            
            // Jika ada perubahan kamar
            if ($request->has('kamar_id') && $request->kamar_id != $mahasiswa->kamar_id) {
                // Cari kamar baru
                $kamarBaru = Kamar::findOrFail($request->kamar_id);
                
                // Cek kapasitas kamar baru
                if ($kamarBaru->terisi >= $kamarBaru->kapasitas) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Kamar baru sudah penuh'
                    ], 400);
                }
                
                // Update kamar lama (kurangi terisi)
                $kamarLama = $mahasiswa->kamar;
                if ($kamarLama) {
                    $kamarLama->terisi = max(0, $kamarLama->terisi - 1);
                    if ($kamarLama->terisi < $kamarLama->kapasitas) {
                        $kamarLama->status = 'tersedia';
                    }
                    $kamarLama->save();
                }
                
                // Update kamar baru (tambah terisi)
                $kamarBaru->terisi = $kamarBaru->terisi + 1;
                if ($kamarBaru->terisi >= $kamarBaru->kapasitas) {
                    $kamarBaru->status = 'terisi';
                }
                $kamarBaru->save();
            }
            
            // Update password jika ada
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
                
                // Update password di user juga
                if ($mahasiswa->user) {
                    $mahasiswa->user->password = Hash::make($request->password);
                    $mahasiswa->user->save();
                }
            }

            $mahasiswa->update($updateData);

            return response()->json([
                'status' => true,
                'message' => 'Data mahasiswa berhasil diupdate',
                'data' => $mahasiswa
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengupdate data mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified mahasiswa.
     */
    public function destroy($nim)
    {
        try {
            $mahasiswa = Mahasiswa::where('nim', $nim)->firstOrFail();
            
            // Update kamar (kurangi terisi)
            $kamar = $mahasiswa->kamar;
            if ($kamar) {
                $kamar->terisi = max(0, $kamar->terisi - 1);
                if ($kamar->terisi < $kamar->kapasitas) {
                    $kamar->status = 'tersedia';
                }
                $kamar->save();
            }
            
            // Hapus user jika perlu
            if ($mahasiswa->user) {
                $mahasiswa->user->delete();
            }
            
            $mahasiswa->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data mahasiswa berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import data mahasiswa from Excel.
     */
    public function import(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx,xls'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Import logic here
            // You'll need to implement Excel import functionality
            // using a package like maatwebsite/excel

            return response()->json([
                'status' => true,
                'message' => 'Data mahasiswa berhasil diimport'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengimport data mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * Get kamar options for dropdown
 */
public function getKamarOptions()
{
    try {
        $kamarOptions = Kamar::where('terisi', '<', DB::raw('kapasitas'))
                            ->where('status', '!=', 'perbaikan')
                            ->select('id', 'gedung', 'no_kamar', 'kapasitas', 'terisi')
                            ->get()
                            ->map(function ($kamar) {
                                return [
                                    'id' => $kamar->id,
                                    'nama' => $kamar->gedung . ' - ' . $kamar->no_kamar,
                                    'kapasitas' => $kamar->kapasitas,
                                    'terisi' => $kamar->terisi
                                ];
                            });

        return response()->json([
            'status' => true,
            'data' => $kamarOptions
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Gagal mengambil data kamar',
            'error' => $e->getMessage()
        ], 500);
    }
}
}