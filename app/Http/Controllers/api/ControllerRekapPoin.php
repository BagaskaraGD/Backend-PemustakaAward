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
        $data = DB::table('rekappoin_award')->get();
        return response()->json($data);
    }
    public function readleaderboardMHS()
    {
        $data = DB::table('REKAPPOIN_AWARD as ra')
            ->join('v_civitas as vc', 'ra.NIM', '=', 'vc.ID_CIVITAS')
            ->select('vc.nama', 'ra.nim', 'vc.status', 'ra.rekap_jumlah')
            ->where('vc.status', 'MHS')
            ->orderByDesc('ra.rekap_jumlah')
            ->get();
        return response()->json($data);
    }
    public function readleaderboardDOSEN()
    {
        $data = DB::table('REKAPPOIN_AWARD as ra')
            ->join('v_civitas as vc', 'ra.NIM', '=', 'vc.ID_CIVITAS')
            ->select('vc.nama', 'ra.nim', 'vc.status', 'ra.nilai')
            ->where('vc.status', 'DOSEN')
            ->orderByDesc('ra.nilai')
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
            ->select('max(nim)', 'max(nilai)')
            ->where('vc.status', 'DOSEN')
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
    public function updateJumAksara($nim, $rekap_jumlah)
    {
        DB::table('REKAPPOIN_AWARD')
            ->where('nim', $nim)
            ->where('ID_KATEGORI', 4)
            ->update([
                'rekap_jumlah' => $rekap_jumlah
            ]);
        return response()->json([
            'success' => true,
            'message' => 'Jumlah aksara berhasil diperbarui',
            'nim'     => $nim,
            'rekap_jumlah' => $rekap_jumlah
        ]);
    }
    public function updateJumKegiatan() {}
}
