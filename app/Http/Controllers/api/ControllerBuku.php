<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerBuku extends Controller
{
    public function readBuku()
    {
        $data = DB::table('v_buku_pust')->get();
        return response()->json($data);
    }
    public function searchbuku(Request $request)
    {
        $keyword = $request->get('q'); // Tangkap keyword dari input user

        $buku = DB::table('V_BUKU_PUST')
            ->select('INDUK', 'JUDUL', 'PENGARANG1', 'PENGARANG2', 'PENGARANG3')
            ->when($keyword, function ($query, $keyword) {
                $query->where('INDUK', 'like', "%{$keyword}%")
                    ->orWhere('JUDUL', 'like', "%{$keyword}%");
            })
            ->get();

        return response()->json($buku);
    }
}