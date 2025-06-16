<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\ActivityLog;
use App\Models\Outlet;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        // $data = DB::connection('mysql_secondary')->table('costumers')->select('costumers.*')->limit(5)->get();
        // dd($request);

        $outlets = DB::connection('mysql_secondary')->table('cabangs')->orderBy('nama_singkat', 'asc')->get();

        $year = Carbon::now()->year;
        $tanggalAwal = Carbon::parse($request->tanggal_awal)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $query = DB::connection('mysql_secondary')
        ->table('costumers')
        ->leftJoin('point', 'point.costumer_id', '=', 'costumers.id')
        ->where('point.cabang_id', '=', $request->id_outlet)
        ->select(
            'costumers.id', 'costumers.name', 'costumers.email', 'costumers.telepon',
            DB::connection('mysql_secondary')->raw("SUM(CASE WHEN point.type = 1 AND YEAR(point.created_at) = $year THEN point.point ELSE 0 END) as total_point_tahun_ini"),
            DB::connection('mysql_secondary')->raw("SUM(CASE WHEN point.type = 2 AND YEAR(point.created_at) = $year THEN point.jml_trans ELSE 0 END) as total_redeem_point_tahun_ini"),
            DB::connection('mysql_secondary')->raw('MAX(point.created_at) as last_transaksi_date'),
            DB::connection('mysql_secondary')->raw('MAX(point.id) as point_id'),
            DB::connection('mysql_secondary')->raw('COUNT(point.id) as total_transaksi_semua'),
            DB::connection('mysql_secondary')->raw('SUM(point.jml_trans) as total_jml_trans'),
            DB::connection('mysql_secondary')->raw("SUM(CASE WHEN YEAR(point.created_at) = $year THEN 1 ELSE 0 END) as total_transaksi_tahunan"),
            DB::connection('mysql_secondary')->raw("SUM(CASE WHEN YEAR(point.created_at) = $year THEN point.jml_trans ELSE 0 END) as total_year_jum_trans"))
        ->groupBy('costumers.id', 'costumers.name');

        if (empty($request->outlet_id)) {
            $query->whereBetween('point.created_at', [$tanggalAwal, $tanggalAkhir]);
            
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('costumers.name', 'like', "%$search%")
                  ->orWhere('costumers.telepon', 'like', "%$search%")
                  ->orWhere('costumers.email', 'like', "%$search%")
                ;
            });
        }

        $customers = $query->orderBy('point_id', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Member/Index', [
            'customers' => $customers,
            'outlets' => $outlets,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function view_detail(Request $request)
    {
        $tanggalAwal = Carbon::createFromFormat('Y-m-d', $request->tgl_awal)->startOfDay();
        $tanggalAkhir = Carbon::createFromFormat('Y-m-d', $request->tgl_akhir)->endOfDay();

        $points = DB::connection('mysql_secondary')->table('point')
                ->leftJoin('cabangs', 'cabangs.id', '=', 'point.cabang_id')
                ->select('point.*', 'cabangs.nama_singkat as nama_outlet')
                ->where('point.costumer_id', $request->input('data.id'))
                ->whereBetween('point.created_at', [$tanggalAwal, $tanggalAkhir])
                ->get();

        return response()->json($points);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:customers,code,' . $id,
            'name' => 'required|string|max:100',
            'type' => 'required|in:branch,customer',
            'region' => 'required|string|max:20',
            'status' => 'required|in:active,inactive',
            'gender' => 'nullable|string|max:10',
            'place_birth' => 'nullable|string|max:100',
            'date_birth' => 'nullable|date',
            'domicile' => 'nullable|string|max:255',
            'domicile_ktp' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:20',
            'blood_type' => 'nullable|string|max:20',
            'golongan_darah' => 'nullable|string|max:5',
            'wa_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'name_emergency_contact' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:50',
            'emergency_contact_relationship' => 'nullable|string|max:50',
            'marital_status' => 'nullable|string|max:20',
            'number_of_children' => 'nullable|integer|min:0',
            'spouse' => 'nullable|string|max:100',
            'wa_spouse' => 'nullable|string|max:20',
            'id_card' => 'nullable|string|max:255',
            'upload_id_card' => 'nullable|upload_id_card|mimes:jpeg,png,jpg|max:1024', // Max 1MB
            'family_card_number' => 'nullable|string|max:50',
            'bca_account_number' => 'nullable|string|max:50',
            'bca_account_name' => 'nullable|string|max:100',
            'npwp_number' => 'nullable|string|max:50',
            'bpjs_health_number' => 'nullable|string|max:50',
            'bpjs_employment_number' => 'nullable|string|max:50',
            'last_education' => 'nullable|string|max:50',
            'name_school_college' => 'nullable|string|max:100',
            'school_college_major' => 'nullable|string|max:100',
            'work_start_date' => 'nullable|date',
            'position' => 'nullable|string|max:100',
        ]);
        $customer = DB::table('customers')->where('id', $id)->first();
        $oldData = $customer;

        if ($request->hasFile('upload_id_card')) {

            // Hapus gambar lama jika ada
            if ($customer->upload_id_card && Storage::disk('public')->exists($customer->upload_id_card)) {
                Storage::disk('public')->delete($customer->upload_id_card);
            }

            // Simpan gambar baru di storage/app/public/employee
            $path = $request->file('upload_id_card')->store('employee', 'public');

            // Simpan path saja (jangan prefix ulang 'employee/')
            $customer->upload_id_card = $path;
        }

        DB::table('customers')->where('id', $id)->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'region' => $validated['region'],
            'status' => $validated['status'],
            'gender' => $validated['gender'] ?? null,
            'place_birth' => $validated['place_birth'] ?? null,
            'date_birth' => isset($validated['date_birth']) ? date('Y-m-d', strtotime($validated['date_birth'])) : null,
            'domicile' => $validated['domicile'] ?? null,
            'domicile_ktp' => $validated['domicile'] ?? null,
            'religion' => $validated['religion'] ?? null,
            'blood_type' => $validated['blood_type'] ?? null,
            'wa_number' => $validated['wa_number'] ?? null,
            'email' => $validated['email'] ?? null,
            'name_emergency_contact' => $validated['name_emergency_contact'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'emergency_contact_relationship' => $validated['emergency_contact_relationship'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'number_of_children' => $validated['number_of_children'] ?? null,
            'spouse' => $validated['spouse'] ?? null,
            'wa_spouse' => $validated['wa_spouse'] ?? null,
            'id_card' => $validated['id_card'],
            'family_card_number' => $validated['family_card_number'] ?? null,
            'bca_account_number' => $validated['bca_account_number'] ?? null,
            'bca_account_name' => $validated['bca_account_name'] ?? null,
            'npwp_number' => $validated['npwp_number'] ?? null,
            'bpjs_health_number' => $validated['bpjs_health_number'] ?? null,
            'bpjs_employment_number' => $validated['bpjs_employment_number'] ?? null,
            'last_education' => $validated['last_education'] ?? null,
            'name_school_college' => $validated['name_school_college'] ?? null,
            'school_college_major' => $validated['school_college_major'] ?? null,
            'work_start_date' => isset($validated['work_start_date']) ? date('Y-m-d', strtotime($validated['work_start_date'])) : null,
            'position' => $validated['position'] ?? null,
            // 'previewImage' => $previewImagePath,
            // 'previewImageKK' => $previewImageKKPath,
            'updated_at' => now(),
        ]);

        $newData = DB::table('customers')->where('id', $id)->first();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'update',
            'module' => 'customers',
            'description' => 'Mengupdate customer: ' . $newData->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($newData),
        ]);
        return redirect()->route('member.index')->with('success', 'Member   berhasil diupdate!');
    }

    public function destroy($id)
    {
        $customer = DB::table('customers')->where('id', $id)->first();
        $oldData = $customer;
        DB::table('customers')->where('id', $id)->update([
            'status' => 'inactive',
            'updated_at' => now(),
        ]);
        $newData = DB::table('customers')->where('id', $id)->first();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'delete',
            'module' => 'customers',
            'description' => 'Menonaktifkan customer: ' . $customer->name,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($newData),
        ]);
        return redirect()->route('customers.index')->with('success', 'Customer berhasil dinonaktifkan!');
    }

    public function toggleStatus($id, Request $request)
    {
        $customer = DB::table('customers')->where('id', $id)->first();
        $oldData = $customer;
        DB::table('customers')->where('id', $id)->update([
            'status' => $request->status,
            'updated_at' => now(),
        ]);
        $newData = DB::table('customers')->where('id', $id)->first();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'status_toggle',
            'module' => 'customers',
            'description' => 'Mengubah status customer: ' . $customer->name . ' menjadi ' . $request->status,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($newData),
        ]);
        return response()->json(['success' => true]);
    }
} 