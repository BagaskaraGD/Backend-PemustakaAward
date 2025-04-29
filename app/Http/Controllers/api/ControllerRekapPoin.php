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
}
