<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ControllerKategoriNilai extends Controller
{
    public function readKategoriNilai()
    {
        $data = DB::table('kategori_penilaian_award')->get();
        return response()->json($data);
    }
    public function insKategoriNilai(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'     => 'required|numeric',
            'jenis'  => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::connection('oracle')->statement("
                BEGIN
                    INS_PUSTAWARD_KATNILAI(:pid, :pjenis);
                END;", [
                'pid'    => $request->id,
                'pjenis' => $request->jenis,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data kategori penilaian berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data kategori penilaian',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updKategoriNilai(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jenis' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::connection('oracle')->statement("
                BEGIN
                    UPD_PUSTAWARD_KATNILAI(:pid, :pjenis);
                END;", [
                'pid'    => $id,
                'pjenis' => $request->jenis,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data kategori penilaian berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data kategori penilaian',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function delKategoriNilai($id)
    {
        try {
            DB::connection('oracle')->statement("
                BEGIN
                    DEL_PUSTAWARD_KATNILAI(:pid, NULL);
                END;", [
                'pid' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data kategori penilaian berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data kategori penilaian',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
