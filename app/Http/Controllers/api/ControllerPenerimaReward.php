<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ControllerPenerimaReward extends Controller
{
    // ... (metode readPenerimaReward, insPenerimaReward, delPenerimaReward tetap sama seperti sebelumnya) ...
    // Pastikan metode insPenerimaReward dan lainnya sudah ada di sini dari jawaban sebelumnya.

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
        Log::info('Attempting to claim reward. Payload:', $request->all());

        $validator = Validator::make($request->all(), [
            'id_reward'  => 'required|numeric',
            'id_civitas' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Claim reward validation failed.', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'message' => 'Data input tidak valid.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $id_reward = $request->id_reward;
        $id_civitas = $request->id_civitas;
        $tgl_terima_obj = Carbon::now();
        $tgl_terima_str = $tgl_terima_obj->format('Y-m-d');

        try {
            Log::info("Checking existing claim for civitas: {$id_civitas}, reward: {$id_reward}");
            $existingClaim = DB::table('PENERIMA_REWARD')
                ->where('ID_REWARD', $id_reward)
                ->where('ID_CIVITAS', $id_civitas)
                ->first();

            if ($existingClaim) {
                Log::info("Claim already exists for civitas: {$id_civitas}, reward: {$id_reward}");
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah pernah mengklaim hadiah ini.',
                ], 409);
            }

            Log::info("Checking reward details for reward_id: {$id_reward}");
            $rewardDetails = DB::table('REWARD_AWARD')
                ->where('ID_REWARD', $id_reward)
                ->first();

            if (!$rewardDetails) {
                Log::warning("Reward details not found for reward_id: {$id_reward}");
                return response()->json([
                    'success' => false,
                    'message' => 'Detail reward tidak ditemukan.',
                ], 404);
            }

            $slot_reward = $rewardDetails->slot_reward;
            Log::info("Slot for reward_id {$id_reward} is: {$slot_reward}");

            $claimed_count = DB::table('PENERIMA_REWARD')
                ->where('ID_REWARD', $id_reward)
                ->count();
            Log::info("Claimed count for reward_id {$id_reward} is: {$claimed_count}");

            if ($claimed_count >= $slot_reward) {
                Log::info("Slots full for reward_id {$id_reward}. Claimed: {$claimed_count}, Slot: {$slot_reward}");
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon maaf, slot hadiah untuk level ini sudah penuh.',
                ], 403);
            }

            $lastIdResult = DB::table('PENERIMA_REWARD')->selectRaw('COALESCE(MAX(ID_PENERIMA), 0) as last_id')->first();
            $new_penerima_id = $lastIdResult->last_id + 1;
            Log::info("New penerima_id generated: {$new_penerima_id}");

            DB::connection('oracle')->statement(
                "BEGIN
                    BOBBY21.INS_PUSTAWARD_PENERIMA_REWARD(
                        :PID,
                        :PREWARD,
                        :PCIVITAS,
                        TO_DATE(:PTGL, 'YYYY-MM-DD')
                    );
                END;",
                [
                    'PID'      => $new_penerima_id,
                    'PREWARD'  => $id_reward,
                    'PCIVITAS' => $id_civitas,
                    'PTGL'     => $tgl_terima_str
                ]
            );
            Log::info("Successfully inserted claim for civitas: {$id_civitas}, reward: {$id_reward}, penerima_id: {$new_penerima_id}");

            return response()->json([
                'success' => true,
                'message' => 'Selamat! Hadiah berhasil diklaim.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error claiming reward for civitas ' . $id_civitas . ' and reward ' . $id_reward . ': ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengklaim hadiah. Silakan coba lagi nanti atau hubungi administrator.',
            ], 500);
        }
    }

    public function delPenerimaReward($id)
    {
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
                'message' => 'Gagal menghapus data penerima reward: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function getCurrentActiveRewards(Request $request)
    {
        try {
            $id_civitas = $request->query('id_civitas'); // Ambil id_civitas dari query parameter

            $currentPeriode = DB::table('PERIODE_AWARD')
                ->orderBy('TGL_MULAI', 'desc')
                ->first();

            if (!$currentPeriode) {
                return response()->json(['success' => false, 'message' => 'Periode aktif tidak ditemukan.'], 404);
            }
            $currentPeriodeId = $currentPeriode->id_periode;

            $rewards = DB::table('REWARD_AWARD')
                ->where('ID_PERIODE', $currentPeriodeId)
                ->orderBy('LEVEL_REWARD', 'asc')
                ->get();

            // Tambahkan informasi claimed_slots dan sudah_diklaim_user
            foreach ($rewards as $reward) {
                // Jumlah global slot yang sudah diklaim untuk reward ini
                $reward->claimed_slots = DB::table('PENERIMA_REWARD')
                    ->where('ID_REWARD', $reward->id_reward)
                    ->count();

                // Apakah pengguna saat ini sudah mengklaim reward ini?
                if ($id_civitas) {
                    $userClaim = DB::table('PENERIMA_REWARD')
                        ->where('ID_REWARD', $reward->id_reward)
                        ->where('ID_CIVITAS', $id_civitas)
                        ->first();
                    $reward->sudah_diklaim_user = !is_null($userClaim);
                } else {
                    $reward->sudah_diklaim_user = false; // Default jika tidak ada id_civitas
                }
            }

            return response()->json([
                'success' => true,
                'data' => $rewards,
                'current_periode_id' => $currentPeriodeId,
                'current_periode_nama' => $currentPeriode->nama_periode
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching active rewards: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data reward aktif.',
            ], 500);
        }
    }
}
