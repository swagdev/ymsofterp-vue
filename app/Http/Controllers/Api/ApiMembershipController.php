<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models_secondary\Costumers;
use App\Models_secondary\MemberLastPointTransaction;
use App\Models_secondary\MemberCabangs;
use App\Models_secondary\MemberPoints;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ApiMembershipController extends Controller
{
    public function checkMembership(Request $request)
    {
        $phonenumber = $request->phonenumber;
        $data_costumers = Costumers::where('telepon', $phonenumber)->get();
        return $this->returnJsonHeader($data_costumers);
    }
    public function getMemberOutlet(Request $request)
    {
        $MemberCabangs = MemberCabangs::orderBy("name", "ASC")->get();
        return response()->json($MemberCabangs);
    }
    public function reportMember(Request $request)
    {
        $allData = $request->all(); // Mengambil semua data yang dikirim

        $findEmail = $request->find_email ?? null;
        $outletId = $request->outlet_id ?? null;
        
        $start_date = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $end_date = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now();

        $query = MemberPoints::join('costumers', 'costumers.id', '=', 'point.costumer_id')
            ->select("point.*")
            ->whereBetween('point.created_at', [$start_date, $end_date]);
        if ($findEmail) {
            $query->where('costumers.email', $findEmail);
        }
        if ($outletId) {
            $query->where('point.cabang_id', $outletId);
        }
        $query->where('point.cabang_id', '!=', '0');
        $query->where('point.type', '=', '1');
        $query->orderBy('point.id', 'DESC');
        $pointTransactionMembers = $query->get()
            ->map(function ($transactions) use ($start_date, $end_date) {
                return [
                    'costumer_id' => $transactions->costumer_id,
                    'costumer_name' => $transactions->customer->name,
                    'costumer_tanggal_lahir' => $transactions->customer->tanggal_lahir,
                    'costumer_telepon' => $transactions->customer->telepon,
                    'costumer_email' => $transactions->customer->email,
                    'costumer_alamat' => $transactions->customer->alamat,
                    'costumer_tanggal_register' => $transactions->customer->tanggal_register,

                    'total_count_trans' => $transactions->getTotalRowsThisYear(),
                    'total_count_trans_by_filter' => $transactions->getTotalRowsFilter($start_date, $end_date),

                    'total_trans' => $transactions->getTotalTransactionThisYear(),
                    'total_trans_by_filter' => $transactions->getTotalTransactionFilter($start_date, $end_date),

                    'last_trans_in' => $transactions->getLastTransactionInThisYear()->outlet->name ?? null,
                    'last_trans_in_by_filter' => $transactions->outlet->name,

                    'last_trans_date' => $transactions->created_at->format('Y-m-d H:i:s'),
                    'last_trans' => $transactions->getLastTransactionThisYear()->jml_trans ?? null,
                    'last_trans_by_filter' => $transactions->jml_trans,

                    'last_trans_in_id' => $transactions->outlet->id,
                    'poin_in' => $transactions->getTotalPointIn(),
                    'poin_out' => $transactions->getTotalPointOut(),
                    'poin_balance' => ($transactions->getTotalPointIn() - $transactions->getTotalPointOut()),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $pointTransactionMembers
        ]);
    }
    public function getTransPoinMember($member_id = null)
    {
        $year = date('Y');
        $transPoint = MemberPoints::where("costumer_id", $member_id)
            ->where("cabang_id", "!=", "0")
            ->whereYear('created_at', $year)
            ->orderBy("id", "DESC")
            ->get();
        foreach ($transPoint as $point) {
            $point->outlet_name = $point->outlet->name;
        }
        return $this->returnJsonHeader($transPoint);
    }
    public function getHistorySendGift(Request $request)
    {
        $filterByTanggal = $request->filter_by_tanggal; // tanggal_dibuat tanggal_claim
        $programPromoClaimed = $request->program_promo_claimed;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $query = DB::table('pushnotification_target as pnt')
            ->join('pushnotification as pn', 'pn.id', '=', 'pnt.id_pushnotification')
            ->leftJoin('claim_program_promo as cpp', 'cpp.id_pushnotification_target', '=', 'pnt.id')
            ->join('admin_cashier as ac', 'ac.id', '=', 'cpp.admin_cashier_id')
            ->select('pnt.created_at', 'pnt.email_member', 'pnt.qrcode', 'pnt.program_promo_claimed', 'cpp.no_bill', 'cpp.created_at as tanggal_claim', 'ac.nama_cabang as tempat_claim', 'pn.title', 'pn.body');
        if (isset($programPromoClaimed)) {
            $query->where('pnt.program_promo_claimed', $programPromoClaimed);
            if ($programPromoClaimed == "1") {
                $query->whereNotNull('cpp.created_at');
            }
        }
        if (isset($startDate) && isset($endDate)) {
            $endDate = Carbon::parse($endDate)->addDay();
            if ($programPromoClaimed == "0") {
                $query->whereBetween('pnt.created_at', [$startDate, $endDate]);
            } else if ($programPromoClaimed == "1") {
                if ($filterByTanggal == "tanggal_dibuat") {
                    $query->whereBetween('pnt.created_at', [$startDate, $endDate]);
                } else if ($filterByTanggal == "tanggal_claim") {
                    $query->whereBetween('cpp.created_at', [$startDate, $endDate]);
                }
            }
        }
        $results = $query->orderByDesc('pnt.created_at')->get();
        return response()->json($results);
    }
}
