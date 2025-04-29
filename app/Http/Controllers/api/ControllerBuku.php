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
}