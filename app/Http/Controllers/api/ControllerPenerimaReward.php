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

    protected function getActivePeriodeId(): ?string
    {
        $now = Carbon::now();
        // Fetch all periods to determine the active one based on dates
        $allPeriodes = DB::table('PERIODE_AWARD')->get(); // Fetch all period data

        foreach ($allPeriodes as $periode) {
            try {
                $tglMulaiCarbon = Carbon::parse($periode->TGL_MULAI);
                $tglSelesaiCarbon = Carbon::parse($periode->TGL_SELESAI)->endOfDay();

                if ($now->between($tglMulaiCarbon, $tglSelesaiCarbon)) {
                    return $periode->ID_PERIODE ?? $periode->id_periode ?? $periode->id ?? null;
                }
            } catch (\Exception $e) {
                Log::error('Error parsing date for active period detection: ' . $e->getMessage(), (array)$periode);
            }
        }
        return null; // No active period found
    }

    public function readPenerimaReward(Request $request)
    {
        try {
            $query = DB::table('PENERIMA_REWARD as pr')
                ->select(
                    'pr.id_penerima',
                    'pr.id_reward',
                    'pr.id_civitas',
                    'pr.tgl_terima',
                    'ra.level_reward',
                    'ra.slot_reward',
                    'ra.bentuk_reward',
                    'ra.id_periode',
                    'civ.nama as nama_civitas',
                    'civ.status as status_civitas'
                )
                ->leftJoin('REWARD_AWARD as ra', 'pr.id_reward', '=', 'ra.id_reward')
                ->leftJoin('V_CIVITAS as civ', 'pr.id_civitas', '=', 'civ.id_civitas');

            $inputPeriodeId = $request->input('id_periode');

            if ($request->filled('id_periode')) {
                // If a period ID is explicitly provided and not empty
                Log::info('Filtering Penerima Reward with requested periode ID: ' . $inputPeriodeId);
                $query->whereRaw('ra.id_periode = ?', [$inputPeriodeId]);
            } else {
                // If no period ID is provided or it's empty, use the currently active period
                $activePeriodeId = $this->getActivePeriodeId(); // Call the new helper method

                if ($activePeriodeId) {
                    $query->where('ra.id_periode', $activePeriodeId);
                    Log::info('No periode ID provided for Penerima Reward, using active periode ID: ' . $activePeriodeId);
                } else {
                    Log::warning('No active periode found for Penerima Reward. Returning empty data.');
                    return response()->json(['success' => true, 'data' => []]);
                }
            }

            $data = $query->orderBy('ra.level_reward', 'asc')
                ->orderBy('pr.tgl_terima', 'asc')
                ->get();

            $data->transform(function ($item) {
                if (strtolower($item->status_civitas) === 'mahasiswa') {
                    $item->nim = $item->id_civitas;
                    $item->nidn = null;
                } else {
                    $item->nim = null;
                    $item->nidn = $item->id_civitas;
                }
                return $item;
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Gagal membaca data Penerima Reward: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server.'], 500);
        }
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

            $activePeriode = DB::table('PERIODE_AWARD')
                ->whereRaw('CURRENT_DATE BETWEEN TGL_MULAI AND TGL_SELESAI')
                ->first();

            if (!$activePeriode) {
                return response()->json(['success' => false, 'message' => 'Periode aktif tidak ditemukan.'], 404);
            }
            $activePeriodeId = $activePeriode->id_periode;

            $rewards = DB::table('REWARD_AWARD')
                ->where('ID_PERIODE', $activePeriodeId)
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
                'current_periode_id' => $activePeriodeId,
                'current_periode_nama' => $activePeriode->nama_periode
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
