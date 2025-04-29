<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerKegiatan extends Controller
{
    public function readKegiatan()
    {
        $data = DB::table('kegiatan_pust')->get();
        return response()->json($data);
    }
    public function insKegiatan(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id_kegiatan' => 'required|numeric',
            'judul_kegiatan' => 'required|string|max:255',
            'media' => 'required|string|max:100',
            'lokasi' => 'required|string|max:100',
            'keterangan' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Eksekusi stored procedure Oracle
            DB::connection('oracle')->statement(
                "BEGIN 
                    INS_PUSTAWARD_KEGIATAN(
                        :pid, 
                        :pjudul, 
                        :pmedia, 
                        :plokasi, 
                        :pket
                    ); 
                END;",
                [
                    'pid' => $request->id_kegiatan,
                    'pjudul' => $request->judul_kegiatan,
                    'pmedia' => $request->media,
                    'plokasi' => $request->lokasi,
                    'pket' => $request->keterangan
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data kegiatan berhasil ditambahkan',
                'data' => $request->all()
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updKegiatan(Request $request, $id)
    {
        // Validasi input TANPA id_kegiatan karena sekarang diambil dari URL
        $validator = Validator::make($request->all(), [
            'judul_kegiatan' => 'required|string|max:255',
            'media'          => 'required|string|max:100',
            'lokasi'         => 'required|string|max:100',
            'keterangan'     => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {

            $exists = DB::connection('oracle')->selectOne("
                SELECT COUNT(*) AS JUMLAH 
                FROM KPTA_22410100003.kegiatan_pust 
                WHERE ID_KEGIATAN = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            DB::connection('oracle')->statement(
                "BEGIN 
            BOBBY21.UPD_PUSTAWARD_KEGIATAN(
                :pid, 
                :pjudul, 
                :pmedia, 
                :plokasi, 
                :pket
            ); 
        END;",
                [
                    'pid'    => $id,
                    'pjudul' => $request->judul_kegiatan,
                    'pmedia' => $request->media,
                    'plokasi' => $request->lokasi,
                    'pket'   => $request->keterangan
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data kegiatan berhasil diupdate via prosedur Oracle',
                'data'    => array_merge(['id_kegiatan' => $id], $request->all())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal eksekusi prosedur UPD_PUSTAWARD_KEGIATAN',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function delKegiatan(Request $request, $id)
    {
        // Validasi input ID
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $exists = DB::connection('oracle')->selectOne("
                SELECT COUNT(*) AS JUMLAH 
                FROM KPTA_22410100003.kegiatan_pust 
                WHERE ID_KEGIATAN = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi stored procedure DELETE
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.DEL_PUSTAWARD_KEGIATAN(:pid); 
            END;",
                ['pid' => $id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data kegiatan berhasil dihapus menggunakan prosedur Oracle',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur DELETE',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
