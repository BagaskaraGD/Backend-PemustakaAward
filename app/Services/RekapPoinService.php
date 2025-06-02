<?php

// App\Services\RekapPoinService.php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\ControllerPeriode; // Untuk mendapatkan periode aktif

class RekapPoinService
{
    protected $controllerPeriode;

    public function __construct(ControllerPeriode $controllerPeriode)
    {
        $this->controllerPeriode = $controllerPeriode;
    }

    private function getPeriodeAktifId()
    {
        // Mengambil ID periode aktif dari ControllerPeriode
        $response = $this->controllerPeriode->getPeriodeAktif(request()); // Anda mungkin perlu meng-inject Request jika diperlukan
        $periodeData = $response->getData();
        return $periodeData->data[0]->ID_PERIODE ?? null; // Sesuaikan dengan struktur response Anda
    }

    public function updateRekapPoin($nim, $idKategori, $rekapJumlah, $rekapPoin)
    {
        $idPeriodeAktif = $this->getPeriodeAktifId();
        if (!$idPeriodeAktif) {
            // Handle jika tidak ada periode aktif, mungkin log error
            return false;
        }

        // Logika untuk UPSERT (Update or Insert) ke REKAPPOIN_AWARD
        // Anda mungkin perlu menyesuaikan ini berdasarkan struktur tabel dan PK Anda
        // Contoh menggunakan MERGE statement Oracle (jika menggunakan DB::statement)
        // Atau menggunakan updateOrInsert jika menggunakan Eloquent (tapi Anda pakai DB facade)

        $currentDate = now()->toDateString(); // Untuk TGL_REKAP

        // Cek apakah entri sudah ada
        $existingRekap = DB::table('REKAPPOIN_AWARD')
            ->where('NIM', $nim)
            ->where('ID_KATEGORI', $idKategori)
            ->where('ID_PERIODE', $idPeriodeAktif)
            ->first();

        if ($existingRekap) {
            DB::table('REKAPPOIN_AWARD')
                ->where('NIM', $nim)
                ->where('ID_KATEGORI', $idKategori)
                ->where('ID_PERIODE', $idPeriodeAktif)
                ->update([
                    'REKAP_JUMLAH' => $rekapJumlah,
                    'REKAP_POIN' => $rekapPoin,
                    'TGL_REKAP' => $currentDate, // Atau sesuai kebutuhan
                ]);
        } else {
            // Ambil ID_REKAP_POIN berikutnya jika auto-increment tidak diatur di level DB
            // atau jika Anda mengelolanya secara manual.
            // $nextIdRekapPoin = DB::table('REKAPPOIN_AWARD')->max('ID_REKAP_POIN') + 1;

            DB::table('REKAPPOIN_AWARD')->insert([
                // 'ID_REKAP_POIN' => $nextIdRekapPoin, // Jika diperlukan
                'NIM' => $nim,
                'ID_PERIODE' => $idPeriodeAktif,
                'ID_KATEGORI' => $idKategori,
                'REKAP_JUMLAH' => $rekapJumlah,
                'REKAP_POIN' => $rekapPoin,
                'TGL_REKAP' => $currentDate, // Atau sesuai kebutuhan
            ]);
        }
        return true;
    }
}
