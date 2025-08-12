<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerPeriode extends Controller
{
    public function getStatusTerkini()
    {
        $currentDate = Carbon::now();

        // 1. Cek apakah ada periode yang sedang aktif
        $periodeAktif = DB::table('PERIODE_AWARD')
            ->whereDate('TGL_MULAI', '<=', $currentDate)
            ->whereDate('TGL_SELESAI', '>=', $currentDate)
            ->orderBy('ID_PERIODE', 'desc')
            ->first();

        if ($periodeAktif) {
            return response()->json([
                'success' => true,
                'status' => 'aktif',
                'periode' => $periodeAktif
            ]);
        }

        // 2. Jika tidak ada yang aktif, cari periode terakhir yang sudah selesai
        $periodeTerakhirSelesai = DB::table('PERIODE_AWARD')
            ->whereDate('TGL_SELESAI', '<', $currentDate)
            ->orderBy('TGL_SELESAI', 'desc')
            ->first();

        if ($periodeTerakhirSelesai) {
            $id_periode = $periodeTerakhirSelesai->id_periode;

            // A. Cari Pemenang Mahasiswa
            $pemenangMahasiswa = DB::table('REKAPPOIN_AWARD as ra')
                ->join('V_CIVITAS as vc', 'ra.NIM', '=', 'vc.ID_CIVITAS')
                ->select(
                    'ra.NIM as NOMOR_INDUK',
                    'vc.NAMA',
                    'vc.JKEL',
                    'vc.STATUS as STATUS_USER',
                    DB::raw('SUM(ra.REKAP_POIN) as TOTAL_POIN')
                )
                ->where('ra.ID_PERIODE', $id_periode)
                ->whereNotNull('ra.REKAP_POIN')
                ->where('vc.STATUS', 'MHS')
                ->groupBy('ra.NIM', 'vc.NAMA', 'vc.JKEL', 'vc.STATUS')
                ->orderBy('TOTAL_POIN', 'desc')
                ->first();

            $dataPemenangMahasiswa = null;
            if ($pemenangMahasiswa) {
                $dataPemenangMahasiswa = [
                    'nama' => $pemenangMahasiswa->nama,
                    'nomor_induk' => $pemenangMahasiswa->nomor_induk,
                    'jkel' => $pemenangMahasiswa->jkel,
                    'total_poin' => (int)$pemenangMahasiswa->total_poin,
                    // FIX: Akses properti menggunakan lowercase 'status_user'
                    'status_user' => $pemenangMahasiswa->status_user,
                ];
            }

            // B. Cari Pemenang Dosen/Tendik
            $pemenangDosenTendik = DB::table('REKAPPOIN_AWARD as ra')
                ->join('V_CIVITAS as vc', 'ra.NIM', '=', 'vc.ID_CIVITAS')
                ->select(
                    'ra.NIM as NOMOR_INDUK',
                    'vc.NAMA',
                    'vc.JKEL',
                    'vc.STATUS as STATUS_USER',
                    DB::raw('SUM(ra.REKAP_POIN) as TOTAL_POIN')
                )
                ->where('ra.ID_PERIODE', $id_periode)
                ->whereNotNull('ra.REKAP_POIN')
                ->whereIn('vc.STATUS', ['DOSEN', 'TENDIK'])
                ->groupBy('ra.NIM', 'vc.NAMA', 'vc.JKEL', 'vc.STATUS')
                ->orderBy('TOTAL_POIN', 'desc')
                ->first();

            $dataPemenangDosenTendik = null;
            if ($pemenangDosenTendik) {
                $dataPemenangDosenTendik = [
                    'nama' => $pemenangDosenTendik->nama,
                    'nomor_induk' => $pemenangDosenTendik->nomor_induk,
                    'jkel' => $pemenangDosenTendik->jkel,
                    'total_poin' => (int)$pemenangDosenTendik->total_poin,
                    // FIX: Akses properti menggunakan lowercase 'status_user'
                    'status_user' => $pemenangDosenTendik->status_user,
                ];
            }

            return response()->json([
                'success' => true,
                'status' => 'berakhir',
                'periode' => [
                    'id_periode' => $periodeTerakhirSelesai->id_periode,
                    'nama_periode' => $periodeTerakhirSelesai->nama_periode,
                ],
                'winner_mahasiswa' => $dataPemenangMahasiswa,
                'winner_dosen_tendik' => $dataPemenangDosenTendik
            ]);
        }

        // Jika tidak ada periode sama sekali
        return response()->json([
            'success' => true,
            'status' => 'aktif',
            'message' => 'Tidak ada periode yang terdefinisi.'
        ]);
    }

    public function readPeriode()
    {
        // Ambil data periode dari database
        $data = DB::table('periode_award')->get();

        return response()->json($data);
    }
    public function getPeriodeAktif() // Tidak perlu Request $request jika tidak dipakai
    {
        $currentDate = Carbon::now()->toDateString();

        // Mengambil dari tabel 'periode_award' (berdasarkan method readPeriode Anda)
        $periodeAktif = DB::table('periode_award')
            ->where('TGL_MULAI', '<=', $currentDate)
            ->where('TGL_SELESAI', '>=', $currentDate)
            // Anda mungkin punya kolom status seperti 'STATUS_PERIODE' = 'AKTIF'
            // ->where('STATUS_PERIODE', 'AKTIF') 
            ->orderBy('ID_PERIODE', 'desc') // Mengambil yang terbaru jika ada overlap
            ->first(); // Hanya mengambil satu periode aktif

        if ($periodeAktif) {
            // Mengembalikan response yang konsisten dengan ekspektasi RekapPoinService:
            // {'data': [{'ID_PERIODE': xxx, ...}]}
            return response()->json(['data' => [$periodeAktif]]);
        } else {
            return response()->json(['data' => [], 'message' => 'Tidak ada periode aktif ditemukan saat ini.'], 404);
        }
    }
    public function insPeriode(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id'          => 'required|numeric',
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

        DB::beginTransaction(); // Memulai transaksi

        try {
            // Eksekusi prosedur insert periode
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

            // --- TAMBAHAN: Insert semua user ke rekappoin_award ---
            $id_periode_baru = $request->id;
            $users = DB::table('v_civitas')->select('ID_CIVITAS')->get();
            $kategori_ids = [1, 2, 3, 4]; // Asumsi ID Kategori yang akan di-insert

            foreach ($users as $user) {
                foreach ($kategori_ids as $kategori_id) {
                    // Ambil ID_REKAP_POIN terakhir dan tambahkan 1
                    $lastId = DB::table('rekappoin_award')->max('ID_REKAP_POIN');
                    $nextId = $lastId + 1;

                    DB::table('rekappoin_award')->insert([
                        'ID_REKAP_POIN' => $nextId,
                        'NIM' => $user->id_civitas,
                        'ID_PERIODE' => $id_periode_baru,
                        'ID_KATEGORI' => $kategori_id,
                        'REKAP_POIN' => 0, // Nilai awal
                        'REKAP_JUMLAH' => 0, // Nilai awal
                        'TGL_REKAP' => now()
                    ]);
                }
            }
            // --- AKHIR TAMBAHAN ---

            DB::commit(); // Menyimpan semua perubahan jika berhasil

            return response()->json([
                'success' => true,
                'message' => 'Data periode berhasil ditambahkan dan semua user telah diinisialisasi di tabel rekap poin.',
                'data'    => $request->all()
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua perubahan jika ada kesalahan
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi proses, transaksi dibatalkan.',
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
