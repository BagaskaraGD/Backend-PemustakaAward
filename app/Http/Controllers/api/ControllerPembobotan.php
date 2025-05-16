<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerPembobotan extends Controller
{
    public function readPembobotan()
    {
        $pembobotan = DB::table('pembobotan_award')->get();
        return response()->json($pembobotan);
    }
    public function getNilailevel1()
    {
        $idPeriode = DB::table('periode_award')
            ->select('id_periode')
            ->whereRaw('CURRENT_DATE BETWEEN TGL_MULAI AND TGL_SELESAI')
            ->first();

        if (!$idPeriode) {
            return response()->json(['message' => 'Tidak ada periode aktif'], 404);
        }

        $nilailevel1 = DB::table('PEMBOBOTAN_AWARD')
            ->select('nilai')
            ->where('id_jenis_bobot', 1)
            ->where('id_periode', $idPeriode->id_periode) // <<-- akses property object dengan ->
            ->first();

        return response()->json($nilailevel1);
    }
    public function getNilailevel2()
    {
        $idPeriode = DB::table('periode_award')
            ->select('id_periode')
            ->whereRaw('CURRENT_DATE BETWEEN TGL_MULAI AND TGL_SELESAI')
            ->first();

        if (!$idPeriode) {
            return response()->json(['message' => 'Tidak ada periode aktif'], 404);
        }

        $nilailevel2 = DB::table('PEMBOBOTAN_AWARD')
            ->select('nilai')
            ->where('id_jenis_bobot', 2)
            ->where('id_periode', $idPeriode->id_periode) // <<-- akses property object dengan ->
            ->first();

        return response()->json($nilailevel2);
    }
    public function getNilailevel3()
    {
        $idPeriode = DB::table('periode_award')
            ->select('id_periode')
            ->whereRaw('CURRENT_DATE BETWEEN TGL_MULAI AND TGL_SELESAI')
            ->first();

        if (!$idPeriode) {
            return response()->json(['message' => 'Tidak ada periode aktif'], 404);
        }

        $nilailevel3 = DB::table('PEMBOBOTAN_AWARD')
            ->select('nilai')
            ->where('id_jenis_bobot', 3)
            ->where('id_periode', $idPeriode->id_periode) // <<-- akses property object dengan ->
            ->first();

        return response()->json($nilailevel3);
    }
    public function insPembobotan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'id_jenis_bobot' => 'required|numeric',
            'id_periode' => 'required|numeric',
            'nilai' => 'required|numeric'
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
                BOBBY21.INS_PUSTAWARD_PEMBOBOTAN(
                    :pid, 
                    :pjenis, 
                    :pperiode, 
                    :pnilai
                ); 
            END;",
                [
                    'pid' => $request->id,
                    'pjenis' => $request->id_jenis_bobot,
                    'pperiode' => $request->id_periode,
                    'pnilai' => $request->nilai
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Pembobotan berhasil ditambahkan',
                'data' => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pembobotan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updPembobotan(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id_jenis_bobot' => 'required|numeric',
            'id_periode' => 'required|numeric',
            'nilai' => 'required|numeric'
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
                FROM KPTA_22410100003.pembobotan_award 
                WHERE ID_PEMBOBOTAN = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            DB::connection('oracle')->statement(
                "BEGIN 
            BOBBY21.UPD_PUSTAWARD_PEMBOBOTAN(
                :pid, 
                :pjenis, 
                :pperiode, 
                :pnilai
            ); 
        END;",
                [
                    'pid' => $id,
                    'pjenis' => $request->id_jenis_bobot,
                    'pperiode' => $request->id_periode,
                    'pnilai' => $request->nilai
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Pembobotan berhasil diperbarui',
                'data' => array_merge(['id_pembobotan' => $id], $request->all())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pembobotan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delPembobotan(Request $request, $id)
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
                FROM KPTA_22410100003.pembobotan_award 
                WHERE ID_PEMBOBOTAN = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            DB::connection('oracle')->statement(
                "BEGIN 
            BOBBY21.DEL_PUSTAWARD_PEMBOBOTAN(:pid); 
        END;",
                ['pid' => $id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Pembobotan berhasil dihapus',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pembobotan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getpoinaksaradinamika()
    {
        $nilai = DB::table('PEMBOBOTAN_AWARD')
            ->where('ID_JENIS_BOBOT', '8')
            ->pluck('nilai');
        return response()->json([
            'success' => true,
            'data' => $nilai
        ]);
    }
}
