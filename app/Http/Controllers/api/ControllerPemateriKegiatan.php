<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerPemateriKegiatan extends Controller
{
    public function readPemateriKegiatan()
    {
        // Ambil data pemateri dari tabel
        $data = DB::table('pematerikegiatan_pust')->get();
        return response()->json($data);
    }
    public function insPemateriKegiatan(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id'             => 'required|numeric',
            'nama_pemateri'  => 'required|string|max:100',
            'id_perusahaan'  => 'required|numeric',
            'email'          => 'required|email|max:100',
            'no_hp'          => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Eksekusi stored procedure INSERT
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.INS_PUSTAWARD_PEMATERI(
                    :pid, 
                    :pnama, 
                    :pprsh, 
                    :pemail, 
                    :php
                ); 
            END;",
                [
                    'pid'    => $request->id,
                    'pnama'  => $request->nama_pemateri,
                    'pprsh'  => $request->id_perusahaan,
                    'pemail' => $request->email,
                    'php'    => $request->no_hp
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data pemateri berhasil ditambahkan melalui prosedur Oracle',
                'data'    => $request->all()
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur INS_PUSTAWARD_PEMATERI',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function updPemateriKegiatan(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_pemateri'  => 'required|string|max:100',
            'id_perusahaan'  => 'required|numeric',
            'email'          => 'required|email|max:100',
            'no_hp'          => 'required|string|max:20',
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
            FROM KPTA_22410100003.pematerikegiatan_pust
            WHERE id_pemateri = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi prosedur update
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.UPD_PUSTAWARD_PEMATERI(
                    :pid, 
                    :pnama, 
                    :pprsh, 
                    :pemail, 
                    :php
                ); 
            END;",
                [
                    'pid'    => $id,
                    'pnama'  => $request->nama_pemateri,
                    'pprsh'  => $request->id_perusahaan,
                    'pemail' => $request->email,
                    'php'    => $request->no_hp
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data pemateri berhasil diupdate melalui prosedur Oracle',
                'data'    => array_merge(['id' => $id], $request->all())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur UPD_PUSTAWARD_PEMATERI',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function delPemateriKegiatan(Request $request, $id)
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
            FROM KPTA_22410100003.pematerikegiatan_pust
            WHERE id_pemateri = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi stored procedure DELETE
            DB::connection('oracle')->statement(
                "BEGIN 
            BOBBY21.DEL_PUSTAWARD_PEMATERI(
                :pid, 
                :pnama, 
                :pprsh, 
                :pemail, 
                :php
            ); 
        END;",
                [
                    'pid'    => $id,
                    'pnama'  => $request->input('nama_pemateri'),
                    'pprsh'  => $request->input('id_perusahaan'),
                    'pemail' => $request->input('email'),
                    'php'    => $request->input('no_hp')
                ]
            );

            return response()->json([
                'success'    => true,
                'message'    => 'Data pemateri berhasil dihapus melalui prosedur Oracle',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur DEL_PUSTAWARD_PEMATERI',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
