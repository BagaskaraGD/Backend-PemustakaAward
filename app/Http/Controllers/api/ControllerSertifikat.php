<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerSertifikat extends Controller
{
    public function readSertifikat()
    {
        $data = DB::table('sertifikat_pust')->get();
        return response()->json($data);
    }
    public function insSertifikat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'nim'           => 'required|string|max:20',
            'id_kegiatan'   => 'required|numeric',
            'nama_file'     => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::connection('oracle')->statement(
                "BEGIN 
                    BOBBY21.INS_PUSTAWARD_SERTIFIKAT(
                    :pid, 
                    :pnim, 
                    :pkegiatan, 
                    :pfile
                ); 
                END;",
                [
                    'pid'       => $request->id,
                    'pnim'      => $request->nim,
                    'pkegiatan' => $request->id_kegiatan,
                    'pfile'     => $request->nama_file
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Sertifikat berhasil ditambahkan',
                'data'    => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan sertifikat',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function updSertifikat(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nim'           => 'required|string|max:20',
            'id_kegiatan'   => 'required|numeric',
            'nama_file'     => 'required|string|max:255'
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
            FROM KPTA_22410100003.sertifikat_pust 
            WHERE ID_sertifikat = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            DB::connection('oracle')->statement(
                "BEGIN 
                    BOBBY21.UPD_PUSTAWARD_SERTIFIKAT(:pid, :pnim, :pkegiatan, :pfile); 
                END;",
                [
                    'pid'       => $id,
                    'pnim'      => $request->nim,
                    'pkegiatan' => $request->id_kegiatan,
                    'pfile'     => $request->nama_file
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Sertifikat berhasil diperbarui',
                'data'    => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui sertifikat',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function delSertifikat($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric'
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
            FROM KPTA_22410100003.sertifikat_pust 
            WHERE ID_sertifikat = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.DEL_PUSTAWARD_SERTIFIKAT(:pid); 
            END;",
                ['pid' => $id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Sertifikat berhasil dihapus',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus sertifikat',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
