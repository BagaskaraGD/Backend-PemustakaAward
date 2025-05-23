<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Tambahkan Log untuk debugging jika perlu
use Illuminate\Support\Facades\Validator;

class ControllerHistoriStatus extends Controller
{
    public function readHistoriStatus()
    {
        try {
            $data = DB::connection('oracle')->table('histori_status')->get();
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error reading histori status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca histori status',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function insHistoriStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_histori_status'   => 'required|numeric',
            'id_aksara_dinamika'  => 'required|numeric',
            'status'              => 'required|string|max:100',
            'keterangan'          => 'required|string|max:255',
            'tgl_status'          => 'required|date',
            'user'                => 'required|string|max:50' // Asumsi 'user' adalah string (NIK/ID), sesuaikan max length
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Pastikan nama bind variable ke-6 (:puser_pust atau nama lain)
            // SESUAI DENGAN DEFINISI PARAMETER DI PROSEDUR PL/SQL ORACLE
            // INS_PUSTAWARD_HISTORI_STATUS
            Log::info('Attempting to insert histori status with data: ', $request->all());

            DB::connection('oracle')->statement(
                "BEGIN 
                    INS_PUSTAWARD_HISTORI_STATUS(
                        :pid, 
                        :paksara, 
                        :pstatus, 
                        :pket, 
                        :ptgl, 
                        :puser_pust_status -- Bind variable ke-6 untuk USER_PUST_STATUS
                    ); 
                 END;",
                [
                    'pid'               => $request->id_histori_status,
                    'paksara'           => $request->id_aksara_dinamika,
                    'pstatus'           => $request->status,
                    'pket'              => $request->keterangan,
                    'ptgl'              => $request->tgl_status,
                    'puser_pust_status' => $request->user // Mengambil nilai dari field 'user' yang dikirim frontend
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Histori status berhasil ditambahkan',
                'data'    => $request->all() // Mengembalikan data yang diterima untuk konfirmasi
            ]);
        } catch (\Exception $e) {
            Log::error('Error inserting histori status: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception_trace' => $e->getTraceAsString() // Untuk debugging lebih detail jika perlu
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan histori status',
                // Mengembalikan pesan error Oracle jika ada, atau pesan umum
                'error'   => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updHistoriStatus(Request $request, $id) // Parameter $id biasanya untuk ID record yang diupdate
    {
        // Validasi untuk update mungkin sedikit berbeda, misalnya id_histori_status tidak boleh diubah
        // atau diambil dari URL ($id) bukan dari body request.
        // Untuk saat ini, saya asumsikan validasinya sama dengan insert, tapi ini perlu diklarifikasi.
        $dataToValidate = $request->all();
        // Jika id_histori_status diambil dari URL path parameter, tambahkan ke data untuk validasi
        // if (!isset($dataToValidate['id_histori_status'])) {
        //     $dataToValidate['id_histori_status'] = $id;
        // }


        $validator = Validator::make($dataToValidate, [
            'id_histori_status'   => 'required|numeric', // Ini adalah ID record HISTORI_STATUS yang akan diupdate
            'id_aksara_dinamika'  => 'sometimes|required|numeric', // 'sometimes' jika tidak selalu diupdate
            'status'              => 'sometimes|required|string|max:100',
            'keterangan'          => 'sometimes|required|string|max:255',
            'tgl_status'          => 'sometimes|required|date',
            'user'                => 'sometimes|required|string|max:50' // User yang melakukan update
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Pastikan nama bind variable dan jumlahnya
            // SESUAI DENGAN DEFINISI PARAMETER DI PROSEDUR PL/SQL ORACLE
            // UPD_PUSTAWARD_HISTORI_STATUS
            // Prosedur update mungkin memiliki parameter yang berbeda (misalnya, hanya field yang diupdate)
            Log::info('Attempting to update histori status ID: ' . $request->id_histori_status . ' with data: ', $request->all());

            // Ambil field yang ada di request saja untuk update
            $updateData = [
                'pid'     => $request->id_histori_status, // ID record yang diupdate
                'paksara' => $request->id_aksara_dinamika, // Jika ID Aksara bisa diubah
                'pstatus' => $request->status,
                'pket'    => $request->keterangan,
                'ptgl'    => $request->tgl_status,
                'puser_pust_status' => $request->user // User yang melakukan update
            ];

            // Hapus field yang tidak ada di request agar tidak mengirim null ke prosedur jika tidak dimaksudkan
            // Ini tergantung bagaimana prosedur UPD_PUSTAWARD_HISTORI_STATUS menghandle parameter null
            // $updateData = array_filter($updateData, function($value) {
            //     return $value !== null;
            // });
            // if (!isset($request->id_aksara_dinamika)) unset($updateData['paksara']);
            // ... dan seterusnya untuk field opsional


            DB::connection('oracle')->statement(
                "BEGIN 
                    UPD_PUSTAWARD_HISTORI_STATUS(
                        :pid, 
                        :paksara, 
                        :pstatus, 
                        :pket, 
                        :ptgl,
                        :puser_pust_status -- Asumsi prosedur update juga butuh user
                    ); 
                 END;",
                $updateData
            );

            return response()->json([
                'success' => true,
                'message' => 'Histori status berhasil diperbarui',
                'data'    => $request->all()
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating histori status: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception_trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui histori status',
                'error'   => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delHistoriStatus($id)
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
            Log::info('Attempting to delete histori status ID: ' . $id);
            DB::connection('oracle')->statement(
                "BEGIN 
                    DEL_PUSTAWARD_HISTORI_STATUS(:pid); 
                 END;",
                ['pid' => $id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Histori status berhasil dihapus',
                'deleted_id' => $id
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting histori status: ' . $e->getMessage(), [
                'id_to_delete' => $id,
                'exception_trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus histori status',
                'error'   => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getHistoriStatus($nim, $id)
    {
        $data = DB::table('HISTORI_STATUS as hs')
            ->join('AKSARA_DINAMIKA as ad', 'hs.ID_AKSARA_DINAMIKA', '=', 'ad.ID_AKSARA_DINAMIKA')
            ->where('ad.NIM', $nim)
            ->where('ad.INDUK_BUKU', $id)
            ->select('*')
            ->orderBy('hs.tgl_status', 'desc')
            ->get();
        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }
}
