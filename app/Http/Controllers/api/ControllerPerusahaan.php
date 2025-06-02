<?php
namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

Class ControllerPerusahaan extends Controller
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
        $validator = Validator::make($request->all(), [
            'nama_perusahaan' => 'required|string|max:255',
            'alamat_perusahaan' => 'required|string|max:255',
            'no_telp_perusahaan' => 'required|string|max:15',
            'email_perusahaan' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        DB::table('perusahaan_pemateri_pust')->insert([
            'nama_perusahaan' => $request->nama_perusahaan,
            'alamat_perusahaan' => $request->alamat_perusahaan,
            'no_telp_perusahaan' => $request->no_telp_perusahaan,
            'email_perusahaan' => $request->email_perusahaan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perusahaan berhasil ditambahkan'
        ]);
    }
    public function deletePerusahaan($id)
    {
        $data = DB::table('perusahaan_pemateri_pust')->where('id', $id)->delete();
        if ($data) {
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data gagal dihapus'
            ]);
        }
    }
}














?>

