<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerPeriode extends Controller
{
    public function readPeriode()
    {
        // Ambil data periode dari database
        $data = DB::table('periode_award')->get();

        return response()->json($data);
    }

    public function insPeriode(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id'         => 'required|numeric',
            'nama'       => 'required|string|max:100',
            'tgl_mulai'  => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Eksekusi prosedur insert
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.INS_PUSTAWARD_PERIODE(
                    :pid, 
                    :pnama, 
                    :pmulai, 
                    :pselesai
                ); 
            END;",
                [
                    'pid'      => $request->id,
                    'pnama'    => $request->nama,
                    'pmulai'   => $request->tgl_mulai,
                    'pselesai' => $request->tgl_selesai
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data periode berhasil ditambahkan menggunakan prosedur Oracle',
                'data'    => $request->all()
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur INS_PUSTAWARD_PERIODE',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function updPeriode(Request $request, $id)
    {
        // Validasi input request (tanpa id di body)
        $validator = Validator::make($request->all(), [
            'nama'        => 'required|string|max:100',
            'tgl_mulai'   => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai'
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
            FROM KPTA_22410100003.periode_award 
            WHERE ID_PERIODE = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi prosedur update dengan named binding
            DB::connection('oracle')->statement(
                "BEGIN 
            BOBBY21.UPD_PUSTAWARD_PERIODE(
                :pid, 
                :pnama, 
                :pmulai, 
                :pselesai
            ); 
        END;",
                [
                    'pid'      => $id, // ← ambil dari parameter route
                    'pnama'    => $request->nama,
                    'pmulai'   => $request->tgl_mulai,
                    'pselesai' => $request->tgl_selesai
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data periode berhasil diupdate via prosedur Oracle',
                'data'    => array_merge(['id' => $id], $request->all())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal eksekusi prosedur UPD_PUSTAWARD_PERIODE',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function delPeriode(Request $request, $id)
    {
        // Validasi input ID
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
            FROM KPTA_22410100003.periode_award 
            WHERE ID_PERIODE = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi prosedur DELETE
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.DEL_PUSTAWARD_PERIODE(:pid); 
            END;",
                ['pid' => $id]
            );

            return response()->json([
                'success'     => true,
                'message'     => 'Data periode berhasil dihapus menggunakan prosedur Oracle',
                'deleted_id'  => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur DEL_PUSTAWARD_PERIODE',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
