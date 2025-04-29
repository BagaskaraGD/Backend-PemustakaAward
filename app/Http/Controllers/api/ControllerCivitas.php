<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerCivitas extends Controller
{
    public function readCivitas(Request $request)
    {
        $data = DB::table('v_civitas')->get();
        return response()->json($data);
    }
}