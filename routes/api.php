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
use App\Http\Controllers\Api\ControllerPemateriKegiatan;
use App\Http\Controllers\Api\ControllerPembobotan;
use App\Http\Controllers\Api\ControllerPeriode;
use App\Http\Controllers\Api\ControllerRangeKunjungan;
use App\Http\Controllers\Api\ControllerRekapPoin;
use App\Http\Controllers\Api\ControllerSertifikat;
use Illuminate\Support\Facades\DB;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('aksara-dinamika')->group(function () {
    // GET /aksara-dinamika - Read all data
    Route::get('/', [ControllerAksaraDinamika::class, 'readAksaraDinamika']);

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

    // POST /aksara-dinamika - Insert new data
    Route::post('/', [ControllerRekapPoin::class, 'insRekapPoin']);

    // PUT /aksara-dinamika - Update data
    Route::put('/{id}', [ControllerRekapPoin::class, 'updRekapPoin']);

    Route::put('/aksara/{nim}/{rekap_jumlah}', [ControllerRekapPoin::class, 'updateJumAksara']);

    Route::put('/kegiatan/{nim}/{rekap_jumlah}/{rekap_poin}', [ControllerRekapPoin::class, 'updateJumKegiatan']);

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
});

Route::prefix('karyawan')->group(function () {

    Route::get('/', [ControllerKaryawan::class, 'readKaryawan']);
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

Route::get('/myrank/{id}', function ($id) {
   
    $data = DB::table(DB::raw("(
        SELECT 
            ra.nim,
            vc.nama,
            vc.status,
            SUM(COALESCE(ra.REKAP_POIN, 0)) AS total_rekap_poin,
            ROW_NUMBER() OVER (ORDER BY SUM(COALESCE(ra.REKAP_POIN, 0)) DESC) AS peringkat
        FROM REKAPPOIN_AWARD ra
        JOIN V_CIVITAS vc ON ra.nim = vc.ID_CIVITAS
        GROUP BY ra.nim, vc.nama, vc.status
        HAVING SUM(COALESCE(ra.REKAP_POIN, 0)) > 0
    ) ranking"))
        ->where('nim', $id)
        ->get();
    return response()->json(['count' => $data]);
});
