<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

// Import controller API yang akan digunakan
use App\Http\Controllers\Api\ControllerSertifikat;

class ControllerTemplateSertifikat extends Controller
{
    protected $dummyKegiatanIdForTemplate;
    protected $globalTemplateNimIdentifier;

    public function __construct()
    {
        $this->dummyKegiatanIdForTemplate = config('app.dummy_kegiatan_id_for_template', '0');
        $this->globalTemplateNimIdentifier = config('app.global_template_nim_identifier', 'TPLGLB');
    }

    public function index()
    {
        $currentTemplate = null;
        $errorMessage = null;

        try {
            $sertifikatController = new ControllerSertifikat();
            $response = $sertifikatController->readSertifikat();
            $sertifikatList = json_decode($response->getContent());

            if ($response->status() == 200 && is_array($sertifikatList)) {
                $foundTemplateData = collect($sertifikatList)->first(function ($sertifikat) {
                    $s = (object) $sertifikat;
                    return (isset($s->nim) && strtoupper(trim($s->nim)) === strtoupper($this->globalTemplateNimIdentifier)) &&
                           (isset($s->id_kegiatan) && trim((string)$s->id_kegiatan) === trim((string)$this->dummyKegiatanIdForTemplate));
                });

                if ($foundTemplateData) {
                    $currentTemplate = $foundTemplateData;
                    // Logika untuk display date & name
                }
            } else {
                 $errorMessage = 'Gagal memuat data template dari API internal.';
            }
        } catch (\Exception $e) {
            Log::error('[SERTIFIKAT_TEMPLATE_INDEX] Exception: ' . $e->getMessage());
            $errorMessage = 'Terjadi kesalahan sistem saat memuat template.';
        }

        if ($errorMessage) {
            session()->flash('error', $errorMessage);
        }

        return view('sertifikat.template_management', compact('currentTemplate')); // Anda perlu membuat view ini
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_template' => 'required|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $sertifikatController = new ControllerSertifikat();

        // Hapus template lama jika ada
        $responseOld = $sertifikatController->readSertifikat();
        $oldTemplates = json_decode($responseOld->getContent());
        $oldGlobalTemplate = collect($oldTemplates)->first(function ($s) {
            return (isset($s->nim) && strtoupper(trim($s->nim)) === strtoupper($this->globalTemplateNimIdentifier)) &&
                   (isset($s->id_kegiatan) && (string)$s->id_kegiatan === (string)$this->dummyKegiatanIdForTemplate);
        });

        if ($oldGlobalTemplate) {
            $oldId = $oldGlobalTemplate->id_sertifikat;
            $oldNamaFile = $oldGlobalTemplate->nama_file;
            Storage::disk('public')->delete('templates_sertifikat/' . $oldNamaFile);
            $sertifikatController->delSertifikat($oldId);
        }

        // Simpan file baru
        $file = $request->file('file_template');
        $fileName = 'template-global-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('templates_sertifikat', $fileName, 'public');

        // Buat record baru di DB
        if ($path) {
            $lastIdResponse = $sertifikatController->getLastId();
            $lastIdData = json_decode($lastIdResponse->getContent());
            $nextIdSertifikat = ($lastIdData->last_id ?? 0) + 1;

            $apiRequest = new Request([
                'id' => $nextIdSertifikat,
                'nim' => $this->globalTemplateNimIdentifier,
                'id_kegiatan' => $this->dummyKegiatanIdForTemplate,
                'nama_file' => $fileName
            ]);

            $resultResponse = $sertifikatController->insSertifikat($apiRequest);
            $result = json_decode($resultResponse->getContent());

            if ($result->success) {
                return redirect()->route('sertifikat.templates.index')->with('success', 'Template sertifikat global berhasil diunggah.');
            } else {
                Storage::disk('public')->delete($path); // Rollback file
                return back()->with('error', 'Gagal menyimpan data template ke database.')->withInput();
            }
        }
        return back()->with('error', 'Gagal mengunggah file template.')->withInput();
    }
}