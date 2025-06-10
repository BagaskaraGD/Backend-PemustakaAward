<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ControllerKota extends Controller
{
    public function readKota()
    {
        $kota = DB::table('v_kota')->get();
        return response()->json([
            'success' => true,
            'data'    => $kota
        ]);
    }
    public function insKota(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'nama' => 'required|string',
            'jenis' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::table('v_kota')->insert([
            'ID' => $request->id,
            'NAMA' => $request->nama,
            'JENIS' => $request->jenis,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data kota berhasil ditambahkan!'
        ]);
    }
}
