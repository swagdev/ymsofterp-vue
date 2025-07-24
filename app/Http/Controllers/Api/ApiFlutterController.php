<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ApiFlutterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return "/api/flutter";
    }
    public function sendEmail(Request $request)
    {
        $mailData = [
            'to' => $request->email,
            'subject' => 'Subjek Email Anda 111111111111',
            'content' => 'Isi Email Anda disini...',
        ];
        $config = [
            'driver' => 'smtp',
            'host' => 'justusmember.co.id',
            'port' => 587,
            'from' => ['address' => 'noreplay@justusmember.co.id', 'name' => 'Justus Steakhouse'],
            'encryption' => 'tls',
            'username' => 'noreplay@justusmember.co.id',
            'password' => 'hallonoreplay',
        ];
        config(['mail' => $config]);
        Mail::send([], [], function ($message) use ($mailData) {
            $message->to($mailData['to'])
                ->subject($mailData['subject'])
                ->setBody($mailData['content'], 'text/plain');
        });
    }
    public function sendEmailFromJustusKu(Request $request)
    {
        $real_email = $request->real_email;
        $new_password = $request->new_password;

        $curl = curl_init();
        $data = array(
            'target_email' => $real_email,
            'message' => 'Hi, your password of Justus Member Account has been reset to: ' . $new_password . '. Thank You.',
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ymsoft-erp.justusku.co.id/api/send_email',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'Authorization: 12IUWw+T#jyRIM#R6f2K'
            ),
        ));
        $response[] = json_decode(curl_exec($curl));
        curl_close($curl);
        return json_encode($response);
    }
    public function sendWA(Request $request)
    {
        $target_number = $request->target_number;
        //$target_number = "6282117589434";
        $email = $request->email;
        $real_email = $request->real_email;
        $new_password = $request->new_password;

        $url = 'https://omnichannel.qiscus.com/whatsapp/v1/qmrzp-slmqpu0rqraf8wc/5480/messages';
        $headers = [
            'Content-Type: application/json',
            'Qiscus-App-Id: qmrzp-slmqpu0rqraf8wc',
            'Qiscus-Secret-Key: 4e8cddbc8868438111245bbd42a89b25',
        ];
        $data = [
            'to' => $target_number,
            'type' => 'template',
            'template' => [
                'namespace' => 'ecef580c_de27_4197_911a_23514d1ddb57',
                'name' => 'member_apps_reset_password',
                'language' => [
                    'policy' => 'deterministic',
                    'code' => 'id'
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $real_email],
                            ['type' => 'text', 'text' => $new_password]
                        ]
                    ]
                ]
            ]
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response[] = json_decode(curl_exec($ch));
        curl_close($ch);
        return json_encode($response);
    }
    public function sendWAFathForce(Request $request)
    {
        $curl = curl_init();
        $api_key = '0VMJQ3HHFKHhtXiT7r64Uc6tvlUaoO';
        $sender = '08112093999';
        $target_number = $request->target_number;
        $email = $request->email;
        $new_password = $request->new_password;

        $data1 = [
            'api_key' => $api_key,
            'sender' => $sender,
            'number' => $target_number,
            'message' => "Hi, $email your password of Justus Member Account has been reset to: $new_password\nThank You.",
        ];

        $curl_options = [
            CURLOPT_URL => 'https://fathwa.com/send-message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ];

        curl_setopt_array($curl, $curl_options);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data1));
        $response[] = json_decode(curl_exec($curl));

        curl_close($curl);
        return json_encode($response);
    }
    public function loginCheckPopup(Request $request)
    {
        $email = $request->email;

        $result = DB::connection('mysql_secondary')->table('pushnotification_target as pt')
            ->join('pushnotification as p', 'pt.id_pushnotification', '=', 'p.id')
            ->select('pt.id as ptid', 'pt.*', 'p.*')
            ->where('pt.email_member', $email)
            ->where('pt.status_read', '0')
            ->where(function($query) {
                $query->whereDate('pt.program_promo_berlaku_hingga', '>=', Carbon::today()) // Tanggal tidak lebih dari hari ini
                      ->orWhereNull('pt.program_promo_berlaku_hingga'); // Atau program_promo_berlaku_hingga adalah NULL
            })
            ->orderBy('pt.id', 'ASC')
            ->first();
        DB::connection('mysql_secondary')->table('pushnotification_target')
            ->where('id', $result->ptid)
            ->update(['status_read' => '1']);

        $arrayResult = $result ? (array) $result : [];
        return json_encode($arrayResult);
    }
    public function loginUpdateToken(Request $request)
    {
        $id_costumers = $request->id_costumers;
        $token = $request->token;
        DB::connection('mysql_secondary')->table('costumers')
            ->where('id', $id_costumers)
            ->update(['firebase_token_device' => $token]);
    }
    public function loginAdminCashier(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        // Cek apakah ada user dengan email dan password yang cocok
        $adminCashier = DB::connection('mysql_secondary')->table('admin_cashier')
            ->where('user', $email)
            ->where('password', $password)
            ->first();

        if ($adminCashier) {
            // Jika ditemukan, kembalikan respons success true
            return response()->json(['success' => true, 'data' => $adminCashier]);
        } else {
            // Jika tidak ditemukan, kembalikan respons success false
            return response()->json(['success' => false]);
        }
    }
    public function login(Request $request)
    {
        /*
            valid_until unknown
            tanggal_aktif < tgl_sekarang
            status_aktif 1
            status_block 0
        */
        //https://justusmember.co.id/api/flutter/login?email=fahmifeb@yahoo.co.id&password=96e79218965eb72c92a549dd5a330112
        $email = $request->email;
        $password = $request->password;
        $token = $request->token;

        $countCostumers = DB::connection('mysql_secondary')->table('costumers')
            ->where('email', $email)
            ->where('android_password', $password)
            ->count();
        if ($countCostumers > 0) {
            $contactUs = DB::connection('mysql_secondary')->table('hubungi')->first();
            $customers = DB::connection('mysql_secondary')->table('costumers')
                ->where('email', $email)
                ->where('android_password', $password)
                ->first();
            if ($customers->status_aktif == 1) {
                if ($customers->status_block == 1) {
                    if (date("Y-m-d") <= $customers->tanggal_aktif) {
                        DB::connection('mysql_secondary')->table('costumers')
                            ->where('email', $email)
                            ->where('android_password', $password)
                            ->update(['firebase_token_device' => $token]);
                        $result["data"] = $customers;
                        $result["result"] = 1;
                    } else {
                        $result["message"] = "Your account is expire.\nPlease contact our admin : $contactUs->chat.";
                        $result["result"] = 0;
                    }
                } else {
                    $result["message"] = "Your account is not active.\nPlease contact our admin : $contactUs->chat.";
                    $result["result"] = 0;
                }
            } else {
                $result["message"] = "Your account is not active or expire.\nPlease contact our admin : $contactUs->chat.";
                $result["result"] = 0;
            }
        } else {
            $result["message"] = 'Your email or passsword is wrong.';
            $result["result"] = 0;
        }
        $result["data_request"] = $request->all();
        return json_encode($result);
    }
    public function register(Request $request)
    {
        //     "request_data": {
        //       "email": "sfghgi",
        //       "nama_lengkap": "jhhhk",
        //       "mobile_phone": 7887978,
        //       "tanggal_lahir": "2023-09-27 00:00:00.000",
        //       "jenis_kelamin": 2,
        //       "pekerjaan": "TNI/Polri",
        //       "pin": 222,
        //       "password": "gggggg",
        //       "confirm_password": "gggggg"
        //     }
        $result = [];
        try {
            $nama               = $request->nama_lengkap;
            $email              = $request->email;
            $telepon            = $request->mobile_phone;
            $tanggalLahir       = $request->tanggal_lahir;
            $jenis_kelamin      = $request->jenis_kelamin;
            $pekerjaan          = $request->pekerjaan;
            $password           = $request->password;
            $pin                = $request->pin;
            $exclusive_member   = $request->exclusive_member ?? '';
            $tambah_tanggal     = mktime(0, 0, 0, date('m') + 0, date('d') + 0, date('Y') + 1);
            $tanggalAktif         = date('Y-m-d', $tambah_tanggal);
            $tanggalRegister     = date('Y-m-d');
            $device             = $request->device;

            $existsExclusiveMemberGenerate = DB::connection('mysql_secondary')->table('exclusive_member')
                ->where('full_id', $exclusive_member)
                ->exists();

            $existsExclusiveMemberCostumers = DB::connection('mysql_secondary')->table('costumers')
                ->where('exclusive_member', $exclusive_member)
                ->exists();

            $existingCustomer = DB::connection('mysql_secondary')->table('costumers')
                ->where('email', $email)
                ->orWhere('telepon', $telepon)
                ->first();
            /*
 
*/
            if ($existingCustomer) {
                $result["message"] = 'Email or mobile phone already exists.';
                $result["result"] = 0;
            } else if ($exclusive_member != "" && $existsExclusiveMemberGenerate == false) {
                $result["message"] = 'The exclusive member code has not been registered in our system.';
                $result["result"] = 0;
            } else if ($exclusive_member != "" && $existsExclusiveMemberCostumers == true) {
                $result["message"] = 'The exclusive member code has been used by another account.';
                $result["result"] = 0;
            } else {
                DB::beginTransaction();
                $data_costumers = [
                    'costumers_id' => '',
                    'barcode' => '',
                    'nik' => '',
                    'name' => $nama,
                    'nama_panggilan' => $nama,
                    'email' => $email,
                    'alamat' => '',
                    'telepon' => $telepon,
                    'kota' => '',
                    'kode_pos' => '',
                    'tempat_lahir' => '',
                    'tanggal_lahir' => $tanggalLahir,
                    'jenis_kelamin' => $jenis_kelamin,
                    'pekerjaan' => $pekerjaan,
                    'agama' => '',
                    'valid_until' => '1',
                    'golongan_darah' => '',
                    'status_warga_negara' => '',
                    'status_kawin' => '',
                    'status_aktif' => '1',
                    'password2' => md5($password),
                    'android_password' => md5($password),
                    'hint' => $password,
                    'pin' => $pin,
                    'exclusive_member' => $exclusive_member,
                    'first_fb' => '0',
                    'tanggal_aktif' => $tanggalAktif,
                    'tanggal_register' => $tanggalRegister,
                    'device' => $device,
                    'status_block' => '1',
                ];
                $insertedId = DB::connection('mysql_secondary')->table('costumers')->insertGetId($data_costumers);

                $newid = "U" . str_pad($insertedId, 4, "0", STR_PAD_LEFT);
                $barcode = 'https://chart.googleapis.com/chart?cht=qr&chs=400x400&chl=' . $newid . '&choe=UTF-8';
                $data_costumers_update = [
                    'costumers_id' => $newid,
                    'barcode' => $barcode,
                ];
                DB::connection('mysql_secondary')->table('costumers')->where('id', $insertedId)->update($data_costumers_update);

                DB::commit();

                $result["message"] = 'Registration success, you can log in.';
                $result["result"] = 1;
            }
        } catch (\Exception $e) {
            DB::rollback();
            $result["message"] = 'Registration failed, error: ' . $e->getMessage();
            $result["result"] = 0;
        }
        $result["request_data"] = $request->all();
        return json_encode($result);
    }
    public function scanPromoBarcode(Request $request)
    {
        $qrcode = $request->qrcode;
        $nobill = $request->nobill;

        $id_inbox = hexdec($qrcode);

        $customer = DB::connection('mysql_secondary')->table('pushnotification_target')
            ->select(DB::raw("CASE 
            WHEN EXISTS (SELECT 1 FROM `point` WHERE no_bill = '{$nobill}') 
            THEN 'ada' 
            ELSE 'tidak ada' 
            END AS ket_no_bill"), 'costumers.*')
            ->leftjoin('costumers', 'costumers.email', '=', 'pushnotification_target.email_member')
            ->where('pushnotification_target.qrcode', $qrcode) // Gunakan tipe data yang sesuai
            ->where('pushnotification_target.program_promo_claimed', '0')
            ->orderBy('pushnotification_target.id', 'DESC')
            ->limit(1)
            ->first();

        // $customer = DB::connection('mysql_secondary')->table('pushnotification_target')
        //     ->select(DB::raw("CASE 
        //     WHEN EXISTS (SELECT 1 FROM `point` WHERE no_bill = '$nobill') 
        //     THEN 'ada' 
        //     ELSE 'tidak ada' 
        //     END AS ket_no_bill"), 'costumers.*')
        //     ->join('costumers', 'costumers.email', '=', 'pushnotification_target.email_member')
        //     ->where('pushnotification_target.qrcode', $qrcode) // Gunakan tipe data yang sesuai
        //     ->where('pushnotification_target.program_promo_claimed', '0')
        //     ->orderBy('pushnotification_target.id', 'DESC')
        //     ->limit(1)
        //     ->frist();

        if (!$customer) {
            $customer = DB::connection('mysql_secondary')->table('pushnotification_target as pt')
                ->select(DB::raw("CASE 
                    WHEN EXISTS (SELECT 1 FROM `point` WHERE no_bill = '{$nobill}') 
                    THEN 'ada'
                    ELSE 'tidak ada'
                    END AS ket_no_bill"), 'c.*')
                ->join('costumers as c', 'c.email', '=', 'pt.email_member')
                ->where('pt.id', $id_inbox)
                ->where('pt.program_promo_claimed', '0')
                ->first();
        }

        // Mengembalikan data ke respon
        return response()->json($customer);
    }
    public function scanPromoBarcodeUpdate(Request $request)
    {
        $nobill = $request->nobill;
        $qrcode = $request->qrcode;
        $id_inbox = hexdec($qrcode);
        $admin_cashier_id = $request->admin_cashier_id;
        $imageName = "";

        // Memproses file gambar jika ada
        if ($request->hasFile('image_ktp')) {
            $image = $request->file('image_ktp');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('assets/file_photo_notification'), $imageName);
            //$this->zeroCrop(public_path('assets/file_photo_notification/' . $imageName));
        }

        // Mencoba untuk memperbarui program promo berdasarkan qrcode terlebih dahulu
        $updated = DB::connection('mysql_secondary')->table('pushnotification_target')
            ->where('qrcode', $qrcode)
            ->update(['program_promo_claimed' => '1']);

        // Jika tidak ada yang ter-update, coba dengan id_inbox
        if (!$updated) {
            $updated = DB::connection('mysql_secondary')->table('pushnotification_target')
                ->where('id', $id_inbox)
                ->update(['program_promo_claimed' => '1']);
        }

        // Ambil ID dari pushnotification_target yang telah di-update
        if ($updated) {
            $targetId = DB::connection('mysql_secondary')->table('pushnotification_target')
                ->where('qrcode', $qrcode)
                ->orWhere('id', $id_inbox)
                ->value('id');

            // Menyimpan data klaim program promo
            DB::connection('mysql_secondary')->table('claim_program_promo')->insert([
                'id_pushnotification_target' => $targetId, // Ganti dengan ID yang baru diambil
                'photo_ktp' => $imageName,
                'no_bill' => $nobill,
                'admin_cashier_id' => $admin_cashier_id,
            ]);
            return response()->json(['message' => 'Promo claimed successfully.'], 200);
        } else {
            return response()->json(['message' => 'Promo claimed failed!'], 400);
        }
    }
    public function forgotPassword(Request $request)
    {
        //dd($request->all());
        $email = $request->email;
        $customers = DB::connection('mysql_secondary')->table('costumers')
            ->where('email', $email)
            ->orWhere('telepon', $email)
            ->first();
        $idCustomer = $customers->id;
        $phoneNumber = $customers->telepon;
        $newPassword = rand(100000, 999999);

        $data_update_costumers = [
            'password2' => md5($newPassword),
            'android_password' => md5($newPassword),
            'hint' => $newPassword,
        ];
        DB::connection('mysql_secondary')->table('costumers')->where('id', $idCustomer)->update($data_update_costumers);
        // $param = new Request();
        // $param->input('email', $email);
        // $param->input('target_number', $phoneNumber);
        // $param->input('new_password', $newPassword);

        $newRequest = new Request([
            'email' => $email,
            'real_email' => $customers->email,
            'target_number' => $phoneNumber,
            'new_password' => $newPassword,
        ]);

        $result["data_send_email"] = $this->sendEmailFromJustusKu($newRequest);
        //$result["data_send_wa"] = $this->sendWA($newRequest);
        $result["request_data"] = $request->all();
        $result["message"] = 'The new password is sent to : ' . $customers->email . ', also check the spam folder, maybe the email goes there.';
        $result["result"] = 1;
        return json_encode($result);
    }

    public function getPointTable(Request $request)
    {
        $customerId = $request->customerId;
        $customerUId = $request->customerUId;
        $data = DB::connection('mysql_secondary')->table('point')
            ->select('point.no_bill', 'point.jml_trans', 'point.point', 'point.type as point_type', 'cabangs.id AS cabangs_id', 'cabangs.name AS branch_name', 'costumers.costumers_id', 'costumers.name', 'point.created_at AS point_date')
            ->leftJoin('costumers', 'point.costumer_id', '=', 'costumers.id')
            ->leftJoin('cabangs', 'point.cabang_id', '=', 'cabangs.id')
            ->where('costumers.costumers_id', '=', $customerUId)
            ->orderBy('point.created_at', 'desc')
            ->get();
        $totalRows = $data->count();
        $result['data_point'] = $data;
        $result['data_point_count'] = $totalRows;
        $result["request_data"] = $request->all();
        $result["message"] = 'Load point success';
        $result["result"] = 1;

        return json_encode($result);
    }
    public function getPoint(Request $request)
    {
        //dd($request->all());
        $costumer_id = $request->costumer_id;
        if (!empty($costumer_id)) {
            $sql_total_point_masuk = DB::connection('mysql_secondary')->table('point')
                ->select(DB::raw('SUM(point.point) as total_point'))
                ->leftJoin('costumers', 'point.costumer_id', '=', 'costumers.id')
                ->where('costumers.costumers_id', '=', $costumer_id)
                ->where('point.type', '=', '1')
                ->whereYear('point.created_at', date('Y'))
                ->first();
            $data_total_point_masuk = $sql_total_point_masuk->total_point ?? 0;

            $sql_total_point_keluar = DB::connection('mysql_secondary')->table('point')
                ->select(DB::raw('SUM(point.point) as total_point'))
                ->leftJoin('costumers', 'point.costumer_id', '=', 'costumers.id')
                ->where('costumers.costumers_id', '=', $costumer_id)
                ->where('point.type', '=', '2')
                ->whereYear('point.created_at', date('Y'))
                ->first();
            $data_total_point_keluar = $sql_total_point_keluar->total_point ?? 0;
        }
        $result["request_data"] = $request->all();
        $result["data_total_sisa_point"] = (@$data_total_point_masuk - @$data_total_point_keluar);
        $result["point_berakhir"] = "Point akan berakhir pada 31 Desember 2025";
        return json_encode($result);
    }
    public function updateProfile(Request $request)
    {
        $customer_id = $request->customer_id;
        $exclusive_member   = $request->exclusive_member ?? '';
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                Rule::unique('costumers', 'email')->ignore($customer_id),
            ],
            'mobile_phone' => [
                'required',
                'numeric',
                Rule::unique('costumers', 'telepon')->ignore($customer_id),
            ],
        ], [
            'email.required' => 'Email tidak boleh kosong.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email telah terdaftar.',
            'mobile_phone.required' => 'Telepon tidak boleh kosong.',
            'mobile_phone.numeric' => 'Format telepon tidak valid.',
            'mobile_phone.unique' => 'Telepon telah terdaftar.',

        ]);

        $existsExclusiveMemberGenerate = DB::connection('mysql_secondary')->table('exclusive_member')
            ->where('full_id', $exclusive_member)
            ->exists();

        $existsExclusiveMemberCostumers = DB::connection('mysql_secondary')->table('costumers')
            ->where('exclusive_member', '=', $exclusive_member)
            ->where('id', '!=', $customer_id)
            ->exists();
        /*

*/
        if ($validator->fails()) {
            $valObj = json_decode($validator->errors());
            foreach ($valObj as $key => $val) {
                $validator_message = $val[0];
            }
            $result["request_data"] = $request->all();
            $result["error_messages"] = $validator->errors();
            $result["message"] = $validator_message ?? 'Update Profile Gagal';
            $result["result"] = 0;
        } else if ($exclusive_member != "" && $existsExclusiveMemberGenerate == false) {
            $result["request_data"] = $request->all();
            $result["error_messages"] = "";
            $result["message"] = 'The exclusive member code has not been registered in our system.';
            $result["result"] = 0;
        } else if ($exclusive_member != "" && $existsExclusiveMemberCostumers == true) {
            $result["request_data"] = $request->all();
            $result["error_messages"] = "";
            $result["message"] = 'The exclusive member code has been used by another account.';
            $result["result"] = 0;
        } else {
            $customer_id = $request->customer_id;
            $email = $request->email;
            $nama_lengkap = $request->nama_lengkap;
            $nama_panggilan = $request->nama_panggilan;
            $mobile_phone = $request->mobile_phone;
            $tanggal_lahir = $request->tanggal_lahir;
            $jenis_kelamin = $request->jenis_kelamin;
            $pekerjaan = $request->pekerjaan;

            $data_costumers_update = [
                'name' => $nama_lengkap,
                'nama_panggilan' => $nama_panggilan,
                'email' => $email,
                'telepon' => $mobile_phone,
                'tanggal_lahir' => $tanggal_lahir,
                'jenis_kelamin' => $jenis_kelamin,
                'pekerjaan' => $pekerjaan,
                'exclusive_member' => $exclusive_member,
                // 'password2' => md5($password),
                // 'android_password' => md5($password),
                // 'hint' => $password,
            ];
            DB::connection('mysql_secondary')->table('costumers')->where('id', $customer_id)->update($data_costumers_update);
            $customers = DB::connection('mysql_secondary')->table('costumers')
                ->where('id', $customer_id)
                ->first();
            $result["data_after_update"] = $customers;

            $result["request_data"] = $request->all();
            $result["message"] = 'Update Profile Success';
            $result["result"] = 1;
        }
        return json_encode($result);
    }
    public function updateProfilePassword(Request $request)
    {
        //{customerId: 39546, password: 1, confirmPassword: 1}
        $customer_id = $request->customerId;
        $password = $request->password;
        $data_costumers_update = [
            'password2' => md5($password),
            'android_password' => md5($password),
            'hint' => $password,
        ];
        DB::connection('mysql_secondary')->table('costumers')->where('id', $customer_id)->update($data_costumers_update);
        $result["request_data"] = $request->all();
        $result["message"] = 'Update Password Success';
        $result["result"] = 1;
        return json_encode($result);
    }
    public function getImgSlideBeranda(Request $request)
    {
        $slideBerandaData = DB::connection('mysql_secondary')->table('slide_beranda')
            ->orderBy('id', 'desc')
            ->get();
        foreach ($slideBerandaData as $row) {
            $imgList[] = $row->image;
        }
        $result["data_img_slide_beranda"] = $imgList;
        $result["data_slide_beranda"] = $slideBerandaData;
        $result["data_request"] = $request->all();
        $result["message"] = 'Get Img Slide Beranda Success';
        $result["result"] = 1;
        return json_encode($result);
    }
    public function getNews(Request $request)
    {
        $dataList = DB::connection('mysql_secondary')->table('news')
            ->orderBy('id', 'desc')
            ->get();
        $result["data_list"] = $dataList;
        $result["data_request"] = $request->all();
        $result["message"] = 'Get getNews Success';
        $result["result"] = 1;
        return json_encode($result);
    }
    public function foodNBeverages(Request $request)
    {
        //SELECT * FROM `but_products` ORDER BY `but_products`.`id` DESC;
        //https://justusmember.co.id/api/flutter/food_n_beverages?group=1
        if ($request->group == "1") {
            $dataList = DB::connection('mysql_secondary')->table('but_products')
                ->orderBy('id', 'desc')
                ->get();
            $result["data_list"] = $dataList;
        }
        //SELECT * FROM `sub_products` WHERE but_products_id = '23' ORDER BY `sub_products`.`id` DESC;
        //https://justusmember.co.id/api/flutter/food_n_beverages?group=2&group_id_1=23
        if ($request->group == "2" and isset($request->group_id_1)) {
            $dataList = DB::connection('mysql_secondary')->table('sub_products')
                ->where('but_products_id', $request->group_id_1)
                ->orderBy('id', 'desc')
                ->get();
            $result["data_list"] = $dataList;
        }
        //SELECT * FROM `products` WHERE but_products_id = '23' AND sub_products_id = '11' ORDER BY `products`.`id` DESC;
        //https://justusmember.co.id/api/flutter/food_n_beverages?group=3&group_id_1=23&group_id_2=11
        if ($request->group == "3" and isset($request->group_id_1) and isset($request->group_id_2)) {
            $dataList = DB::connection('mysql_secondary')->table('products')
                ->where('but_products_id', $request->group_id_1)
                ->where('sub_products_id', $request->group_id_2)
                ->orderBy('id', 'desc')
                ->get();
            $result["data_list"] = $dataList;
        }
        $result["data_request"] = $request->all();
        $result["message"] = 'Get foodNBeverages Success';
        $result["result"] = 1;
        return json_encode($result);
    }
    public function storeLocation(Request $request)
    {
        //SELECT * FROM `cabangs` WHERE id != '0' ORDER BY `cabangs`.`id` ASC;
        //https://justusmember.co.id/api/flutter/store_location
        $dataList = DB::connection('mysql_secondary')->table('cabangs')
            ->where('id', '!=', 0)
            ->orderBy('id', 'ASC')
            ->get();
        $result["data_list"] = $dataList;
        $result["data_request"] = $request->all();
        $result["message"] = 'Get storeLocation Success';
        $result["result"] = 1;
        return json_encode($result);
    }
    public function giftInbox(Request $request)
    {
        // Ambil email dari request
        $email = $request->email;
        $tglSekarang = Carbon::today();
        // Inisialisasi query builder
        $query = DB::connection('mysql_secondary')->table('pushnotification_target as pt')
            ->join('pushnotification as pn', 'pn.id', '=', 'pt.id_pushnotification')
            ->select('pt.*', 'pn.title', 'pn.body', 'pn.photo')
            ->where('pt.program_promo_claimed', '0')
            ->where(function($query) {
                $query->whereDate('pt.program_promo_berlaku_hingga', '>=', Carbon::today()) // Tanggal tidak lebih dari hari ini
                      ->orWhereNull('pt.program_promo_berlaku_hingga'); // Atau program_promo_berlaku_hingga adalah NULL
            })
            ->orderByDesc('pt.id');
        // Tambahkan kondisi jika email ada
        if (!empty($email)) {
            $query->where('pt.email_member', $email);
        }
        // Ambil hasil query
        $results = $query->get();
        // Siapkan hasil response
        $result = [
            "data_list" => $results,
            "data_request" => $request->all(),
            "message" => 'Get storeLocation Success',
            "result" => 1,
        ];
        return response()->json($result);
    }
    public function aboutUs(Request $request)
    {
        //https://justusmember.co.id/api/flutter/about_us
        $dataList = DB::connection('mysql_secondary')->table('hubungi')->get();
        $result["data_list"] = $dataList;
        $result["data_request"] = $request->all();
        $result["message"] = 'Get storeLocation Success';
        $result["result"] = 1;
        return json_encode($result);
    }
    public function dailyPushNotif()
    {
        //https://justusmember.co.id/api/flutter/daily_push_notif
        $this->dailyAutoPushNotifInbox();
        // $this->dailyInboxNotifHappyBirthDay();
        // if ($this->isWithinAllowedTime()) {
        //     $this->resetPushNotification();
        // }
    }

    public function dailyPushBirthDay()
    {
        $this->dailyInboxNotifHappyBirthDay();
    }

    public function dailyInboxNotifHappyBirthDay()
    {
        $today = Carbon::now();
        $month = $today->format('m');
        $day = $today->format('d');
        $program_promo_berlaku_hingga = Carbon::now()->addDays(6);

        $customers = DB::connection('mysql_secondary')->table('costumers')
            ->select('name', 'email', 'telepon', 'tanggal_lahir', 'firebase_token_device')
            ->whereMonth('tanggal_lahir', $month)
            ->whereDay('tanggal_lahir', $day)
            ->where('firebase_token_device', '!=', '')
            ->whereNotNull('firebase_token_device')
            ->get()
            ->toArray();

        // Menambahkan devel row
        // $customers[] = (object) [
        //     "name" => "Fahmi Febriandri SR",
        //     "email" => "fahmifeb@yahoo.co.id",
        //     "telepon" => "082117589434",
        //     "tanggal_lahir" => $today->format('Y-m-d'),
        //     "firebase_token_device" => DB::connection('mysql_secondary')->table('costumers')
        //         ->where('email', 'fahmifeb@yahoo.co.id')
        //         ->value('firebase_token_device')
        // ];

        // Mempersiapkan data untuk di-insert ke tabel pushnotification_target
        $pushTargets = [];
        foreach ($customers as $customer) {
            // Ambil bulan dan tanggal dari tanggal_lahir
            $birthMonth = Carbon::parse($customer->tanggal_lahir)->format('m');
            $birthDay = Carbon::parse($customer->tanggal_lahir)->format('d');

            // Cek jika data sudah ada di tabel pushnotification_target
            $exists = DB::connection('mysql_secondary')->table('pushnotification_target')
                ->where('email_member', $customer->email)
                ->where('tanggal_lahir', $customer->tanggal_lahir)
                ->whereYear('created_at', date('Y'))
                ->exists();

            // Hanya tambahkan ke $pushTargets jika belum ada
            if (!$exists && $birthMonth == $month && $birthDay == $day) {
                // Membuat pesan push notification
                $id_pushnotification = DB::connection('mysql_secondary')->table('pushnotification')->insertGetId([
                    'status_send' => '1',
                    'title' => 'Happy Birthday!',
                    'body' => 'Selamat Ulang Tahun, ' . $customer->name . '! Semoga hari Anda menyenangkan.',
                    'photo' => 'photo_birthday_promo.png',
                    'target' => 'custom',
                ]);
                $pushTargets[] = [
                    'email_member' => $customer->email,
                    'token' => $customer->firebase_token_device,
                    'id_pushnotification' => $id_pushnotification,
                    'tanggal_lahir' => $customer->tanggal_lahir,
                    'status_read' => '0',
                    'status_send' => '0',
                    'program_promo_status' => '1',
                    'program_promo_claimed' => '0',
                    'program_promo_berlaku_hingga' => $program_promo_berlaku_hingga,
                ];
            }

            // Lakukan batch insert setiap 1000 data
            if (count($pushTargets) >= 1000) {
                DB::connection('mysql_secondary')->table('pushnotification_target')->insert($pushTargets);
                $pushTargets = []; // Reset array
            }
        }

        // Insert sisa data jika ada
        if (!empty($pushTargets)) {
            DB::connection('mysql_secondary')->table('pushnotification_target')->insert($pushTargets);
        }

        // buat qrcode 14 digit lebih
        DB::connection('mysql_secondary')->table('pushnotification_target')
            ->where(function ($query) {
                $query->where('qrcode', '')
                    ->orWhereNull('qrcode');
            })
            ->update([
                'qrcode' => DB::raw("CONCAT(id, DATE_FORMAT(created_at, '%y%m%d%H%i%s'))")
            ]);
    }
    public function resetPushNotification()
    {
        DB::connection('mysql_secondary')->table('pushnotification_target')
            ->whereDate('program_promo_berlaku_hingga', '=', Carbon::now()->toDateString())
            ->where("program_promo_claimed", "0")
            ->update([
                'status_send' => '1',
                'program_promo_claimed' => '1'
            ]);

        DB::connection('mysql_secondary')->table('pushnotification_target')
            ->whereDate('created_at', '<=', Carbon::now()->subDays(3))
            ->whereNull('program_promo_berlaku_hingga')
            ->where("program_promo_claimed", "0")
            ->update([
                'status_send' => '1',
                'program_promo_claimed' => '1'
            ]);
    }
    private function isWithinAllowedTime()
    {
        $now = Carbon::now();
        $currentHour = $now->hour;
        if ($currentHour == 3) {
            return true;
        }
        if ($currentHour == 5) {
            return true;
        }
        return false;
    }
    function sendPushNotification($targetToken, $title, $body)
    {
        $url = 'https://ymsoft-erp.justusku.co.id/laravel-firebase-app/public/api/send-fcm';
        $data = [
            'title' => $title,
            'body' => $body,
            'target_token' => $targetToken
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $message = curl_errno($ch) ? 'cURL Error: ' . curl_error($ch) : 'Response: ' . $response;
        curl_close($ch);
        return $message;
    }

    public function dailyAutoPushNotifInbox()
    {
        $results = DB::connection('mysql_secondary')->table('pushnotification_target as pt')
            ->join('pushnotification as pn', 'pn.id', '=', 'pt.id_pushnotification')
            ->select('pt.id', 'pt.token', 'pt.created_at', 'pt.tanggal_lahir', 'pn.title', 'pn.body', 'pn.photo')
            ->whereDate('pt.created_at', '=', Carbon::now()->toDateString())
            ->where('pt.token', '!=', '')
            ->where('pt.token', '!=', null)
            ->where('pt.status_send', '=', '0')
            ->where('pt.program_promo_claimed', '=', '0')
            ->orderBy('pt.created_at', 'ASC')
            ->limit(100)
            ->get();
        $message = "";
        foreach ($results as $row) {
            $token = $row->token;
            $title = $row->title;
            $body = $row->body;
            // $token
            $message = $this->sendPushNotification($token, $title, $body);
            //dd($token, $title, $body, $message);
            DB::connection('mysql_secondary')->table('pushnotification_target')
                ->where('id', $row->id)
                ->update(['status_send' => '1']);
        }
    }

    public function singlePushNotif(Request $request)
    {
        // https://justusmember.co.id/api/flutter/single_push_notif

        $title = $request->title;
        $body = $request->body;
        $gambar = $request->gambar;
        $targetString = $request->target;
        $program_promo_status = $request->program_promo; // aktifasi barcode 1 || 0
        $program_promo_berlaku_hingga =  $request->program_promo_berlaku_hingga;
        $photo = "photo_birthday_promo.png";

        if ($targetString != "all") {
            $this->singlePushNotifByTarget($title, $body, $gambar, $targetString, $photo, $program_promo_status, $program_promo_berlaku_hingga);
        } else {
            $this->singlePushNotifByAll($title, $body, $gambar, $targetString, $photo, $program_promo_status, $program_promo_berlaku_hingga);
        }

        $return['status'] = true;
        return $this->returnJsonHeader($return);
    }

    public function singlePushNotifByAll($title, $body, $gambar, $targetString, $photo, $program_promo_status, $program_promo_berlaku_hingga)
    {
        $threeMonthsAgo = Carbon::now()->subMonths(12);
        $now = Carbon::now();

        $customers = DB::connection('mysql_secondary')->table('costumers')
                ->select(
                    'costumers.id',
                    'costumers.name',
                    'costumers.email',
                    'costumers.telepon',
                    'costumers.tanggal_lahir',
                    'costumers.firebase_token_device'
                )
                ->leftJoin('point', 'costumers.id', '=', 'point.costumer_id')
                ->where('costumers.status_aktif', '1')
                ->where('costumers.firebase_token_device', '!=', '')
                ->groupBy('costumers.id', 'costumers.name', 'costumers.email', 'costumers.telepon', 'costumers.tanggal_lahir', 'costumers.firebase_token_device')
                ->get();

        //echo count($customers); die();

        $id_pushnotification = DB::connection('mysql_secondary')->table('pushnotification')->insertGetId([
            'status_send' => '1',
            'title' => $title,
            'body' => $body,
            'photo' => $photo,
            'target' => $targetString,
        ]);

        $pushTargets = [];
        foreach ($customers as $customer) {
            $pushTargets[] = [
                'email_member' => $customer->email,
                'gambar' => $gambar,
                'token' => $customer->firebase_token_device,
                'id_pushnotification' => $id_pushnotification,
                'status_read' => '0',
                'status_send' => '1',
                'program_promo_status' => $program_promo_status,
                'program_promo_claimed' => ($program_promo_status == '1') ? '0' : '1',
                'program_promo_berlaku_hingga' => $program_promo_berlaku_hingga,
            ];
            if (count($pushTargets) >= 500) {
                DB::connection('mysql_secondary')->table('pushnotification_target')->insert($pushTargets);
                $pushTargets = [];
            }
        }
        if (!empty($pushTargets)) {
            DB::connection('mysql_secondary')->table('pushnotification_target')->insert($pushTargets);
        }
        // buat qrcode 14 digit lebih
        DB::connection('mysql_secondary')->table('pushnotification_target')
            ->where(function ($query) {
                $query->where('qrcode', '')
                    ->orWhereNull('qrcode');
            })
            ->update([
                'qrcode' => DB::raw("CONCAT(id, DATE_FORMAT(created_at, '%y%m%d%H%i%s'))")
            ]);
    }
    public function singlePushNotifByTarget($title, $body, $gambar, $targetString, $photo, $program_promo_status, $program_promo_berlaku_hingga)
    {
        $emails = explode(",", $targetString);
        $id_pushnotification = DB::connection('mysql_secondary')->table('pushnotification')->insertGetId([
            'status_send' => '1',
            'title' => $title,
            'body' => $body,
            'photo' => $photo,
            'target' => $targetString,
        ]);
        $pushTargets = [];
        foreach ($emails as $email) {
            $email = trim($email);
            $token = DB::connection('mysql_secondary')->table('costumers')
                ->where('email', $email)
                ->value('firebase_token_device') ?? "";

            $pushTargets[] = [
                'email_member' => $email,
                'gambar' => $gambar,
                'token' => $token,
                'id_pushnotification' => $id_pushnotification,
                'status_read' => '0',
                'status_send' => '0',
                'program_promo_status' => $program_promo_status,
                'program_promo_claimed' => ($program_promo_status == '1') ? '0' : '1',
                'program_promo_berlaku_hingga' => $program_promo_berlaku_hingga,
            ];
            if (count($pushTargets) >= 500) {
                DB::connection('mysql_secondary')->table('pushnotification_target')->insert($pushTargets);
                $pushTargets = [];
            }
        }
        if (!empty($pushTargets)) {
            DB::connection('mysql_secondary')->table('pushnotification_target')->insert($pushTargets);
        }
        DB::connection('mysql_secondary')->table('pushnotification_target')
            ->where(function ($query) {
                $query->where('qrcode', '')
                    ->orWhereNull('qrcode');
            })
            ->update([
                'qrcode' => DB::raw("CONCAT(id, DATE_FORMAT(created_at, '%y%m%d%H%i%s'))")
            ]);
    }
    public function getLatestVersionFromServer(Request $request)
    {
        $platform = $request->query('platform'); // 'android' or 'ios'
        
        if($platform == 'ios') {
            $return['version'] = "10.4.61";
            $return['update_now'] = true;
            return $this->returnJsonHeader($return);
        }else if($platform == 'android') {
            $return['version'] = "10.4.61";
            $return['update_now'] = true;
            return $this->returnJsonHeader($return);
        } else {
            $return['status'] = false;
            $return['message'] = "Platform not supported";
            return $this->returnJsonHeader($return);
        }
    }

    public function dataPopupAlert()
    {
        $popupList = DB::connection('mysql_secondary')->table('popup_alert')->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->get(['title', 'message']);

        return response()->json([
            'status' => true,
            'popups' => $popupList
        ]);
    }

    public function test()
    {
        // $threeMonthsAgo = Carbon::now()->subMonths(6);
        // $now = Carbon::now();

        // $customers = DB::connection('mysql_secondary')->table('costumers')
        //         ->select(
        //             'costumers.id',
        //             'costumers.name',
        //             'costumers.email',
        //             'costumers.telepon',
        //             'costumers.tanggal_lahir',
        //             'costumers.firebase_token_device'
        //         )
        //         ->leftJoin('point', 'costumers.id', '=', 'point.costumer_id')
        //         ->where('costumers.status_aktif', '1')
        //         ->where('costumers.firebase_token_device', '!=', '')
        //         ->groupBy('costumers.id', 'costumers.name', 'costumers.email', 'costumers.telepon', 'costumers.tanggal_lahir', 'costumers.firebase_token_device')
        //         ->get()
        //         ->count();


        // $customer = DB::connection('mysql_secondary')->table('pushnotification_target')
        //         ->select(DB::raw("CASE 
        //         WHEN EXISTS (SELECT 1 FROM `point` WHERE no_bill = '0604.FCL.24.12') 
        //         THEN 'ada' 
        //         ELSE 'tidak ada' 
        //         END AS ket_no_bill"), 'costumers.*')
        //         ->leftjoin('costumers', 'costumers.email', '=', 'pushnotification_target.email_member')
        //         ->where('pushnotification_target.qrcode', '1304696241206133408') // Gunakan tipe data yang sesuai
        //         ->where('pushnotification_target.program_promo_claimed', '0')
        //         ->orderBy('pushnotification_target.id', 'DESC')
        //         ->limit(1)
        //         ->first();
            
        // $query = DB::connection('mysql_secondary')->table('pushnotification_target as pt')
        //     ->leftjoin('pushnotification as pn', 'pn.id', '=', 'pt.id_pushnotification')
        //     ->select('pt.*', 'pn.title', 'pn.body', 'pn.photo')
        //     ->where('pt.program_promo_claimed', '0')
        //     ->where(function($query) {
        //         $query->whereDate('pt.program_promo_berlaku_hingga', '>=', Carbon::today()) // Tanggal tidak lebih dari hari ini
        //               ->orWhereNull('pt.program_promo_berlaku_hingga'); // Atau program_promo_berlaku_hingga adalah NULL
        //     })
        //     ->where('pt.email_member', 'wafaqory16@gmail.com')
        //     ->orderByDesc('pt.id')
        //     ->get();

        // dd($query);
        // $today = Carbon::now();
        // $month = $today->format('m');
        // $day = $today->format('d');
            
        // $customers = DB::connection('mysql_secondary')->table('costumers')
        //     ->select('name', 'email', 'telepon', 'tanggal_lahir', 'firebase_token_device')
        //     ->whereMonth('tanggal_lahir', $month)
        //     ->whereDay('tanggal_lahir', $day)
        //     ->where('firebase_token_device', '!=', '')
        //     ->whereNotNull('firebase_token_device')
        //     ->get()
        //     ->count();

        // $results = DB::connection('mysql_secondary')->table('pushnotification_target as pt')
        //     ->join('pushnotification as pn', 'pn.id', '=', 'pt.id_pushnotification')
        //     ->select('pt.id', 'pt.token', 'pt.created_at', 'pt.tanggal_lahir', 'pn.title', 'pn.body', 'pn.photo')
        //     ->whereDate('pt.created_at', '=', Carbon::now()->toDateString())
        //     ->where('pt.token', '!=', '')
        //     ->where('pt.token', '!=', null)
        //     ->where('pt.status_send', '=', '0')
        //     ->where('pt.program_promo_claimed', '=', '0')
        //     ->orderBy('pt.created_at', 'ASC')
        //     ->limit(100)
        //     ->get();

        // $tglSekarang = Carbon::today();
        // // Inisialisasi query builder
        // $query = DB::connection('mysql_secondary')->table('pushnotification_target as pt')
        //     ->join('pushnotification as pn', 'pn.id', '=', 'pt.id_pushnotification')
        //     ->select('pt.*', 'pn.title', 'pn.body', 'pn.photo')
        //     ->where('pt.program_promo_claimed', '0')
        //     ->where(function($query) {
        //         $query->whereDate('pt.program_promo_berlaku_hingga', '<=', Carbon::today()) // Tanggal tidak lebih dari hari ini
        //               ->orWhereNull('pt.program_promo_berlaku_hingga'); // Atau program_promo_berlaku_hingga adalah NULL
        //     })
        //     ->where('pt.email_member', 'fitrianatta09@gmail.com')
        //     ->orderByDesc('pt.id')
        //     ->get();



        // $results = DB::connection('mysql_secondary')->table('pushnotification_target as pt')
        //     ->join('pushnotification as pn', 'pn.id', '=', 'pt.id_pushnotification')
        //     ->select('pt.id', 'pt.token', 'pt.created_at', 'pt.tanggal_lahir', 'pn.title', 'pn.body', 'pn.photo')
        //     ->where('pt.id', '=', '1')
        //     ->where('pt.token', '!=', '')
        //     ->where('pt.token', '!=', null)
        //     ->where('pt.status_send', '=', '0')
        //     ->where('pt.program_promo_claimed', '=', '0')
        //     ->orderBy('pt.created_at', 'ASC')
        //     ->limit(100)
        //     ->get();
        // $message = "";

        // foreach ($results as $row) {
        //     $token = $row->token;
        //     $title = $row->title;
        //     $body = $row->body;
        //     // $token
        //     $message = $this->sendPushNotification($token, $title, $body);
        // }

        // $program_promo_berlaku_hingga = Carbon::now()->addDays(6);

        // $email = 'sp.dhartia@gmail.com';

        // $result = DB::connection('mysql_secondary')->table('pushnotification_target as pt')
        //     ->join('pushnotification as p', 'pt.id_pushnotification', '=', 'p.id')
        //     ->select('pt.id as ptid', 'pt.*', 'p.*')
        //     ->where('pt.email_member', $email)
        //     ->where('pt.status_read', '0')
        //     ->where(function($query) {
        //         $query->whereDate('pt.program_promo_berlaku_hingga', '>=', Carbon::today()) // Tanggal tidak lebih dari hari ini
        //               ->orWhereNull('pt.program_promo_berlaku_hingga'); // Atau program_promo_berlaku_hingga adalah NULL
        //     })
        //     ->orderBy('pt.id', 'ASC')
        //     ->first();

        // dd($result);

        // $message = $this->sendPushNotification('eB68fdrLQ0WgQ1iaTUORwu:APA91bEuc3xUvAPRiIxg2mCLFBOkZoDkbtUJlQlSdNgdkJpHSXP0A6zaYLkxCBP7mV5OjDxVCb6vx_5JxR9hBxW5mcOR4daJjXi04aL9nNgATR5EskUIPnk', 'coba', 'coba');

        // $this->dailyInboxNotifHappyBirthDay();

        $token = 'eW5_1yh6SXaDGTtZ0wWv8r:APA91bFNRN39-7sW432JGKPFj3quchtm2e5uG0gCCCIELwqwQ5migIjs-XovnX-2rNv0Pm3STTYvQDeRrlRyfZpzqhKblSu5QVT86ymH1rlVMJmjczHwbbM';
        $title = 'test test test';
        $body = 'test';

        $this->sendPushNotification2($token, $title, $body);
    }

    function sendPushNotification2($targetToken, $title, $body)
    {
        $url = 'https://ymsoft-erp.justusku.co.id/laravel-firebase-app/public/api/test';
        $data = [
            'title' => $title,
            'body' => $body,
            'target_token' => $targetToken
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        $message = curl_errno($ch) ? 'cURL Error: ' . curl_error($ch) : 'Response: ' . $response;
        curl_close($ch);
        return $message;
    }
}
