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
            'dosen_usulan',
            'link_upload',
            'tgl_review' => 'required|date',
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
                        :plink,
                        :ptgl
                    ); 
                END;",
                [
                    'pid' => $request->id,
                    'pnim' => $request->nim,
                    'pbuku' => $request->id_buku,
                    'pinduk' => $request->induk_buku,
                    'preview' => $request->review,
                    'pdosen' => $request->dosen_usulan,
                    'plink' => $request->link_upload,
                    'ptgl' => $request->tgl_review
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
    public function getuserakasradinamika($nim)
    {
        $data = DB::table(DB::raw('
        (
            SELECT * FROM (
                SELECT hs.*, 
                       ROW_NUMBER() OVER (PARTITION BY ID_AKSARA_DINAMIKA ORDER BY ID_HISTORI_STATUS DESC) AS rn
                FROM HISTORI_STATUS hs
            ) sub
            WHERE rn = 1
        ) STATUS_TERBARU
    '))
            ->rightJoin(DB::raw('
        (
            SELECT * FROM (
                SELECT ad.*, 
                       ROW_NUMBER() OVER (PARTITION BY ID_AKSARA_DINAMIKA ORDER BY TGL_REVIEW DESC) AS rn
                FROM AKSARA_DINAMIKA ad
                WHERE REVIEW IS NOT NULL
            ) sub
            WHERE rn = 1
        ) REVIEW_TERBARU
    '), 'REVIEW_TERBARU.ID_AKSARA_DINAMIKA', '=', 'STATUS_TERBARU.ID_AKSARA_DINAMIKA')
            ->join('PERIODE_AWARD AS pa', function ($join) {
                $join->whereRaw('REVIEW_TERBARU.TGL_REVIEW BETWEEN pa.TGL_MULAI AND pa.TGL_SELESAI');
            })
            ->join('V_BUKU_PUST AS vbp', 'REVIEW_TERBARU.INDUK_BUKU', '=', 'vbp.INDUK')
            ->where('REVIEW_TERBARU.NIM', $nim)
            ->select([
                'REVIEW_TERBARU.ID_AKSARA_DINAMIKA',
                'vbp.JUDUL',
                'REVIEW_TERBARU.TGL_REVIEW',
                'pa.NAMA_PERIODE',
                DB::raw("CASE WHEN STATUS_TERBARU.STATUS IS NULL THEN 'menunggu' ELSE STATUS_TERBARU.STATUS END AS STATUS")
            ])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    public function checkReview(Request $request)
    {
        $nim = $request->get('nim');
        $induk = $request->get('induk_buku');

        $exists = DB::connection('oracle')
            ->table('AKSARA_DINAMIKA')
            ->where('nim', $nim)
            ->where('induk_buku', $induk)
            ->exists();

        return response()->json(['exists' => $exists]);
    }
    public function getLastId()
    {
        $lastId = DB::connection('oracle')
            ->table('AKSARA_DINAMIKA')
            ->max('ID_AKSARA_DINAMIKA');

        return response()->json([
            'last_id' => $lastId
        ]);
    }
    public function getLastIdBuku()
    {
        $lastIdb = DB::connection('oracle')
            ->table('AKSARA_DINAMIKA')
            ->max('ID_BUKU');

        return response()->json([
            'last_idb' => $lastIdb
        ]);
    }
}
