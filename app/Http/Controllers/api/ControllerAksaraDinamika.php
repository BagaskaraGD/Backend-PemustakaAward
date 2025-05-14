<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ControllerAksaraDinamika extends Controller
{
    public function readAksaraDinamika()
    {
        // $data = DB::table('aksara_dinamika')->get();
        // return response()->json($data);
        $data = DB::table('aksara_dinamika as ad')
            ->join('v_buku_pust as vbp', 'ad.induk_buku', '=', 'vbp.induk')
            ->join('v_civitas as vc', 'ad.nim', '=', 'vc.id_civitas')
            ->select('*')
        ->get();          
        return response()->json($data);
    }

    public function insAksaraDinamika(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'nim' => 'required|string|max:50',
            'id_buku' => 'required|string|max:50',
            'induk_buku' => 'required|string|max:50',
            'review' => 'required|string',
            'dosen_usulan' => 'required|string|max:100',
            'link_upload' => 'required|url|max:255'
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
                    BOBBY21.INS_PUSTAWARD_AKSARA_DINAMIKA(
                        :pid, 
                        :pnim, 
                        :pbuku, 
                        :pinduk, 
                        :preview, 
                        :pdosen, 
                        :plink
                    ); 
                END;",
                [
                    'pid' => $request->id,
                    'pnim' => $request->nim,
                    'pbuku' => $request->id_buku,
                    'pinduk' => $request->induk_buku,
                    'preview' => $request->review,
                    'pdosen' => $request->dosen_usulan,
                    'plink' => $request->link_upload
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil ditambahkan menggunakan prosedur Oracle',
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
    public function updAksaraDinamika(Request $request, $id)
    {
        // Validasi input request (tanpa id di body)
        $validator = Validator::make($request->all(), [
            'nim'           => 'required|string|max:50',
            'id_buku'       => 'required|string|max:50',
            'induk_buku'    => 'required|string|max:50',
            'review'        => 'required|string',
            'dosen_usulan'  => 'required|string|max:100',
            'link_upload'   => 'required|url|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Cek apakah data dengan ID tersebut ada
            $exists = DB::connection('oracle')->selectOne("
                SELECT COUNT(*) AS JUMLAH 
                FROM KPTA_22410100003.AKSARA_DINAMIKA 
                WHERE ID_AKSARA_DINAMIKA = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }

            // Eksekusi prosedur update dengan named binding
            DB::connection('oracle')->statement(
                "BEGIN 
                    BOBBY21.UPD_PUSTAWARD_AKSARA_DINAMIKA(
                        :pid, 
                        :pnim, 
                        :pbuku, 
                        :pinduk, 
                        :preview, 
                        :pdosen, 
                        :plink
                    ); 
                END;",
                [
                    'pid'     => $id, // dari parameter route
                    'pnim'    => $request->nim,
                    'pbuku'   => $request->id_buku,
                    'pinduk'  => $request->induk_buku,
                    'preview' => $request->review,
                    'pdosen'  => $request->dosen_usulan,
                    'plink'   => $request->link_upload
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diupdate via prosedur Oracle',
                'data'    => array_merge(['id' => $id], $request->all())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal eksekusi prosedur UPD_PUSTAWARD_AKSARA_DINAMIKA',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function delAksaraDinamika(Request $request, $id)
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
                FROM KPTA_22410100003.AKSARA_DINAMIKA 
                WHERE ID_AKSARA_DINAMIKA = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi stored procedure DELETE
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.DEL_PUSTAWARD_AKSARA_DINAMIKA(:pid); 
            END;",
                ['pid' => $id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus menggunakan prosedur Oracle',
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
