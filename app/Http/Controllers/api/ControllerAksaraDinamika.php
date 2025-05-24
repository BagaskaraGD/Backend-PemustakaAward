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
        $data = DB::select("
        SELECT
            ID_AKSARA_DINAMIKA,
            INDUK_BUKU,
            JUDUL,
            TGL_REVIEW,
            NAMA_PERIODE,
            STATUS
        FROM (
            WITH StatusTerbaru AS (
                SELECT
                    ID_AKSARA_DINAMIKA,
                    STATUS,
                    ROW_NUMBER() OVER (
                        PARTITION BY ID_AKSARA_DINAMIKA
                        ORDER BY TGL_STATUS DESC
                    ) AS rn
                FROM HISTORI_STATUS
            ),
            DataUnikPerInduk AS (
                SELECT
                    ad.ID_AKSARA_DINAMIKA,
                    ad.INDUK_BUKU,
                    vbp.JUDUL,
                    ad.TGL_REVIEW,
                    pa.NAMA_PERIODE,
                    COALESCE(st.STATUS, 'menunggu') AS STATUS,
                    ROW_NUMBER() OVER (
                        PARTITION BY ad.INDUK_BUKU
                        ORDER BY ad.TGL_REVIEW DESC
                    ) AS rn
                FROM AKSARA_DINAMIKA ad
                JOIN PERIODE_AWARD pa 
                    ON ad.TGL_REVIEW BETWEEN pa.TGL_MULAI AND pa.TGL_SELESAI
                    AND ad.REVIEW IS NOT NULL
                JOIN (
                    SELECT DISTINCT INDUK, JUDUL FROM V_BUKU_PUST
                ) vbp ON ad.INDUK_BUKU = vbp.INDUK
                LEFT JOIN StatusTerbaru st 
                    ON ad.ID_AKSARA_DINAMIKA = st.ID_AKSARA_DINAMIKA
                    AND st.rn = 1
                WHERE ad.NIM = ?
            )
            SELECT * FROM DataUnikPerInduk
            WHERE rn = 1
        ) ORDER BY ID_AKSARA_DINAMIKA
    ", [$nim]);

        return response()->json([
            'success' => true,
            'data'    => $data
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
    public function getAksaraDinamikaForEdit($id, $induk_buku, $nim)
    {
        $result = DB::table('AKSARA_DINAMIKA as ad')
            ->select(
                'ad.ID_AKSARA_DINAMIKA',
                'ad.NIM',
                'ad.ID_BUKU',
                'ad.INDUK_BUKU',
                'ad.REVIEW',
                'ad.DOSEN_USULAN',
                'ad.LINK_UPLOAD',
                'ad.TGL_REVIEW',
                'vbp.JUDUL',
                'vbp.TH_TERBIT',
                // Kolom PENGARANG_ALL menggunakan DB::raw() untuk ekspresi SQL kompleks
                DB::raw("TRIM(
                    NVL(vbp.PENGARANG1, '') ||
                    CASE WHEN vbp.PENGARANG1 IS NOT NULL AND (vbp.PENGARANG2 IS NOT NULL OR vbp.PENGARANG3 IS NOT NULL) THEN ', ' ELSE '' END ||
                    NVL(vbp.PENGARANG2, '') ||
                    CASE WHEN vbp.PENGARANG2 IS NOT NULL AND vbp.PENGARANG3 IS NOT NULL THEN ', ' ELSE '' END ||
                    NVL(vbp.PENGARANG3, '')
                ) AS PENGARANG_ALL")
            )
            ->leftJoin('HISTORI_STATUS as hs', 'ad.ID_AKSARA_DINAMIKA', '=', 'hs.ID_AKSARA_DINAMIKA')
            ->leftJoin('V_BUKU_PUST as vbp', 'vbp.INDUK', '=', 'ad.INDUK_BUKU')
            ->where('ad.NIM', $nim)
            ->where('ad.INDUK_BUKU', $induk_buku)
            ->where('ad.ID_AKSARA_DINAMIKA', $id)
            ->get();
        
        return response()->json([
            'success' => true,
            'data'    => $result
        ]);
    }
}
