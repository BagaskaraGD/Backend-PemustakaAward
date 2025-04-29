<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerHistoriStatus extends Controller
{
    public function readHistoriStatus()
    {
        $data = DB::table('histori_status')->get();
        return response()->json($data);
    }
    public function insHistoriStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_histori_status'   => 'required|numeric',
            'id_aksara_dinamika'  => 'required|numeric',
            'status'              => 'required|string|max:100',
            'keterangan'          => 'required|string|max:255',
            'tgl_status'          => 'required|date'
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
                    INS_PUSTAWARD_HISTORI_STATUS(
                        :pid, :paksara, :pstatus, :pket, :ptgl
                    ); 
                END;",
                [
                    'pid'     => $request->id_histori_status,
                    'paksara' => $request->id_aksara_dinamika,
                    'pstatus' => $request->status,
                    'pket'    => $request->keterangan,
                    'ptgl'    => $request->tgl_status
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Histori status berhasil ditambahkan',
                'data'    => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan histori status',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function updHistoriStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_histori_status'   => 'required|numeric',
            'id_aksara_dinamika'  => 'required|numeric',
            'status'              => 'required|string|max:100',
            'keterangan'          => 'required|string|max:255',
            'tgl_status'          => 'required|date'
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
                UPD_PUSTAWARD_HISTORI_STATUS(
                    :pid, :paksara, :pstatus, :pket, :ptgl
                ); 
            END;",
                [
                    'pid'     => $request->id_histori_status,
                    'paksara' => $request->id_aksara_dinamika,
                    'pstatus' => $request->status,
                    'pket'    => $request->keterangan,
                    'ptgl'    => $request->tgl_status
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Histori status berhasil diperbarui',
                'data'    => $request->all()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui histori status',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function delHistoriStatus($id)
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
                DEL_PUSTAWARD_HISTORI_STATUS(:pid); 
            END;",
                ['pid' => $id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Histori status berhasil dihapus',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus histori status',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
