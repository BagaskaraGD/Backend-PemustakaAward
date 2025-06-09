<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerKaryawan extends Controller
{
    public function readKaryawan()
    {
        $data = DB::table('v_karyawan')->get();
        return response()->json($data);
    }
    public function searchkaryawan(Request $request)
    {
        $keyword = $request->get('q'); // Tangkap keyword dari input user

        $Karyawan = DB::table('V_KARYAWAN')
            ->select('NIK', 'NAMA')
            ->where('STATUS', 'A')
            ->when($keyword, function ($query, $keyword) {
                $query->where('NIK', 'like', "%{$keyword}%")
                    ->orWhere('NAMA', 'like', "%{$keyword}%");
            })
            ->get();

        return response()->json($Karyawan);
    }

}