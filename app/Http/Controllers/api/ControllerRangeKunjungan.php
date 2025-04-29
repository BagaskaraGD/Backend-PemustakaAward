<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerRangeKunjungan extends Controller
{
    public function readRangeKunjungan()
    {
        $data = DB::table('rangekunjungan_award')->get();
        return response()->json($data, 200);
    }
    public function insRangeKunjungan(Request $request)
    {
        // Validasi input request
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'id_jenis_range' => 'required|numeric',
            'id_periode'     => 'required|numeric',
            'range_awal'     => 'required|numeric',
            'range_akhir'    => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Eksekusi prosedur insert dengan named binding
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.INS_PUSTAWARD_RANGEKUNJ(
                    :pid, 
                    :pjenis, 
                    :pperiode, 
                    :pawal, 
                    :pakhir
                ); 
            END;",
                [
                    'pid'      => $request->id, // Ambil dari parameter body
                    'pjenis'   => $request->id_jenis_range,
                    'pperiode' => $request->id_periode,
                    'pawal'    => $request->range_awal,
                    'pakhir'   => $request->range_akhir
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data range kunjungan berhasil ditambahkan',
                'data'    => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur INS_PUSTAWARD_RANGEKUNJ',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function updRangeKunjungan(Request $request, $id)
    {
        // Validasi input request
        $validator = Validator::make($request->all(), [
            'id_jenis_range' => 'required|numeric',
            'id_periode'     => 'required|numeric',
            'range_awal'     => 'required|numeric',
            'range_akhir'    => 'required|numeric',
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
                FROM KPTA_22410100003.rangekunjungan_award 
                WHERE ID_RANGE_KUNJUNGAN = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi prosedur update dengan named binding
            DB::connection('oracle')->statement(
                "BEGIN 
            BOBBY21.UPD_PUSTAWARD_RANGEKUNJ(
                :pid, 
                :pjenis, 
                :pperiode, 
                :pawal, 
                :pakhir
            ); 
        END;",
                [
                    'pid'      => $id, // Ambil ID dari parameter route
                    'pjenis'   => $request->id_jenis_range,
                    'pperiode' => $request->id_periode,
                    'pawal'    => $request->range_awal,
                    'pakhir'   => $request->range_akhir
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data range kunjungan berhasil diperbarui',
                'data'    => array_merge(['id' => $id], $request->all())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur UPD_PUSTAWARD_RANGEKUNJ',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function delRangeKunjungan(Request $request, $id)
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
            FROM KPTA_22410100003.rangekunjungan_award 
            WHERE ID_RANGE_KUNJUNGAN = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi prosedur delete dengan named binding
            DB::connection('oracle')->statement(
                "BEGIN 
            BOBBY21.DEL_PUSTAWARD_RANGEKUNJ(:pid); 
        END;",
                [
                    'pid' => $id // Ambil ID dari parameter route
                ]
            );

            return response()->json([
                'success'    => true,
                'message'    => 'Data range kunjungan berhasil dihapus',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur DEL_PUSTAWARD_RANGEKUNJ',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
