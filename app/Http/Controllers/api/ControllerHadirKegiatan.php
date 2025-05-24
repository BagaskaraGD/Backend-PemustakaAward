<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerHadirKegiatan extends Controller
{
    public function readHadirKegiatan()
    {
        // Ambil data dari tabel hadirkegiatan
        $data = DB::table('hadirkegiatan_pust')->get();
        return response()->json($data);
    }
    public function insHadirKegiatan(Request $request)
    {
        // Validasi input request
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'id_jadwal' => 'required|numeric',
            'nim'       => 'required|string|max:50',
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
                BOBBY21.INS_PUSTAWARD_HADIRKEG(
                    :pid, 
                    :pjadwal, 
                    :pnim
                ); 
            END;",
                [
                    'pid'     => $request->id, // Ambil dari parameter body
                    'pjadwal' => $request->id_jadwal,
                    'pnim'    => $request->nim
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data hadir kegiatan berhasil ditambahkan',
                'data'    => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur INS_PUSTAWARD_HADIRKEG',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function updHadirKegiatan(Request $request, $id)
    {
        // Validasi input request
        $validator = Validator::make($request->all(), [
            'id_jadwal' => 'required|numeric',
            'nim'       => 'required|string|max:50',
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
            FROM KPTA_22410100003.hadirkegiatan_pust 
            WHERE ID_HADIR = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi prosedur update dengan named binding
            DB::connection('oracle')->statement(
                "BEGIN 
            BOBBY21.UPD_PUSTAWARD_HADIRKEG(
                :pid, 
                :pjadwal, 
                :pnim
            ); 
        END;",
                [
                    'pid'     => $id, // Ambil ID dari parameter route
                    'pjadwal' => $request->id_jadwal,
                    'pnim'    => $request->nim
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data hadir kegiatan berhasil diperbarui',
                'data'    => array_merge(['id' => $id], $request->all())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur UPD_PUSTAWARD_HADIRKEG',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function delHadirKegiatan(Request $request, $id)
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
            FROM KPTA_22410100003.hadirkegiatan_pust 
            WHERE ID_HADIR = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi prosedur delete dengan named binding
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.DEL_PUSTAWARD_HADIRKEG(:pid); 
            END;",
                [
                    'pid' => $id // Ambil ID dari parameter route
                ]
            );

            return response()->json([
                'success'    => true,
                'message'    => 'Data hadir kegiatan berhasil dihapus',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur DEL_PUSTAWARD_HADIRKEG',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function getkehadiran($nim)
    {
        $data = DB::connection('oracle')
            ->table('KEGIATAN_PUST as kp')
            ->join('JADWAL_KEGIATAN_PUST as jkp', 'kp.ID_KEGIATAN', '=', 'jkp.ID_KEGIATAN')
            ->join('PEMATERIKEGIATAN_PUST as pp', 'jkp.ID_PEMATERI', '=', 'pp.ID_PEMATERI')
            ->join('HADIRKEGIATAN_PUST as hp', 'jkp.ID_JADWAL', '=', 'hp.ID_JADWAL')
            ->select(
                'kp.ID_KEGIATAN',
                'kp.JUDUL_KEGIATAN',
                'jkp.TGL_KEGIATAN',
                DB::raw("REPLACE(TO_CHAR(jkp.WAKTU_MULAI, 'HH24:MI'), ':', '.') || ' - ' || REPLACE(TO_CHAR(jkp.WAKTU_SELESAI, 'HH24:MI'), ':', '.') AS JAM_KEGIATAN"),
                'pp.NAMA_PEMATERI',
                'kp.LOKASI',
                'jkp.BOBOT'
            )
            ->where('hp.nim', '=', $nim)
            ->get();
        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }
    public function readMyKegiatan($nim)
    {
        $data = DB::table('kegiatan_pust')
            ->join('hadirkegiatan_pust', 'kegiatan_pust.id_kegiatan', '=', 'hadirkegiatan_pust.id_kegiatan')
            ->where('hadirkegiatan_pust.nim', $nim)
            ->select('kegiatan_pust.*')
            ->get();

        return response()->json($data);
    }
    public function checkKegiatan(Request $request)
    {
        $civitas = $request->get('nim');
        $idjadwal = $request->get('id_jadwal');

        $sudahAbsen = DB::connection('oracle')
            ->table('HADIRKEGIATAN_PUST')
            ->where('ID_JADWAL', $idjadwal)
            ->where('NIM', $civitas)
            ->exists();

        return response()->json(['exists' =>  $sudahAbsen]);
    }
    public function getLastIdHadir()
    {
        $lastId = DB::connection('oracle')
            ->table('HADIRKEGIATAN_PUST')
            ->max('ID_HADIR');

        return response()->json([
            'last_id' => $lastId
        ]);
    }
}
