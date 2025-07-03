<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Jabatan;
use App\Models\Divisi;
use App\Models\Outlet;
use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class CrmController extends Controller
{
    public function index(Request $request)
    {
        $jabatans = Jabatan::where('status', 'A')->orderBy('nama_jabatan', 'asc')->get();
        $divisis = Divisi::whereNull('deleted_at')->orderBy('nama_divisi', 'asc')->get();
        $outlets = Outlet::where('status', 'A')->orderBy('nama_outlet', 'asc')->get();

        $bulan_tahun = $request->bulan_tahun;
        if (Str::contains($bulan_tahun, '-')) {
            [$tahun, $bulan] = explode('-', $bulan_tahun);
        } else {
            $tahun = now()->format('Y');
            $bulan = now()->format('m');
        }

        $tglNow = Carbon::now();
        $tahun_saat_ini = Carbon::now()->year;
        $bulan_saat_ini = Carbon::now()->month;
        $start_date = Carbon::create($tahun, $bulan - 1, 26)->startOfDay();
        $end_date = Carbon::create($tahun, $bulan, 25)->endOfDay();
        $tahun_lalu = now()->subYear()->year;

        $tahunIni = $tglNow->year;
        if ($tglNow->month < 3) {
            // Jika bulan sekarang kurang dari Maret, ambil dari Maret tahun lalu ke Februari tahun ini
            $startYear = Carbon::create($tahunIni - 1, 3, 1)->format('Y-m-d');; // 1 Maret tahun lalu
            $endYear = Carbon::create($tahunIni, 2, 1)->endOfMonth()->format('Y-m-d');; // Akhir Februari tahun ini
        } else {
            // Jika bulan sekarang Maret atau lebih, ambil dari Maret tahun ini ke Februari tahun depan
            $startYear = Carbon::create($tahunIni, 3, 1)->format('Y-m-d');; // 1 Maret tahun ini
            $endYear = Carbon::create($tahunIni + 1, 2, 1)->endOfMonth()->format('Y-m-d');; // Akhir Februari tahun depan
        }

        // if (!empty($request->id_divisi)) {
            $user = Auth::user();
            $divisi_id = $request->id_divisi ?? '';
            $outlet_id = $request->id_outlet ?? '';

            $d = [];

            $d['getTotalTransaksiInMounth'] = DB::connection('mysql_secondary')->table('point')
                    ->where('type', '1')
                    ->where('cabang_id', '!=', '0')
                    ->whereYear('created_at', $tahun_saat_ini)
                    ->whereMonth('created_at', $bulan_saat_ini)
                    ->sum('jml_trans');

            $d['getTotalNewMemberMounth'] = DB::connection('mysql_secondary')->table('costumers')
                    ->whereYear('created_at', $tahun_saat_ini)
                    ->whereMonth('created_at', $bulan_saat_ini)
                    ->count();

            $d['getTotalPointInMounth'] = DB::connection('mysql_secondary')->table('point')
                    ->where('type', '1')
                    ->where('cabang_id', '!=', '0')
                    ->whereYear('created_at', $tahun_saat_ini)
                    ->whereMonth('created_at', $bulan_saat_ini)
                    ->sum('point');

            $d['getTotalRedeemInMounth'] = DB::connection('mysql_secondary')->table('point')
                    ->where('type', '2')
                    ->where('cabang_id', '!=', '0')
                    ->whereYear('created_at', $tahun_saat_ini)
                    ->whereMonth('created_at', $bulan_saat_ini)
                    ->sum('point');

            $d['getTotalRedeemInMounth'] = DB::connection('mysql_secondary')->table('point')
                    ->where('type', '2')
                    ->where('cabang_id', '!=', '0')
                    ->whereYear('created_at', $tahun_saat_ini)
                    ->whereMonth('created_at', $bulan_saat_ini)
                    ->sum('point');
            

            $d['getTotalMember'] = DB::connection('mysql_secondary')->table('costumers')
                                    ->count();

            $d['getTotalMemberBandung'] = DB::connection('mysql_secondary')->query()
                                    ->fromSub(
                                        DB::connection('mysql_secondary')->table('costumers')
                                            ->leftJoin('point', 'point.costumer_id', '=', 'costumers.id')
                                            ->whereNull('point.id')
                                            ->select('costumers.id')
                                            ->union(
                                                DB::connection('mysql_secondary')->table('costumers')
                                                    ->join('point', 'point.costumer_id', '=', 'costumers.id')
                                                    ->join('cabangs', 'cabangs.id', '=', 'point.cabang_id')
                                                    ->whereIn('point.id', function ($q) {
                                                        $q->select(DB::connection('mysql_secondary')->raw('MIN(id)'))
                                                        ->from('point')
                                                        ->groupBy('costumer_id');
                                                    })
                                                    ->whereNotIn('cabangs.kode_cabang', ['SH011', 'SH012', 'SH013', 'SH014', 'SH015'])
                                                    ->select('costumers.id')
                                            ),
                                        'full_set'
                                    )
                                    ->count();

            $d['getTotalMemberJakarta'] = DB::connection('mysql_secondary')->query()
                                            ->fromSub(
                                                DB::connection('mysql_secondary')->table('costumers')
                                                    ->join('point', 'point.costumer_id', '=', 'costumers.id')
                                                    ->join('cabangs', 'cabangs.id', '=', 'point.cabang_id')
                                                    ->whereIn('point.id', function ($q) {
                                                        $q->select(DB::connection('mysql_secondary')->raw('MIN(p2.id)'))
                                                        ->from('point as p2')
                                                        ->groupBy('p2.costumer_id');
                                                    })
                                                    ->whereIn('cabangs.kode_cabang', ['SH011', 'SH012', 'SH013', 'SH014', 'SH015'])
                                                    ->select('costumers.id'),
                                                'first_joined'
                                            )
                                            ->count();

            $d['getTotalActiveMember'] = DB::connection('mysql_secondary')->table('costumers')
                                            ->where('tanggal_aktif', '>', now())
                                            ->count();

            $d['getTotalActiveMemberTransactions'] =  DB::connection('mysql_secondary')->table('costumers')
                                            ->join('point', 'costumers.id', '=', 'point.costumer_id')
                                            ->where('costumers.tanggal_aktif', '>', now())
                                            ->where('point.created_at', '>=', now()->subMonth())
                                            ->distinct()
                                            ->count('costumers.id');

            
            $newMonthlyDataMember = DB::connection('mysql_secondary')->table('costumers')
                                            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                                            ->whereYear('created_at', $tahun_saat_ini)
                                            ->groupBy(DB::connection('mysql_secondary')->raw('MONTH(created_at)'))
                                            ->orderBy('month')
                                            ->get();

            $d['getNewMemberGrafik'] = collect(range(1, $bulan_saat_ini))->map(function ($m) use ($newMonthlyDataMember) {
                                            $found = $newMonthlyDataMember->firstWhere('month', $m);
                                            return [
                                                'month' => Carbon::create()->month($m)->format('M'),
                                                'total' => $found ? $found->total : 0,
                                            ];
                                        });

            $lastMonthDataMember = DB::connection('mysql_secondary')->table('costumers')
                                            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                                            ->whereYear('created_at', $tahun_lalu)
                                            ->groupBy(DB::connection('mysql_secondary')->raw('MONTH(created_at)'))
                                            ->orderBy('month')
                                            ->get();

            $d['getLastYearMemberGrafik'] = collect(range(1, 12))->map(function ($m) use ($lastMonthDataMember) {
                                            $found = $lastMonthDataMember->firstWhere('month', $m);
                                            return [
                                                'month' => Carbon::create()->month($m)->format('M'),
                                                'total' => $found ? $found->total : 0,
                                            ];
                                        });
                       
            $monthTransaksiGrafik = DB::connection('mysql_secondary')->table('point')
                                            ->selectRaw("MONTH(created_at) as month,
                                                        SUM(CASE WHEN type = 1 THEN jml_trans ELSE 0 END) as transaksi,
                                                        SUM(CASE WHEN type = 1 THEN point ELSE 0 END) as topup,
                                                        SUM(CASE WHEN type = 2 THEN point ELSE 0 END) as redeem")
                                            ->whereYear('created_at', $tahun_saat_ini)
                                            ->groupByRaw('MONTH(created_at)')
                                            ->orderByRaw('MONTH(created_at)')
                                            ->get();

                                        // normalisasi ke 12 bulan
             $d['monthTransaksiGrafik'] = collect(range(1, $bulan_saat_ini))->map(function ($bulan) use ($monthTransaksiGrafik) {
                                            $found = $monthTransaksiGrafik->firstWhere('month', $bulan);
                                            return [
                                                'month' => \Carbon\Carbon::create()->month($bulan)->format('M'),
                                                'transaksi' => $found->transaksi ?? 0,
                                                'topup' => $found->topup ?? 0,
                                                'redeem' => $found->redeem ?? 0,
                                            ];
                                        });

            $lastYearTransaksiGrafik = DB::connection('mysql_secondary')->table('point')
                                            ->selectRaw("MONTH(created_at) as month,
                                                        SUM(CASE WHEN type = 1 THEN jml_trans ELSE 0 END) as transaksi,
                                                        SUM(CASE WHEN type = 1 THEN point ELSE 0 END) as topup,
                                                        SUM(CASE WHEN type = 2 THEN point ELSE 0 END) as redeem")
                                            ->whereYear('created_at', $tahun_lalu)
                                            ->groupByRaw('MONTH(created_at)')
                                            ->orderByRaw('MONTH(created_at)')
                                            ->get();

                                        // normalisasi ke 12 bulan
            $d['lastYearTransaksiGrafik'] = collect(range(1, 12))->map(function ($bulan) use ($lastYearTransaksiGrafik) {
                                            $found = $lastYearTransaksiGrafik->firstWhere('month', $bulan);
                                            return [
                                                'month' => \Carbon\Carbon::create()->month($bulan)->format('M'),
                                                'transaksi' => $found->transaksi ?? 0,
                                                'topup' => $found->topup ?? 0,
                                                'redeem' => $found->redeem ?? 0,
                                            ];
                                        });

            $d['getTabelPointInMonth'] = DB::connection('mysql_secondary')
                                                ->table('point')
                                                ->join('cabangs', 'cabangs.id', '=', 'point.cabang_id')
                                                ->select(
                                                    'cabangs.name',
                                                    DB::raw("SUM(CASE WHEN point.type = 1 THEN point.jml_trans ELSE 0 END) as transaksi"),
                                                    DB::raw("SUM(CASE WHEN point.type = 1 THEN point.point ELSE 0 END) as topup"),
                                                    DB::raw("SUM(CASE WHEN point.type = 2 THEN point.point ELSE 0 END) as redeem"),
                                                    DB::raw("SUM(CASE WHEN point.type = 1 THEN point.point ELSE 0 END) - SUM(CASE WHEN point.type = 2 THEN point.point ELSE 0 END) as saldo")
                                                )
                                                ->where('point.cabang_id', '!=', 0)
                                                ->whereYear('point.created_at', $tahun_saat_ini)
                                                ->whereMonth('point.created_at', $bulan_saat_ini)
                                                ->groupBy('cabangs.name')
                                                ->orderBy('transaksi', 'desc') // opsional: tampilkan outlet transaksi tertinggi dulu
                                                ->get();
                                                
        return Inertia::render('Crm/Index', [
            'd' => $d,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    
    }

    public function create()
    {
        $jabatans = Jabatan::where('status', 'A')->orderBy('nama_jabatan', 'asc')->get();
        $divisis = Divisi::whereNull('deleted_at')->orderBy('nama_divisi', 'asc')->get();
        $outlets = Outlet::where('status', 'A')->orderBy('nama_outlet', 'asc')->get();
        $roles = Role::where('status', 'A')->orderBy('nama_role', 'asc')->get();

        return Inertia::render('Employee/FormEmployee', [
            'jabatans' => $jabatans,
            'divisis' => $divisis,
            'outlets' => $outlets,
            'roles' => $roles,
        ]);

        return inertia('Employee/FormEmployee');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'region' => 'string|max:20',
            'status' => 'required|in:A,B',
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
            'password' => 'nullable|string|min:4',
            'imei' => 'nullable|string|max:50',
            'id_jabatan' => 'nullable|exists:tbl_data_jabatan,id_jabatan',
            'id_divisi' => 'nullable|exists:tbl_data_divisi,id',
            'id_outlet' => 'nullable|exists:tbl_data_outlet,id_outlet',
            'id_role' => 'nullable|exists:tbl_data_role,id_role',
            'name_emergency_contact' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:50',
            'emergency_contact_relationship' => 'nullable|string|max:50',
            'marital_status' => 'nullable|string|max:20',
            'number_of_children' => 'nullable|integer|min:0',
            'spouse' => 'nullable|string|max:100',
            'wa_spouse' => 'nullable|string|max:20',
            'id_card' => 'nullable|string|max:255',
            'upload_id_card' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
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

        $id = DB::table('users')->insertGetId([
            'nik' => $validated['nik'],
            'nama_lengkap' => $validated['name'],
            'region' => $validated['region'],
            'status' => $validated['status'],
            'jenis_kelamin' => $validated['gender'] ?? null,
            'tempat_lahir' => $validated['place_birth'] ?? null,
            'tanggal_lahir' => isset($validated['date_birth']) ? date('Y-m-d', strtotime($validated['date_birth'])) : null,
            'alamat' => $validated['domicile'] ?? null,
            'alamat_ktp' => $validated['domicile_ktp'] ?? null,
            'agama' => $validated['religion'] ?? null,
            'golongan_darah' => $validated['blood_type'] ?? $validated['golongan_darah'] ?? null, // fallback
            'no_hp' => $validated['wa_number'] ?? null,
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'] ?? bcrypt($validated['password']) ?? null,
            'hint_password' => $validated['password'] ?? null, // Simpan hint password jika ada
            'imei' => $validated['imei'] ?? null,
            'id_jabatan' => $validated['id_jabatan'] ?? null,
            'division_id' => $validated['id_divisi'] ?? null,
            'id_outlet' => $validated['id_outlet'] ?? null,
            'id_role' => $validated['id_role'] ?? null,
            'nama_kontak_darurat' => $validated['name_emergency_contact'] ?? null,
            'no_hp_kontak_darurat' => $validated['emergency_contact'] ?? null,
            'hubungan_kontak_darurat' => $validated['emergency_contact_relationship'] ?? null,
            'status_pernikahan' => $validated['marital_status'] ?? null,
            'jumlah_anak' => $validated['number_of_children'] ?? null,
            'nama_pasangan' => $validated['spouse'] ?? null,
            'wa_pasangan' => $validated['wa_spouse'] ?? null,
            'no_ktp' => $validated['id_card'] ?? null,
            'foto_ktp' => isset($customer->upload_id_card) ? $customer->upload_id_card : null,
            'nomor_kk' => $validated['family_card_number'] ?? null,
            'no_rekening' => $validated['bca_account_number'] ?? null,
            'nama_rekening' => $validated['bca_account_name'] ?? null,
            'npwp_number' => $validated['npwp_number'] ?? null,
            'bpjs_health_number' => $validated['bpjs_health_number'] ?? null,
            'bpjs_employment_number' => $validated['bpjs_employment_number'] ?? null,
            'last_education' => $validated['last_education'] ?? null,
            'name_school_college' => $validated['name_school_college'] ?? null,
            'school_college_major' => $validated['school_college_major'] ?? null,
            'work_start_date' => isset($validated['work_start_date']) ? date('Y-m-d', strtotime($validated['work_start_date'])) : null,
            'position' => $validated['position'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $customer = DB::table('users')->where('id', $id)->first();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'create',
            'module' => 'customers',
            'description' => 'Menambahkan customer: ' . $customer->nama_lengkap,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => null,
            'new_data' => json_encode($customer),
        ]);
        return redirect()->route('employee.index')->with('success', 'Customer berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $customer = DB::table('users')->where('id', '=', $id)->first();

        $jabatans = Jabatan::where('status', 'A')->orderBy('nama_jabatan', 'asc')->get();
        $divisis = Divisi::whereNull('deleted_at')->orderBy('nama_divisi', 'asc')->get();
        $outlets = Outlet::where('status', 'A')->orderBy('nama_outlet', 'asc')->get();
        $roles = Role::where('status', 'A')->select(['id_role as role_id', 'nama_role', 'keterangan', 'status', 'created_at', 'updated_at'])->orderBy('nama_role', 'asc')->get();
        
        return Inertia::render('Employee/FormEmployee', [
            'customer' => $customer,
            'jabatans' => $jabatans,
            'divisis' => $divisis,
            'outlets' => $outlets,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, $id)
    {
        $field = array_keys($request->all())[0];
        $value = $request->input($field);
        $bulan_tahun = $request->input('bulan_tahun');
        [$tahun, $bulan] = explode('-', $bulan_tahun);

        DB::table('tbl_transaksi_gaji')
        ->where('user_id', $id)
        ->where('tahun_create', $tahun)
        ->where('bulan_create', $bulan)
        ->update([$field => $value]);

        // return response()->json(['success' => true]);
    }

    public function generatePDF($userId, $bulan_tahun)
    {
        $user = User::with(['tgajisatuan', 'hasjabatan', 'hasoutlet', 'divisi'])
            ->select('id', 'id_jabatan', 'division_id', 'id_outlet', 'nama_lengkap', 'nik', 'no_rekening', 'no_bpjs_tk')
            ->where('id', $userId)->firstOrFail();

        if (Str::contains($bulan_tahun, '-')) {
            [$tahun, $bulan] = explode('-', $bulan_tahun);
        } else {
            $tahun = now()->format('Y');
            $bulan = now()->format('m');
        }

        $start_date = Carbon::create($tahun, $bulan - 1, 26)->startOfDay();
        $end_date = Carbon::create($tahun, $bulan, 25)->endOfDay();
        $jumlah_hari_kerja = (int) $start_date->diffInDays($end_date) + 1;
        
        $gaji_rata_rata = (($user->tgajisatuan->gaji + $user->tgajisatuan->tunjangan_jabatan) / $jumlah_hari_kerja);
 
        $user->alpa_kerja_factor = DB::table('tbl_attendance as atend')
            ->where('atend.user_id', $user->id)
            ->whereBetween('atend.tgl', [$start_date, $end_date])
            ->whereNull('atend.flag_libur')
            ->where(function ($query) {
                $query->where('atend.check_in', '00:00')
                    ->orWhere('atend.check_out', '00:00');
            })
            ->count();

        $user->total_telat = DB::table('tbl_attendance as atend')
            ->where('atend.user_id', $user->id)
            ->whereBetween('atend.tgl', [$start_date, $end_date])
            ->sum('atend.telat');

        $user->total_gaji = $user->tgajisatuan->gaji + $user->tgajisatuan->tunjangan_jabatan;
        $user->jumlah_alpa_kerja = $gaji_rata_rata * $user->alpa_kerja_factor;
        $user->jumlah_telat = $user->tgajisatuan->telat_init * $user->total_telat;
        $user->total_potongan = $user->jumlah_alpa_kerja + $user->jumlah_telat;
        $user->gaji_bersih = $user->total_gaji - $user->jumlah_alpa_kerja - $user->jumlah_telat;
            // dd($user->gaji_bersih);
        $pdf = Pdf::loadView('pdf.slip-gaji', compact('user', 'bulan_tahun'));
        return $pdf->stream("Slip-Gaji-{$user->nama_lengkap}.pdf");
    }

    public function destroy($id)
    {
        $customer = DB::table('users')->where('id', $id)->first();
        $oldData = $customer;
        DB::table('users')->where('id', $id)->update([
            'status' => 'B',
            'updated_at' => now(),
        ]);
        $newData = DB::table('users')->where('id', $id)->first();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'delete',
            'module' => 'customers',
            'description' => 'Menonaktifkan karyawan: ' . $customer->nama_lengkap,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($newData),
        ]);
        return redirect()->route('employee.index')->with('success', 'Karyawan berhasil dinonaktifkan!');
    }

    public function toggleStatus($id, Request $request)
    {
        $customer = DB::table('users')->where('id', $id)->first();
        $oldData = $customer;
        $status = $request->status == 'inactive' ? 'B' : 'A';
        DB::table('users')->where('id', $id)->update([
            'status' => $status,
            'updated_at' => now(),
        ]);
        $newData = DB::table('users')->where('id', $id)->first();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'status_toggle',
            'module' => 'karyawan',
            'description' => 'Mengubah status karyawan: ' . $customer->nama_lengkap . ' menjadi ' . $request->status,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($newData),
        ]);
        return response()->json(['success' => true]);
    }
} 