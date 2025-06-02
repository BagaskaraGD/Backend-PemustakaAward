<?php 
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerPenerimaReward extends Controller
{
    public function readPenerimaReward()
    {
        $data = DB::table('PENERIMA_REWARD')->get();
        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }

    public function insPenerimaReward(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id'         => 'required|numeric',
            'id_reward'       => 'required|numeric',
            'id_civitas'  => 'required|string',
            'tgl_terima' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Eksekusi prosedur insert
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.INS_PUSTAWARD_PENERIMA_REWARD(
                    :PID, 
                    :PREWARD, 
                    :PCIVITAS, 
                    :PTGL
                ); 
            END;",
                [
                    'PID'      => $request->id,
                    'PREWARD'    => $request->id_reward,
                    'PCIVITAS'   => $request->id_civitas,
                    'PTGL' => $request->tgl_terima
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data penerima reward berhasil ditambahkan menggunakan prosedur Oracle',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data penerima reward: '.$e->getMessage(),
            ], 500);
        }
    }
    public function delPenerimaReward($id)
    {
        // Validasi ID
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
            // Eksekusi prosedur delete
            DB::connection('oracle')->statement(
                "BEGIN 
                    BOBBY21.DEL_PUSTAWARD_PENERIMA_REWARD(:PID); 
                END;",
                ['PID' => $id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data penerima reward berhasil dihapus',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data penerima reward: '.$e->getMessage(),
            ], 500);
        }
    }
}


?>