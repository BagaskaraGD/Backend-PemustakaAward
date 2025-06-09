<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerPerusahaan extends Controller
{
    public function readPerusahaan()
    {
        $data = DB::table('perusahaan_pemateri_pust')->get();
        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }
    public function insPerusahaan(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'nama' => 'required|string',
            'alamat' => 'required|string',
            'kota' => 'required|string',
            'email' => 'required|string',
            'telp' => 'required|string',
            'kontak' => 'required|string',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Eksekusi stored procedure
            DB::connection('oracle')->statement(
                "BEGIN 
                    BOBBY21.INS_PUSTAWARD_PRSH_MATERI(
                        :pid, 
                        :pnama, 
                        :palamat, 
                        :pkota, 
                        :pemail, 
                        :ptelp,
                        :pcp                   
                    ); 
                END;",
                [
                    'pid' => $request->id,
                    'pnama' => $request->nama,
                    'palamat' => $request->alamat,
                    'pkota' => $request->kota,
                    'pemail' => $request->email,
                    'ptelp' => $request->telp,
                    'pcp' => $request->kontak
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil ditambahkan menggunakan prosedur Oracle',
                'data' => $request->all()
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function delPerusahaan($id)
    {
        // Validasi input ID
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $exists = DB::connection('oracle')->selectOne("
                SELECT COUNT(*) AS JUMLAH 
                FROM KPTA_22410100003.PERUSAHAAN_PEMATERI_PUST
                WHERE ID_PERUSAHAAN = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi stored procedure DELETE
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.DEL_PUSTAWARD_PRSH_MATERI(:pid); 
            END;",
                ['pid' => $id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus menggunakan prosedur Oracle',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur DELETE',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
