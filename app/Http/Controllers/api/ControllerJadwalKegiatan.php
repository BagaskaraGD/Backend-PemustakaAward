<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerJadwalKegiatan extends Controller
{
    public function readJadwalKegiatan()
    {
        $data = DB::table('jadwal_kegiatan_pust')->get();
        return response()->json($data);
    }
    public function insJadwalKegiatan(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'id_pemateri' => 'required|numeric',
            'id_kegiatan' => 'required|numeric',
            'tgl_kegiatan' => 'required|date',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date',
            'bobot' => 'required|numeric',
            'keterangan' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Eksekusi stored procedure
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.INS_PUSTAWARD_JADWALKEG(
                    :pid, 
                    :ppemateri, 
                    :pkegiatan, 
                    :ptgl, 
                    :pmulai, 
                    :pselesai, 
                    :pbobot, 
                    :pketerangan
                ); 
            END;",
                [
                    'pid' => $request->id,
                    'ppemateri' => $request->id_pemateri,
                    'pkegiatan' => $request->id_kegiatan,
                    'ptgl' => $request->tgl_kegiatan,
                    'pmulai' => $request->waktu_mulai,
                    'pselesai' => $request->waktu_selesai,
                    'pbobot' => $request->bobot,
                    'pketerangan' => $request->keterangan
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data jadwal kegiatan berhasil ditambahkan menggunakan prosedur Oracle',
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
    public function updJadwalKegiatan(Request $request, $id)
    {
        // Validasi input, tanpa 'id' di body
        $validator = Validator::make($request->all(), [
            'id_pemateri'    => 'required|numeric',
            'id_kegiatan'    => 'required|numeric',
            'tgl_kegiatan'   => 'required|date',
            'waktu_mulai'    => 'required|date',
            'waktu_selesai'  => 'required|date',
            'bobot'          => 'required|numeric',
            'keterangan'     => 'nullable|string|max:255'
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
            FROM KPTA_22410100003.jadwal_kegiatan_pust 
            WHERE ID_JADWAL = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi prosedur update
            DB::connection('oracle')->statement(
                "BEGIN 
            BOBBY21.UPD_PUSTAWARD_JADWALKEG(
                :pid,
                :ppemateri,
                :pkegiatan,
                :ptgl,
                :pmulai,
                :pselesai,
                :pbobot,
                :pketerangan
            );
        END;",
                [
                    'pid'         => $id,
                    'ppemateri'   => $request->id_pemateri,
                    'pkegiatan'   => $request->id_kegiatan,
                    'ptgl'        => $request->tgl_kegiatan,
                    'pmulai'      => $request->waktu_mulai,
                    'pselesai'    => $request->waktu_selesai,
                    'pbobot'      => $request->bobot,
                    'pketerangan' => $request->keterangan
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data jadwal kegiatan berhasil diperbarui via prosedur Oracle',
                'data'    => array_merge(['id' => $id], $request->all())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur UPD_PUSTAWARD_JADWALKEG',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function delJadwalKegiatan(Request $request, $id)
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
            FROM KPTA_22410100003.jadwal_kegiatan_pust 
            WHERE ID_JADWAL = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi prosedur DELETE
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.DEL_PUSTAWARD_JADWALKEG(:pid); 
            END;",
                ['pid' => $id]
            );

            return response()->json([
                'success'    => true,
                'message'    => 'Data jadwal kegiatan berhasil dihapus via prosedur Oracle',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur DEL_PUSTAWARD_JADWALKEG',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
