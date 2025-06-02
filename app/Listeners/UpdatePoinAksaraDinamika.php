<?php

namespace App\Listeners;

use App\Events\AksaraDinamikaDisetujui;
use App\Services\RekapPoinService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ControllerPembobotan; // Jika Anda mengambil bobot dari sini
use Illuminate\Http\Request; // Diperlukan jika ControllerPembobotan membutuhkannya

class UpdatePoinAksaraDinamika
{
    protected $rekapPoinService;
    protected $controllerPembobotan; // Inject controller bobot

    public function __construct(RekapPoinService $rekapPoinService, ControllerPembobotan $controllerPembobotan)
    {
        $this->rekapPoinService = $rekapPoinService;
        $this->controllerPembobotan = $controllerPembobotan;
    }

    public function handle(AksaraDinamikaDisetujui $event): void
    {
        $nim = $event->nim;
        $idKategori = 4; // ID Kategori untuk Aksara Dinamika

        // 1. Hitung jumlah Aksara Dinamika yang disetujui untuk $nim
        $rekapJumlah = DB::table('HISTORI_STATUS as hs')
            ->join('AKSARA_DINAMIKA as ad', 'ad.ID_AKSARA_DINAMIKA', '=', 'hs.ID_AKSARA_DINAMIKA')
            ->where('ad.nim', $nim)
            ->where('hs.STATUS', 'diterima') // Pastikan hanya yang disetujui
            ->count();

        // 2. Ambil bobot poin untuk Aksara Dinamika
        // Anda perlu memastikan cara memanggil getpoinaksaradinamika dengan benar
        // Mungkin perlu membuat instance Request dummy atau merefaktor getpoinaksaradinamika
        $request = new Request(); // Atau inject Request jika listener dipanggil via HTTP context
        $responseBobot = $this->controllerPembobotan->getpoinaksaradinamika($request);
        $bobotData = $responseBobot->getData(true); // Ambil sebagai array
        $bobotPerAksara = $bobotData['data'][0]['POIN_PEMBOBOTAN'] ?? 0; // Sesuaikan path

        $rekapPoin = $rekapJumlah * $bobotPerAksara;

        // 3. Panggil service untuk update
        $this->rekapPoinService->updateRekapPoin($nim, $idKategori, $rekapJumlah, $rekapPoin);
    }
}
