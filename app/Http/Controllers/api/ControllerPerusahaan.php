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
}














?>

