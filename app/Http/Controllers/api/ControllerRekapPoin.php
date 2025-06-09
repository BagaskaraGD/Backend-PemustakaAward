<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerRekapPoin extends Controller
{
    public function readRekapPoin()
    {
        $data = DB::table(DB::raw("(
        SELECT 
            ra.nim,
            vc.nama,
            vc.status,
            SUM(COALESCE(ra.REKAP_POIN, 0)) total_rekap,
            ROW_NUMBER() OVER (ORDER BY SUM(COALESCE(ra.REKAP_POIN, 0)) DESC) peringkat
        FROM REKAPPOIN_AWARD ra
        JOIN V_CIVITAS vc ON ra.nim = vc.ID_CIVITAS
        GROUP BY ra.nim, vc.nama, vc.status
        HAVING SUM(COALESCE(ra.REKAP_POIN, 0)) > 0
    ) ranking"))
            ->get();
        return response()->json($data);
    }
    public function getRankedMhsBaseBuilder($activePeriodeId)
    {
        return DB::table('REKAPPOIN_AWARD as ra')
            ->join('v_civitas as vc', 'ra.NIM', '=', 'vc.ID_CIVITAS')
            ->select(
                'vc.nama',
                'ra.nim',
                'vc.status',
                'vc.jkel',
                DB::raw('SUM(COALESCE(ra.REKAP_POIN, 0)) as total_rekap_poin'),
                DB::raw('SUM(CASE WHEN ra.ID_KATEGORI = 4 THEN COALESCE(ra.REKAP_JUMLAH, 0) ELSE 0 END) as jumlah_aksara_dinamika'),
                DB::raw('SUM(CASE WHEN ra.ID_KATEGORI = 3 THEN COALESCE(ra.REKAP_JUMLAH, 0) ELSE 0 END) as jumlah_kegiatan'),
                DB::raw('SUM(CASE WHEN ra.ID_KATEGORI = 2 THEN COALESCE(ra.REKAP_JUMLAH, 0) ELSE 0 END) as jumlah_kunjungan'),
                DB::raw('SUM(CASE WHEN ra.ID_KATEGORI = 1 THEN COALESCE(ra.REKAP_JUMLAH, 0) ELSE 0 END) as jumlah_pinjaman')
            )
            ->where('vc.status', 'MHS')
            ->where('ra.ID_PERIODE', $activePeriodeId)
            ->groupBy('ra.nim', 'vc.nama', 'vc.status', 'vc.jkel');
    }

    public function readleaderboardMHS()
    {
        // Langkah 1: Dapatkan ID periode yang sedang aktif
        $activePeriode = DB::table('PERIODE_AWARD')
            ->whereRaw('CURRENT_DATE BETWEEN TGL_MULAI AND TGL_SELESAI')
            ->select('id_periode') // Menggunakan 'id_periode' (lowercase) sesuai perbaikan sebelumnya
            ->first();

        if (!$activePeriode) {
            return response()->json(['message' => 'Tidak ada periode aktif yang ditemukan.'], 404);
        }
        $activePeriodeId = $activePeriode->id_periode;

        // Langkah 2: Ambil data leaderboard menggunakan base builder
        // Karena metode ini ada di dalam kelas yang sama, kita panggil dengan $this
        $queryBuilder = $this->getRankedMhsBaseBuilder($activePeriodeId);

        // Terapkan aturan pengurutan akhir
        $data = $queryBuilder
            ->orderBy('total_rekap_poin', 'DESC')
            ->orderBy('jumlah_aksara_dinamika', 'DESC')
            ->orderBy('jumlah_kegiatan', 'DESC')
            ->orderBy('jumlah_kunjungan', 'DESC')
            ->orderBy('jumlah_pinjaman', 'DESC')
            ->orderBy('nim', 'ASC') // Setelah groupBy, 'ra.nim' akan menjadi 'nim'
            ->get();

        return response()->json($data);
    }
    public function readleaderboardDOSEN()
    {
        $data = DB::table('REKAPPOIN_AWARD as ra')
            ->join('v_civitas as vc', 'ra.NIM', '=', 'vc.ID_CIVITAS')
            ->select(
                'vc.nama',
                'ra.nim',
                'vc.status',
                'vc.jkel',
                DB::raw('SUM(COALESCE(ra.REKAP_POIN, 0)) as total_rekap_poin')
            )
            ->wherein('vc.status', ['TENDIK', 'DOSEN'])
            ->groupBy('ra.nim', 'vc.nama', 'vc.status', 'vc.jkel')
            ->orderByDesc('total_rekap_poin')
            ->get();

        return response()->json($data);
    }
    public function readtopleaderboardMHS()
    {
        $data = DB::table('REKAPPOIN_AWARD as ra')
            ->join('v_civitas as vc', 'ra.NIM', '=', 'vc.ID_CIVITAS')
            ->select('vc.nama', 'ra.nim', 'vc.status', 'ra.nilai')
            ->where('vc.status', 'MHS')
            ->orderByDesc('ra.nilai')
            ->limit(1)
            ->get();

        return response()->json($data);
    }
    public function readtopleaderboardDOSEN()
    {
        $data = DB::table('REKAPPOIN_AWARD as ra')
            ->join('v_civitas as vc', 'ra.NIM', '=', 'vc.ID_CIVITAS')
            ->select('vc.nama', 'ra.nim', 'vc.status', 'ra.nilai')
            ->wherein('vc.status', ['TENDIK', 'DOSEN'])
            ->orderByDesc('ra.nilai')
            ->limit(1)
            ->get();

        return response()->json($data);
    }
    public function insRekapPoin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_rekap_poin' => 'required|numeric',
            'nim'           => 'required|string|max:20',
            'id_periode'    => 'required|numeric',
            'id_kategori'   => 'required|numeric',
            'jns_poin'      => 'required|numeric',
            'nilai'         => 'required|numeric',
            'tgl_nilai'     => 'required|date'
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
                    INS_PUSTAWARD_REKAPPOIN(:pid, :pnim, :pperiode, :pkategori, :pjns, :pnilai, :ptgl); 
                END;",
                [
                    'pid'       => $request->id_rekap_poin,
                    'pnim'      => $request->nim,
                    'pperiode'  => $request->id_periode,
                    'pkategori' => $request->id_kategori,
                    'pjns'      => $request->jns_poin,
                    'pnilai'    => $request->nilai,
                    'ptgl'      => $request->tgl_nilai
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Rekap poin berhasil ditambahkan',
                'data'    => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan rekap poin',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function updRekapPoin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_rekap_poin' => 'required|numeric',
            'nim'           => 'required|string|max:20',
            'id_periode'    => 'required|numeric',
            'id_kategori'   => 'required|numeric',
            'jns_poin'      => 'required|numeric',
            'nilai'         => 'required|numeric',
            'tgl_nilai'     => 'required|date'
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
                UPD_PUSTAWARD_REKAPPOIN(:pid, :pnim, :pperiode, :pkategori, :pjns, :pnilai, :ptgl); 
            END;",
                [
                    'pid'       => $request->id_rekap_poin,
                    'pnim'      => $request->nim,
                    'pperiode'  => $request->id_periode,
                    'pkategori' => $request->id_kategori,
                    'pjns'      => $request->jns_poin,
                    'pnilai'    => $request->nilai,
                    'ptgl'      => $request->tgl_nilai
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Rekap poin berhasil diperbarui',
                'data'    => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui rekap poin',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function delRekapPoin($id)
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
            DB::connection('oracle')->statement(
                "BEGIN 
                DEL_PUSTAWARD_REKAPPOIN(:pid); 
            END;",
                ['pid' => $id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Rekap poin berhasil dihapus',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus rekap poin',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function updateJumAksara($nim, $rekap_jumlah, $rekap_poin)
    {
        DB::table('REKAPPOIN_AWARD')
            ->where('nim', $nim)
            ->where('ID_KATEGORI', 4)
            ->update([
                'rekap_jumlah' => $rekap_jumlah,
                'rekap_poin'   => $rekap_poin
            ]);
        return response()->json([
            'success' => true,
            'message' => 'Jumlah aksara berhasil diperbarui',
            'nim'     => $nim,
            'rekap_jumlah' => $rekap_jumlah,
            'rekap_poin' => $rekap_poin
        ]);
    }
    public function updateJumKegiatan($nim, $rekap_jumlah, $rekap_poin)
    {
        DB::table('REKAPPOIN_AWARD')
            ->where('nim', $nim)
            ->where('ID_KATEGORI', 3)
            ->update([
                'rekap_jumlah' => $rekap_jumlah,
                'rekap_poin'   => $rekap_poin
            ]);
        return response()->json([
            'success' => true,
            'message' => 'Jumlah aksara berhasil diperbarui',
            'nim'     => $nim,
            'rekap_jumlah' => $rekap_jumlah,
            'rekap_poin' => $rekap_poin
        ]);
    }
    public function updateJumKunjungan($nim, $rekap_jumlah, $rekap_poin)
    {
        DB::table('REKAPPOIN_AWARD')
            ->where('nim', $nim)
            ->where('ID_KATEGORI', 2)
            ->update([
                'rekap_jumlah' => $rekap_jumlah,
                'rekap_poin'   => $rekap_poin
            ]);
        return response()->json([
            'success' => true,
            'message' => 'Jumlah kunjungan berhasil diperbarui',
            'nim'     => $nim,
            'rekap_jumlah' => $rekap_jumlah,
            'rekap_poin' => $rekap_poin
        ]);
    }
    public function updateJumPinjaman($nim, $rekap_jumlah, $rekap_poin)
    {
        DB::table('REKAPPOIN_AWARD')
            ->where('nim', $nim)
            ->where('ID_KATEGORI', 1)
            ->update([
                'rekap_jumlah' => $rekap_jumlah,
                'rekap_poin'   => $rekap_poin
            ]);
        return response()->json([
            'success' => true,
            'message' => 'Jumlah pinjaman berhasil diperbarui',
            'nim'     => $nim,
            'rekap_jumlah' => $rekap_jumlah,
            'rekap_poin' => $rekap_poin
        ]);
    }
    public function getjumlahkegiatan($nim)
    {
        $dataKegiatan = DB::table('REKAPPOIN_AWARD')
            ->where('ID_KATEGORI', 3)
            ->where('NIM', $nim)
            ->value('REKAP_JUMLAH');
        return response()->json([
            'success' => true,
            'jumlah_kegiatan' => $dataKegiatan
        ]);
    }
    public function getjumlahaksara($nim)
    {
        $dataAksara = DB::table('REKAPPOIN_AWARD')
            ->where('ID_KATEGORI', 4)
            ->where('NIM', $nim)
            ->value('REKAP_JUMLAH');
        return response()->json([
            'success' => true,
            'jumlah_aksara_dinamika' => $dataAksara
        ]);
    }
    public function getjumlahkunjungan($nim)
    {
        $dataKunjungan = DB::table('REKAPPOIN_AWARD')
            ->where('ID_KATEGORI', 2)
            ->where('NIM', $nim)
            ->value('REKAP_JUMLAH');
        return response()->json([
            'success' => true,
            'jumlah_kunjungan' => $dataKunjungan
        ]);
    }
    public function getjumlahpinjaman($nim)
    {
        $dataPinjaman = DB::table('REKAPPOIN_AWARD')
            ->where('ID_KATEGORI', 1)
            ->where('NIM', $nim)
            ->value('REKAP_JUMLAH');
        return response()->json([
            'success' => true,
            'jumlah_pinjaman' => $dataPinjaman
        ]);
    }
}
