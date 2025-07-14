<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\SurveyPelanggan;

class SurveyPelangganController extends Controller
{
    public $data_question = [];

    public function __construct()
    {
        $this->setDataForm();
    }
    public function index()
    {
        $data['input_question'] = $this->data_question;
        return view('quisioner.survey_pelanggan', compact('data'));
    }
    public function save(Request $request)
    {
        $survey = new SurveyPelanggan();
        //$survey->email = $request['email'];
        $survey->outlet_name = $request['outlet_name'][0];
        $survey->name = $request['name'];
        $survey->phone = $request['phone'];
        $survey->visit_date = $request['visit_date'];
        $survey->reference = json_encode($request['reference']);
        $survey->rate = $request['rate'][0];
        $survey->destination_choice = json_encode($request['destination_choice']);
        $survey->experience = $request['experience'][0];
        $survey->friends = json_encode($request['friends']);
        $survey->program_info = json_encode($request['program_info']);
        $survey->suggestions = $request['suggestions'];
        $survey->save();
        return redirect()->route('survey_pelanggan.thankyou')->with('success', 'Survey berhasil disimpan!');
    }
    public function thankyou(){
        return view('quisioner.thankyou');
    }
    public function setDataForm()
    {
        /*
            [
                "label" => "Email (masukan format email yang benar)",
                "name" => "email",
                "type" => "email",
                "attr" => [
                    "required" => "required",
                ],
            ],
        */
        $this->data_question = [
            [
                "label" => "Pilih Outlet Yang Anda Kunjungi",
                "name" => "outlet_name",
                "type" => "radio",
                "attr" => [
                    "required" => "required",
                ],
                "options" => [
                    "Justus Steakhouse Miko Mall",
                    "Justus Steakhouse Festival Citylink",
                    "Justus Steakhouse Jl Jawa",
                    "Justus Steakhouse Metro Indah Mall",
                    "Justus Steakhouse Cimanuk",
                    "Justus Steakhouse Cihampelas Walk",
                    "Justus Steakhouse Paskal",
                    "Justus Steakhouse Paris van Java",
                    "Justus Steakhouse Dago",
                    "Justus Steakhouse Buah Batu",
                    "Justus Steakhouse Cipete",
                    "Justus Steakhouse Alam Sutera",
                    "Justus Steakhouse Bintaro",
                    "Justus Burger & Steak BIP",
                    "Justus Burger & Steak FCL",
                    "Justus Burger & Steak BEC",
                    "Justus Burger & Steak BTC",
                    "Asian Grill Express BIP",
                    "Asian Grill Express BEC",
                    "Tempayan Indonesia Bistro",
                    "Hawker Sei Metro Indah Mall"
                ],
            ],
            [
                "label" => "Nama",
                "name" => "name",
                "type" => "text",
                "attr" => [
                    "required" => "required",
                ],
            ],
            [
                "label" => "No Hp/Whatsapp",
                "name" => "phone",
                "type" => "text",
                "attr" => [
                    "required" => "required",
                ],
            ],
            [
                "label" => "Tanggal Kunjungan",
                "name" => "visit_date",
                "type" => "date",
                "attr" => [
                    "required" => "required",
                ],
            ],
            [
                "label" => "Dari mana Anda mengetahui Justus Group?",
                "name" => "reference",
                "type" => "checkbox",
                "attr" => [
                    "required" => "required",
                ],
                "options" => [
                    "Instagram Justus Steakhouse",
                    "KOL / Influencer",
                    "Trip Advisor",
                    "Google",
                    "Radio",
                    "Media Promosi Cetak",
                    "Teman / Keluarga",
                    "other",
                ],
            ],
            [
                "label" => "Menurut Anda bagaimana penilaian harga produk di Justus Group?",
                "name" => "rate",
                "type" => "radio",
                "attr" => [
                    "required" => "required",
                ],
                "options" => [
                    "Sangat Mahal",
                    "Mahal",
                    "Terjangkau",
                ],
            ],
            [
                "label" => "Apa yang membuat Anda memilih Justus Group sebagai destinasi kuliner Anda? (Pilih semua yang berlaku)",
                "name" => "destination_choice",
                "type" => "checkbox",
                "attr" => [
                    "required" => "required",
                ],
                "options" => [
                    "Kualitas Produk",
                    "Kualitas Pelayanan",
                    "Harga yang Terjangkau",
                    "Suasana Tempat",
                    "Lokasi yang Strategis",
                    "Promo",
                    "Rekomendasi dan Teman / Keluarga",
                    "other",
                ],
            ],
            [
                "label" => "Bagaimana penilaian Anda terhadap pengalaman anda secara keseluruhan di Justus Group?",
                "name" => "experience",
                "type" => "radio",
                "attr" => [
                    "required" => "required",
                ],
                "options" => [
                    "Sangat Puas",
                    "Puas",
                    "Cukup Puas",
                    "Tidak Puas",
                ],
            ],
            [
                "label" => "Dengan siapa biasanya Anda datang untuk menikmati hidangan di Justus Group?",
                "name" => "friends",
                "type" => "checkbox",
                "attr" => [
                    "required" => "required",
                ],
                "options" => [
                    "Teman",
                    "Pasangan",
                    "Keluarga",
                    "Rekan Kerja",
                ],
            ],
            [
                "label" => "Darimanakah informasi yang anda dapat jika terdapat program promo di Justus Group",
                "name" => "program_info",
                "type" => "checkbox",
                "attr" => [
                    "required" => "required",
                ],
                "options" => [
                    "Instagram",
                    "Tiktok",
                    "Influencer",
                    "Baligho",
                    "other",
                ],
            ],
            [
                "label" => "Apakah Anda memiliki saran atau masukan lain untuk kami guna meningkatkan pengalaman Anda di Justus Group?",
                "name" => "suggestions",
                "type" => "textarea",
                "attr" => [
                    "required" => "required",
                ],
            ],
        ];
    }
    public function output()
    {
        $data['survey'] = SurveyPelanggan::orderBy("id", "desc")->get();
        //dd($data);
        return view('quisioner.survey_pelanggan_output', compact('data'));
    }
    public function apiLoadData(Request $request)
    {
        $data['survey'] = SurveyPelanggan::orderBy("id", "desc")->get();
        return $this->returnJsonHeader($data);
    }
}