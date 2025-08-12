<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;;


use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Import controllers
use App\Http\Controllers\Api\ControllerSertifikat;
use App\Http\Controllers\Api\ControllerKegiatan;
use App\Http\Controllers\Api\ControllerCivitas;

class ControllerGeneratorSertifikat extends Controller
{
    protected $dummyKegiatanIdForTemplate;
    protected $globalTemplateNimIdentifier;

    public function __construct()
    {
        $this->dummyKegiatanIdForTemplate = config('app.dummy_kegiatan_id_for_template', '0');
        $this->globalTemplateNimIdentifier = config('app.global_template_nim_identifier', 'TPLGLB');
    }

    public function generateUntukKegiatan(Request $request, $idKegiatan, $nim)
    {
        Log::info("[GENERATE_SERTIFIKAT] Memulai untuk NIM: {$nim}, ID Kegiatan: {$idKegiatan}");

        try {
            $sertifikatController = new ControllerSertifikat();
            $kegiatanController = new ControllerKegiatan();
            $civitasController = new ControllerCivitas();

            // 1. Ambil Data Template Global
            $responseSertifikat = $sertifikatController->readSertifikat();
            $sertifikatList = json_decode($responseSertifikat->getContent());
            $templateData = collect($sertifikatList)->first(function ($sertifikat) {
                return (isset($sertifikat->nim) && strtoupper(trim($sertifikat->nim)) === strtoupper($this->globalTemplateNimIdentifier));
            });

            if (!$templateData || !isset($templateData->nama_file)) {
                return response("Template sertifikat global tidak ditemukan.", 404);
            }
            $pathBackgroundAbsolut = storage_path('app/public/templates_sertifikat/' . $templateData->nama_file);
            if (!file_exists($pathBackgroundAbsolut)) {
                 return response("File template background '{$templateData->nama_file}' tidak ditemukan.", 404);
            }

            // 2. Ambil Data Kegiatan
            $responseKegiatan = $kegiatanController->readKegiatan();
            $kegiatanList = json_decode($responseKegiatan->getContent());
            $kegiatanData = collect($kegiatanList)->firstWhere('id_kegiatan', $idKegiatan);

            if (!$kegiatanData) {
                return response("Data kegiatan tidak ditemukan", 404);
            }
            $judulKegiatanFormat = strtoupper($kegiatanData->judul_kegiatan ?? 'NAMA KEGIATAN');

            // 3. Ambil Data Peserta (Civitas)
            $responseCivitas = $civitasController->readCivitas(new Request());
            $civitasList = json_decode($responseCivitas->getContent());
            $pesertaData = collect($civitasList)->firstWhere('id_civitas', $nim);

            if (!$pesertaData) {
                return response("Data peserta tidak ditemukan", 404);
            }
            $namaPeserta = strtoupper($pesertaData->nama ?? $nim);

            // 4. Siapkan data untuk view
            $dataUntukView = [
                'namaPeserta'           => $namaPeserta,
                'judulKegiatanFormat'   => $judulKegiatanFormat,
                'pathBackgroundAbsolut' => $pathBackgroundAbsolut,
            ];

            // 5. Generate PDF
            $pdf = Pdf::loadView('sertifikat.template_global_dinamis', $dataUntukView)->setPaper('a4', 'landscape');
            
            // 6. Kembalikan PDF untuk di-download
            $namaFileDownload = 'Sertifikat-' . Str::slug($namaPeserta) . '-' . Str::slug($judulKegiatanFormat) . '.pdf';
            return $pdf->download($namaFileDownload);

        } catch (\Exception $e) {
            Log::error("[GENERATE_SERTIFIKAT] Exception: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response("Terjadi kesalahan saat generate sertifikat PDF: " . $e->getMessage(), 500);
        }
    }
}