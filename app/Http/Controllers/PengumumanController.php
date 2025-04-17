<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PengumumanController extends Controller
{
    /**
     * Display a listing of pengumuman.
     */
    public function index(Request $request)
    {
        try {
            $query = Pengumuman::with('creator');

            // Filter berdasarkan tanggal
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('tanggal_pengumuman', [$request->start_date, $request->end_date]);
            }

            // Search berdasarkan judul
            if ($request->has('search')) {
                $query->where('judul_pengumuman', 'like', '%' . $request->search . '%');
            }

            $pengumuman = $query->latest()->get();

            return response()->json([
                'status' => true,
                'message' => 'Data pengumuman berhasil diambil',
                'data' => $pengumuman
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data pengumuman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created pengumuman.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'judul_pengumuman' => 'required|string|max:255',
                'isi_pengumuman' => 'required|string',
                'tanggal_pengumuman' => 'required|date',
                'file_pengumuman' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120' // max 5MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle file upload if exists
            $file_path = null;
            if ($request->hasFile('file_pengumuman')) {
                $file = $request->file('file_pengumuman');
                $file_path = $file->store('pengumuman', 'public');
            }

            $pengumuman = Pengumuman::create([
                'judul_pengumuman' => $request->judul_pengumuman,
                'isi_pengumuman' => $request->isi_pengumuman,
                'tanggal_pengumuman' => $request->tanggal_pengumuman,
                'file_pengumuman' => $file_path,
                'created_by' => Auth::id()
            ]);

            $pengumuman->load('creator');

            return response()->json([
                'status' => true,
                'message' => 'Pengumuman berhasil ditambahkan',
                'data' => $pengumuman
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menambahkan pengumuman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified pengumuman.
     */
    public function show($id)
    {
        try {
            $pengumuman = Pengumuman::with('creator')->findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $pengumuman
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Pengumuman tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified pengumuman.
     */
    public function update(Request $request, $id)
    {
        try {
            $pengumuman = Pengumuman::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'judul_pengumuman' => 'required|string|max:255',
                'isi_pengumuman' => 'required|string',
                'tanggal_pengumuman' => 'required|date',
                'file_pengumuman' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120' // max 5MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle file upload if new file exists
            if ($request->hasFile('file_pengumuman')) {
                // Delete old file if exists
                if ($pengumuman->file_pengumuman) {
                    Storage::disk('public')->delete($pengumuman->file_pengumuman);
                }
                
                $file = $request->file('file_pengumuman');
                $file_path = $file->store('pengumuman', 'public');
                $pengumuman->file_pengumuman = $file_path;
            }

            $pengumuman->update([
                'judul_pengumuman' => $request->judul_pengumuman,
                'isi_pengumuman' => $request->isi_pengumuman,
                'tanggal_pengumuman' => $request->tanggal_pengumuman
            ]);

            $pengumuman->load('creator');

            return response()->json([
                'status' => true,
                'message' => 'Pengumuman berhasil diupdate',
                'data' => $pengumuman
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengupdate pengumuman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified pengumuman.
     */
    public function destroy($id)
    {
        try {
            $pengumuman = Pengumuman::findOrFail($id);
            
            // Delete file if exists
            if ($pengumuman->file_pengumuman) {
                Storage::disk('public')->delete($pengumuman->file_pengumuman);
            }
            
            $pengumuman->delete();

            return response()->json([
                'status' => true,
                'message' => 'Pengumuman berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus pengumuman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download file pengumuman.
     */
    public function downloadFile($id)
    {
        try {
            $pengumuman = Pengumuman::findOrFail($id);

            if (!$pengumuman->file_pengumuman) {
                return response()->json([
                    'status' => false,
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            return Storage::disk('public')->download($pengumuman->file_pengumuman);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengunduh file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function serveFile($filename)
{
    try {
        // Log untuk debugging
        \Log::info('Serving file: ' . $filename);
        
        // Path file di storage
        $filePath = 'public/pengumuman/' . $filename;
        
        // Cek keberadaan file
        if (!Storage::exists($filePath)) {
            \Log::warning('File not found: ' . $filePath);
            return response()->json([
                'status' => 'error',
                'message' => 'File tidak ditemukan'
            ], 404);
        }
        
        // Get file content dan mime type
        $fullPath = Storage::path($filePath);
        $mimeType = Storage::mimeType($filePath);
        
        // Extract original name (remove timestamp if present)
        $originalName = $filename;
        if (strpos($filename, '_') !== false) {
            $parts = explode('_', $filename, 2);
            if (is_numeric($parts[0])) {
                $originalName = $parts[1];
            }
        }
        
        // Return file as download with CORS headers
        $response = response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $originalName . '"',
        ]);
        
        // Tambahkan header CORS secara manual jika diperlukan
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        
        return $response;
        
    } catch (\Exception $e) {
        \Log::error('Error serving file: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat mengakses file: ' . $e->getMessage()
        ], 500);
    }
}
}