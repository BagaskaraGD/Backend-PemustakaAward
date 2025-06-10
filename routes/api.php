<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ControllerAksaraDinamika;
use App\Http\Controllers\Api\ControllerBuku;
use App\Http\Controllers\Api\ControllerCivitas;
use App\Http\Controllers\Api\ControllerHadirKegiatan;
use App\Http\Controllers\Api\ControllerHistoriStatus;
use App\Http\Controllers\Api\ControllerJadwalKegiatan;
use App\Http\Controllers\Api\ControllerJenisBobot;
use App\Http\Controllers\Api\ControllerJenisRange;
use App\Http\Controllers\Api\ControllerKaryawan;
use App\Http\Controllers\Api\ControllerKategoriNilai;
use App\Http\Controllers\Api\ControllerKegiatan;
use App\Http\Controllers\api\ControllerKota;
use App\Http\Controllers\Api\ControllerPemateriKegiatan;
use App\Http\Controllers\Api\ControllerPembobotan;
use App\Http\Controllers\Api\ControllerPenerimaReward;
use App\Http\Controllers\Api\ControllerPeriode;
use App\Http\Controllers\api\ControllerPerusahaan;
use App\Http\Controllers\Api\ControllerRangeKunjungan;
use App\Http\Controllers\Api\ControllerRekapPoin;
use App\Http\Controllers\Api\ControllerReward;
use App\Http\Controllers\Api\ControllerSertifikat;
use Illuminate\Support\Facades\DB;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('aksara-dinamika')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerAksaraDinamika::class, 'readAksaraDinamika']);

    Route::get('/aksara-user/{nim}', [ControllerAksaraDinamika::class, 'getuserakasradinamika']);

    Route::get('/check-review', [ControllerAksaraDinamika::class, 'checkReview']);

    Route::get('/last-id', [ControllerAksaraDinamika::class, 'getLastId']);

    Route::get('last-idbuku', [ControllerAksaraDinamika::class, 'getLastIdBuku']);

    Route::get('/detail-for-edit/{id}/{induk_buku}/{nim}', [ControllerAksaraDinamika::class, 'getAksaraDinamikaForEdit']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerAksaraDinamika::class, 'insAksaraDinamika']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerAksaraDinamika::class, 'updAksaraDinamika']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerAksaraDinamika::class, 'delAksaraDinamika']);
});


Route::prefix('kegiatan')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerKegiatan::class, 'readKegiatan']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerKegiatan::class, 'insKegiatan']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerKegiatan::class, 'updKegiatan']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerKegiatan::class, 'delKegiatan']);
});

Route::prefix('jadwal-kegiatan')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerJadwalKegiatan::class, 'readJadwalKegiatan']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerJadwalKegiatan::class, 'insJadwalKegiatan']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerJadwalKegiatan::class, 'updJadwalKegiatan']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerJadwalKegiatan::class, 'delJadwalKegiatan']);
});

Route::prefix('pemateri-kegiatan')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerPemateriKegiatan::class, 'readPemateriKegiatan']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerPemateriKegiatan::class, 'insPemateriKegiatan']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerPemateriKegiatan::class, 'updPemateriKegiatan']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerPemateriKegiatan::class, 'delPemateriKegiatan']);
});

Route::prefix('hadir-kegiatan')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerHadirKegiatan::class, 'readHadirKegiatan']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerHadirKegiatan::class, 'insHadirKegiatan']);

    Route::get('/check-kegiatan', [ControllerHadirKegiatan::class, 'checkKegiatan']);

    Route::get('/last-idhadir', [ControllerHadirKegiatan::class, 'getLastIdHadir']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerHadirKegiatan::class, 'updHadirKegiatan']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerHadirKegiatan::class, 'delHadirKegiatan']);

    Route::get('/kehadiran/{nim}', [ControllerHadirKegiatan::class, 'getkehadiran']);
});

Route::prefix('periode')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerPeriode::class, 'readPeriode']);

    Route::get('/aktif', [ControllerPeriode::class, 'getPeriodeAktif']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerPeriode::class, 'insPeriode']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerPeriode::class, 'updPeriode']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerPeriode::class, 'delPeriode']);
});

Route::prefix('reward')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerReward::class, 'readReward']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerReward::class, 'insReward']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerReward::class, 'updReward']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerReward::class, 'delReward']);
});

Route::prefix('jenis-range')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerJenisRange::class, 'readJenisRange']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerJenisRange::class, 'insJenisRange']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerJenisRange::class, 'updJenisRange']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerJenisRange::class, 'delJenisRange']);
});

Route::prefix('range-kunjungan')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerRangeKunjungan::class, 'readRangeKunjungan']);

    Route::get('/kunjungan', [ControllerRangeKunjungan::class, 'getRangeKunjungan']);

    Route::get('/pinjaman', [ControllerRangeKunjungan::class, 'getRangePinjaman']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerRangeKunjungan::class, 'insRangeKunjungan']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerRangeKunjungan::class, 'updRangeKunjungan']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerRangeKunjungan::class, 'delRangeKunjungan']);
});

Route::prefix('pembobotan')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerPembobotan::class, 'readPembobotan']);

    Route::get('/level1', [ControllerPembobotan::class, 'getNilailevel1']);

    Route::get('/level2', [ControllerPembobotan::class, 'getNilailevel2']);

    Route::get('/level3', [ControllerPembobotan::class, 'getNilailevel3']);

    Route::get('/aksara-dinamika', [ControllerPembobotan::class, 'getpoinaksaradinamika']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerPembobotan::class, 'insPembobotan']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerPembobotan::class, 'updPembobotan']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerPembobotan::class, 'delPembobotan']);
});

Route::prefix('jenis-bobot')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerJenisBobot::class, 'readJenisBobot']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerJenisBobot::class, 'insJenisBobot']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerJenisBobot::class, 'updJenisBobot']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerJenisBobot::class, 'delJenisBobot']);
});

Route::prefix('histori-status')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerHistoriStatus::class, 'readHistoriStatus']);

    Route::get('/{nim}/{id}', [ControllerHistoriStatus::class, 'getHistoriStatus']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerHistoriStatus::class, 'insHistoriStatus']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerHistoriStatus::class, 'updHistoriStatus']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerHistoriStatus::class, 'delHistoriStatus']);
});

Route::prefix('rekap-poin')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerRekapPoin::class, 'readRekapPoin']);

    Route::get('/leaderboard/mhs', [ControllerRekapPoin::class, 'readleaderboardMHS']);

    Route::get('/leaderboard/dosen', [ControllerRekapPoin::class, 'readleaderboardDosen']);

    Route::get('/jumlah/kegiatan/{nim}', [ControllerRekapPoin::class, 'getjumlahkegiatan']);

    Route::get('/jumlah/aksara/{nim}', [ControllerRekapPoin::class, 'getjumlahaksara']);

    Route::get('/jumlah/kunjungan/{nim}', [ControllerRekapPoin::class, 'getjumlahkunjungan']);

    Route::get('/jumlah/pinjaman/{nim}', [ControllerRekapPoin::class, 'getjumlahpinjaman']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerRekapPoin::class, 'insRekapPoin']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerRekapPoin::class, 'updRekapPoin']);

    Route::put('/aksara/{nim}/{rekap_jumlah}/{rekap_poin}', [ControllerRekapPoin::class, 'updateJumAksara']);

    Route::put('/kegiatan/{nim}/{rekap_jumlah}/{rekap_poin}', [ControllerRekapPoin::class, 'updateJumKegiatan']);

    Route::put('/kunjungan/{nim}/{rekap_jumlah}/{rekap_poin}', [ControllerRekapPoin::class, 'updateJumKunjungan']);

    Route::put('/pinjaman/{nim}/{rekap_jumlah}/{rekap_poin}', [ControllerRekapPoin::class, 'updateJumPinjaman']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerRekapPoin::class, 'delRekapPoin']);
});

Route::prefix('sertifikat')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerSertifikat::class, 'readSertifikat']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerSertifikat::class, 'insSertifikat']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerSertifikat::class, 'updSertifikat']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerSertifikat::class, 'delSertifikat']);
});

Route::prefix('kategori-nilai')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerKategoriNilai::class, 'readKategoriNilai']);

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerKategoriNilai::class, 'insKategoriNilai']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerKategoriNilai::class, 'updKategoriNilai']);

    // DELETE /aksara-dinamika/{id} - Delete data by ID
    Route::delete('/{id}', [ControllerKategoriNilai::class, 'delKategoriNilai']);
});

Route::prefix('civitas')->group(function () {

    Route::get('/', [ControllerCivitas::class, 'readCivitas']);
});

Route::prefix('buku')->group(function () {

    Route::get('/', [ControllerBuku::class, 'readBuku']);


    Route::get('/search', [ControllerBuku::class, 'searchbuku']);
});

Route::prefix('karyawan')->group(function () {

    Route::get('/', [ControllerKaryawan::class, 'readKaryawan']);

    Route::get('/search', [ControllerKaryawan::class, 'searchkaryawan']);
});
Route::prefix('perusahaan')->group(function () {

    Route::get('/', [ControllerPerusahaan::class, 'readPerusahaan']);
    Route::post('/', [ControllerPerusahaan::class, 'insPerusahaan'])->name('api.perusahaan.create'); 
    Route::delete('/{id}', [ControllerPerusahaan::class, 'delPerusahaan']);
});
Route::prefix('penerima-reward')->group(function () {
    Route::get('/', [ControllerPenerimaReward::class, 'readPenerimaReward']);
    Route::post('/', [ControllerPenerimaReward::class, 'insPenerimaReward'])->name('api.reward.claim'); // Named for easier URL generation if needed
    Route::delete('/{id}', [ControllerPenerimaReward::class, 'delPenerimaReward']);
    Route::get('/rewards/active', [ControllerPenerimaReward::class, 'getCurrentActiveRewards'])->name('api.rewards.active');
});
Route::prefix('kota')->group(function () {
    Route::get('/', [ControllerKota::class, 'readKota']);
    Route::post('/', [ControllerKota::class, 'insKota'])->name('api.kota.create'); // Named for easier URL generation if needed
});


Route::get('/challenge-count/{id}', function ($id) {
    $count = DB::table('HISTORI_STATUS as hs')
        ->join('AKSARA_DINAMIKA as ad', 'ad.ID_AKSARA_DINAMIKA', '=', 'hs.ID_AKSARA_DINAMIKA')
        ->where('ad.nim', $id)
        ->where('hs.STATUS', 'diterima')
        ->count();

    return response()->json(['count' => $count]);
});

Route::get('/kegiatan-count/{nim}', function ($nim) {
    $count = DB::table('HADIRKEGIATAN_PUST')
        ->where('NIM', $nim)
        ->count();

    return response()->json(['count' => $count]);
});

Route::get('/kunjungan-count/{nim}', function ($nim) {
    $count = DB::table('V_KUNJUNGAN_PERPUS')
        ->where('NIM', $nim)
        ->count();

    return response()->json(['count' => $count]);
});

Route::get('/pinjaman-count/{nim}', function ($nim) {
    $count = DB::table('V_HIS_BUKU')
        ->where('NIM', $nim)
        ->count();

    return response()->json(['count' => $count]);
});


Route::get('/myrank/mhs/{id}', function ($id) {
    // Langkah 1: Dapatkan ID periode yang sedang aktif
    $activePeriode = DB::table('PERIODE_AWARD')
        ->whereRaw('CURRENT_DATE BETWEEN TGL_MULAI AND TGL_SELESAI')
        ->select('id_periode') // Menggunakan 'id_periode' (lowercase) sesuai perbaikan sebelumnya
        ->first();

    if (!$activePeriode) {
        // Jika tidak ada periode aktif, kembalikan null atau pesan error yang sesuai
        return response()->json(['data' => null, 'message' => 'Tidak ada periode aktif yang ditemukan.'], 404);
    }
    $activePeriodeId = $activePeriode->id_periode;

    // Langkah 2: Gunakan ControllerRekapPoin untuk mendapatkan query builder dasar
    // Laravel dapat me-resolve instance controller menggunakan helper app()
    $rekapPoinController = app(ControllerRekapPoin::class);

    // Dapatkan query builder dasar yang sudah melakukan select, join, where untuk periode aktif, dan groupBy
    $baseQueryBuilder = $rekapPoinController->getRankedMhsBaseBuilder($activePeriodeId);
    // Kolom yang dihasilkan oleh $baseQueryBuilder:
    // nama, nim, status, jkel, total_rekap_poin,
    // jumlah_aksara_dinamika, jumlah_kegiatan, jumlah_kunjungan, jumlah_pinjaman

    // Langkah 3: Buat subquery untuk menambahkan ROW_NUMBER() (peringkat)
    // ROW_NUMBER() akan dihitung berdasarkan hasil dari $baseQueryBuilder (yang dialiaskan sebagai 'sub')
    $rankedStudentsSubQuery = DB::query()
        ->fromSub($baseQueryBuilder, 'sub') // 'sub' adalah alias untuk hasil dari $baseQueryBuilder
        ->select(
            'sub.nama',
            'sub.nim',
            'sub.status',
            'sub.jkel',
            'sub.total_rekap_poin',
            // Hitung peringkat berdasarkan urutan yang sama dengan readleaderboardMHS
            DB::raw('ROW_NUMBER() OVER (ORDER BY 
                        sub.total_rekap_poin DESC, 
                        sub.jumlah_aksara_dinamika DESC, 
                        sub.jumlah_kegiatan DESC, 
                        sub.jumlah_kunjungan DESC, 
                        sub.jumlah_pinjaman DESC, 
                        sub.nim ASC
                    ) as peringkat')
        );

    // Langkah 4: Ambil data peringkat untuk mahasiswa dengan $id tertentu
    // Kita membuat query lagi dari hasil $rankedStudentsSubQuery (yang dialiaskan sebagai 'ranked_list')
    $data = DB::query()
        ->fromSub($rankedStudentsSubQuery, 'ranked_list') // 'ranked_list' adalah alias untuk hasil subquery sebelumnya
        ->select('nama', 'nim', 'status', 'jkel', 'total_rekap_poin', 'peringkat')
        ->where('nim', $id)
        ->first();

    // Jika data mahasiswa tidak ditemukan di peringkat (misalnya, belum memiliki poin)
    if (!$data) {
        // Anda bisa mencoba mengambil data mahasiswa dari v_civitas sebagai fallback
        // atau mengembalikan null/pesan khusus.
        // Contoh fallback:
        $civitasData = DB::table('v_civitas')->where('ID_CIVITAS', $id)->where('status', 'MHS')->first();
        if ($civitasData) {
            $data = (object)[
                'nama' => $civitasData->nama,
                'nim' => $civitasData->id_civitas, // atau $id
                'status' => $civitasData->status,
                'jkel' => $civitasData->jkel,
                'total_rekap_poin' => 0,
                'peringkat' => null // Atau 'Tidak Terperingkat'
            ];
        } else {
            return response()->json(['data' => null, 'message' => 'Mahasiswa tidak ditemukan.'], 404);
        }
    }

    return response()->json(['data' => $data]);
});

Route::get('/myrank/dosen/{id}', function ($id) {
    $rankedStudents = DB::table('REKAPPOIN_AWARD as ra')
        ->join('v_civitas as vc', 'ra.NIM', '=', 'vc.ID_CIVITAS')
        ->select(
            'vc.nama',
            'ra.nim',
            'vc.status',
            'vc.jkel',
            DB::raw('SUM(NVL(ra.REKAP_POIN, 0)) total_rekap_poin'),
            DB::raw('ROW_NUMBER() OVER (ORDER BY SUM(NVL(ra.REKAP_POIN, 0)) DESC) peringkat')
        )
        ->wherein('vc.status', ['DOSEN', 'TENDIK'])
        ->groupBy('ra.nim', 'vc.nama', 'vc.status', 'vc.jkel');

    $data = DB::table(DB::raw("({$rankedStudents->toSql()}) ranked_students"))
        ->mergeBindings($rankedStudents)
        ->select('nama', 'nim', 'status', 'jkel', 'total_rekap_poin', 'peringkat')
        ->where('nim', $id)
        ->first();

    return response()->json(['data' => $data]);
});
