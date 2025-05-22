<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ControllerReward extends Controller
{

    public function readReward()
    {
        $data = DB::table('REWARD_AWARD')->get();
        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }
    public function insReward(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id'         => 'required|numeric',
            'idperiode'       => 'required|numeric',
            'level'  => 'required|numeric',
            'bentuk' => 'required|string|max:50',
            'slot' => 'required|number'
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
                BOBBY21.INS_PUSTAWARD_REWARD(
                    :PID, 
                    :PPERIODE, 
                    :PLEVEL, 
                    :PBENTUK,
                    :PSLOT
                ); 
            END;",
                [
                    'PID'      => $request->id,
                    'PPERIODE'    => $request->idperiode,
                    'PLEVEL'   => $request->level,
                    'PBENTUK' => $request->bentuk,
                    'PSLOT' => $request->slot
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data reward berhasil ditambahkan menggunakan prosedur Oracle',
                'data'    => $request->all()
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur INS_PUSTAWARD_REWARD',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function updReward(Request $request, $id)
    {
        // Validasi input request (tanpa id di body)
        $validator = Validator::make($request->all(), [
            'idperiode'       => 'required|numeric',
            'level'  => 'required|numeric',
            'bentuk' => 'required|string|max:50',
            'slot' => 'required|number'
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
            FROM KPTA_22410100003.reward_award 
            WHERE ID_REWARD = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi prosedur update dengan named binding
            DB::connection('oracle')->statement(
                "BEGIN 
            BOBBY21.UPD_PUSTAWARD_REWARD(
                :PID, 
                :PPERIODE, 
                :PLEVEL, 
                :PBENTUK,
                :PSLOT
            ); 
        END;",
                [
                    'pid'      => $id, // ← ambil dari parameter route
                    'PPERIODE'    => $request->idperiode,
                    'PLEVEL'   => $request->level,
                    'PBENTUK' => $request->bentuk,
                    'PSLOT' => $request->slot
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Data reward berhasil diupdate via prosedur Oracle',
                'data'    => array_merge(['id' => $id], $request->all())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal eksekusi prosedur UPD_PUSTAWARD_REWARD',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function delReward( $id)
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
            FROM KPTA_22410100003.reward_award 
            WHERE ID_REWARD = :id", ['id' => $id]);

            if (!$exists || $exists->jumlah == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan ID tersebut tidak ditemukan.'
                ], 404);
            }
            // Eksekusi prosedur DELETE
            DB::connection('oracle')->statement(
                "BEGIN 
                BOBBY21.DEL_PUSTAWARD_REWARD(:pid); 
            END;",
                ['pid' => $id]
            );

            return response()->json([
                'success'     => true,
                'message'     => 'Data reward berhasil dihapus menggunakan prosedur Oracle',
                'deleted_id'  => $id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengeksekusi prosedur DEL_PUSTAWARD_REWARD',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
