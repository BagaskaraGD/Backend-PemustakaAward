<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerJenisBobot extends Controller
{
    public function readJenisBobot()
    {
        $data = DB::table('jenisbobot_award')->get();
        return response()->json($data, 200);
    }
    public function insJenisBobot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'nama_jenis_bobot' => 'required|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.INS_PUSTAWARD_JENISBOBOT(:pid, :pnama); 
                END;",
                [
                    'pid' => $request->id,
                    'pnama' => $request->nama_jenis_bobot
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Jenis bobot berhasil ditambahkan',
                'data' => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan jenis bobot',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updJenisBobot(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_jenis_bobot' => 'required|string|max:100'
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
            FROM KPTA_22410100003.jenisbobot_award 
            WHERE ID_JENIS_BOBOT = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.UPD_PUSTAWARD_JENISBOBOT(:pid, :pnama); 
                END;",
                [
                    'pid' => $id,
                    'pnama' => $request->nama_jenis_bobot
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Jenis bobot berhasil diperbarui',
                'data' => array_merge(['id_jenis_bobot' => $id], $request->all())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jenis bobot',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delJenisBobot($id)
    {
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
            FROM KPTA_22410100003.jenisbobot_award 
            WHERE ID_JENIS_BOBOT = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            DB::connection('oracle')->statement(
                "BEGIN 
            BOBBY21.DEL_PUSTAWARD_JENISBOBOT(:pid, NULL); 
            END;",
                ['pid' => $id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Jenis bobot berhasil dihapus',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jenis bobot',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
