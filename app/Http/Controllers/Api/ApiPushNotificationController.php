<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiPushNotificationController extends Controller
{
    public function index(Request $request)
    {
        //https://justusmember.co.id/api/cronjob/pushnotification

        // ambil data admin yang akan di push dengan status on progress
        $dataProcess = DB::table('pushnotification')
            ->where('status_send', '=', '2')
            ->orderBy('id', 'ASC')
            ->first();

        // ambil data admin yang akan di push dengan status belum di progress
        $dataNotSend = DB::table('pushnotification')
            ->where('status_send', '=', '0')
            ->orderBy('id', 'ASC')
            ->first();

        // jika masih ada data admin yang akan di push berstatus on progress
        if ($dataProcess !== null) {
            //cari data admin yang akan di push di tabel pushnotification_process_send
            //dengan status 0 atau belum di kirim
            $id_pushnotification = $dataProcess->id;
            $pushProcess = DB::table('pushnotification_process_send')
                ->where('id_pushnotification', '=', $id_pushnotification)
                ->where('status_send', '=', '0')
                ->orderBy('id', 'ASC')
                ->get();
            // jika data di tabel pushnotification_process_send
            //dengan status 0 atau belum di kirim masih ada
            if (count($pushProcess) > 0) {
                // lakukan pengiriman data ke FCM
                DB::table('pushnotification_process_send')
                    ->where('id_pushnotification', '=', $id_pushnotification)
                    ->where('status_send', '=', '0')
                    ->orderBy('id', 'ASC')
                    ->chunk(100, function ($pushProcess) use ($dataProcess) {
                        foreach ($pushProcess as $rowData) {
                            $id = $rowData->id;
                            $id_pushnotification = $rowData->id_pushnotification;
                            $id_costumers = $rowData->id_costumers;
                            $firebase_token_device = $rowData->firebase_token_device;
                            $status_send = $rowData->id;

                            // proses kirim pesan FCM
                            $data = array(
                                "to" => $firebase_token_device,
                                "notification" => array(
                                    "title" => $dataProcess->title,
                                    "body" => $dataProcess->body
                                ),
                                "data" => array(
                                    "photo" => $dataProcess->photo
                                )
                            );
                            $jsonData = json_encode($data);
                            $ch = curl_init('https://fcm.googleapis.com/fcm/send');
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Authorization: key=AAAAslzPpRc:APA91bEHothpRmZG8xt9mkS_mqMD8dRJhxAwGnv-7eLudDdfydMBo12cw31GEFYQN7c0tsGbi22Wa3gqObbE17pBmTDXpmxUwtkdN7hqEkpgLxgVCFKkdH--RcpfiN3E1LyXCr1LHRSc',
                                'Content-Type: application/json'
                            ));
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            curl_close($ch);
                            // selesai proses kirim pesan FCM
                            //                    

                            //jika kirim pesan selesai/sukses
                            // setiap data yang telah di kirim ke FCM
                            // rubah statusnya menjadi 1 sebagai tanda telah dikirim ke FCM
                            DB::table('pushnotification_process_send')
                                ->where("id", "=", $id)
                                ->update(["status_send" => "1"]);
                        }
                    });
            } else if (count($pushProcess) == 0) {
                // jika data di tabel pushnotification_process_send
                //dengan status 0 atau belum di kirim sudah tidak ada
                //alias semua data sudah dikirim
                // update status data admin yang akan di push menjadi 1 
                // sebagai penanda proses pengiriman ke semua target device selesai
                DB::table('pushnotification')
                    ->where("id", "=", $id_pushnotification)
                    ->update(["status_send" => "1"]);
            }
        } else if ($dataNotSend !== null) { // jika masih ada data admin yang akan di push berstatus belum di progress
            // proses backup proses copy data secara manual query bagian ini ada di bawah halaman sudah di backup
            // ambil semua data token tabel costumers simpan di tabel pushnotification_process_send 
            // yang di beri tanda id_pushnotification (identify ada data admin yang akan di push)
            $id_pushnotification = $dataNotSend->id;
            DB::table('costumers')
                ->select('id', 'firebase_token_device')
                ->where('firebase_token_device', '!=', '')
                ->orderBy('id', 'DESC')
                ->chunk(100, function ($dataTokenDevices) use ($id_pushnotification) {
                    $dataInsert = [];
                    foreach ($dataTokenDevices as $rowData) {
                        $dataInsert[] = [
                            "id_pushnotification" => $id_pushnotification,
                            "id_costumers" => $rowData->id,
                            "firebase_token_device" => $rowData->firebase_token_device,
                        ];
                    }
                    DB::table('pushnotification_process_send')->insert($dataInsert);
                });
            // setelah data tersimpan di pushnotification_process_send 
            // update ada data admin yang akan di push nya
            // menjadi status on progress
            DB::table('pushnotification')
                ->where("id", "=", $id_pushnotification)
                ->update(["status_send" => "2"]);
        }
    }
    public function loadDataTargetNotif()
    {
        // Ambil data yang diperlukan
        $result = DB::table('pushnotification_target as pt')
            ->join('pushnotification as p', 'pt.id_pushnotification', '=', 'p.id')
            ->select('pt.id as ptid', 'pt.token', 'p.title', 'p.body', 'p.photo')
            ->where('pt.status_send', '0')
            ->orderBy('pt.id', 'ASC')
            ->get();

        $updateIds = [];
        $pushData = [];

        foreach ($result as $row) {
            $dataPush = [
                'token' => $row->token,
                'title' => $row->title,
                'body' => $row->body,
                'foto' => $row->photo,
            ];

            if (trim($row->token) != "") {
                $pushData[] = $dataPush;
            }

            $updateIds[] = $row->ptid;
        }

        // Kirim push notification dalam batch
        foreach ($pushData as $data) {
            $this->sendPushNotification($data);
        }

        // Batch update status_send
        if (!empty($updateIds)) {
            DB::table('pushnotification_target')
                ->whereIn('id', $updateIds)
                ->update(['status_send' => '1']);
        }
    }
    public function sendPushNotification($data)
    {
        $token = $data['token'];
        $title = $data['title'];
        $body = $data['body'];
        $foto = $data['foto'];

        // proses kirim pesan FCM
        $data = array(
            "to" => $token,
            "notification" => array(
                "title" => $title,
                "body" => $body
            ),
            "data" => array(
                "photo" => $foto
            )
        );
        $jsonData = json_encode($data);
        $ch = curl_init('https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: key=AAAAslzPpRc:APA91bEHothpRmZG8xt9mkS_mqMD8dRJhxAwGnv-7eLudDdfydMBo12cw31GEFYQN7c0tsGbi22Wa3gqObbE17pBmTDXpmxUwtkdN7hqEkpgLxgVCFKkdH--RcpfiN3E1LyXCr1LHRSc',
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }
    public function loadPromo8a8s()
    {
        $data_8agustus_8september = DB::table('webmastermember_member.ViewMemberPoint_Promo_8_Agustus_September as vmp')
            ->leftJoin('point as p', 'p.no_bill', '=', 'vmp.no_bill')
            ->leftJoin('costumers as c', 'c.id', '=', 'p.costumer_id')
            ->leftJoin('pushnotification_target as pt', 'pt.email_member', '=', 'c.email')
            ->select(
                'vmp.*',
                'c.email as email',
                DB::raw("IF(IFNULL(pt.email_member, '') != '', '1', '0') as TelahTerkirim")
            )
            ->whereRaw("IFNULL(pt.email_member, '') = ''")
            ->orderBy('vmp.created_at', 'desc')
            ->get()->toJson();
        $obj_8agustus_8september = json_decode($data_8agustus_8september);
        $data_email = [];
        foreach ($obj_8agustus_8september as $row) {
            $data_email[] = $row->email;
        }
        if (count($data_email) > 0) {
            $title = "Reward Justus Group";
            $body = "Selamat Anda berkesempatan untuk mendapatkan Reward dari Justus Group";
            $target = implode(",", $data_email) . ",fahmifeb@yahoo.co.id";
            $fileFoto = null;

            // Siapkan data notifikasi
            $notificationData = [
                'title' => $title,
                'body' => $body,
                'target' => $target,
                'status_send' => $target != "all" ? '1' : '0'
            ];

            // Simpan notifikasi dan ambil ID
            $notificationId = DB::table('pushnotification')->insertGetId($notificationData);

            $fileName = "";
            if ($fileFoto) {
                $fileName = $notificationId . '.' . $fileFoto->getClientOriginalExtension();
                $fileFoto->move(public_path('assets/file_photo_notification'), $fileName);

                // Update nama file foto di tabel pushnotification
                DB::table('pushnotification')->where('id', $notificationId)->update(['photo' => $fileName]);
            }

            // Siapkan data target notifikasi
            $arr_txt_target = explode(",", $target);
            $customers = DB::table('costumers')->whereIn('email', $arr_txt_target)->get(['email', 'firebase_token_device']);

            // Proses pengiriman notifikasi ke setiap target
            foreach ($customers as $customer) {
                DB::table('pushnotification_target')->insert([
                    'email_member' => $customer->email,
                    'token' => $customer->firebase_token_device,
                    'id_pushnotification' => $notificationId,
                ]);
            }
        }
    }
}
