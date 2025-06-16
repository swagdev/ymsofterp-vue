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
use Inertia\Inertia;
use App\Models\ActivityLog;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('users');
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('no_ktp', 'like', "%$search%")
                ;
            });
        }
        if ($request->filled('status') OR empty($request->status)) {
            $status = $request->status == 'inactive' ? 'B' : 'A';
            $query->where('status', $status);
        }
        $customers = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        
        return Inertia::render('Employee/Index', [
            'customers' => $customers,
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

        $this->generateNik();

        return Inertia::render('Employee/FormEmployee', [
            'jabatans' => $jabatans,
            'divisis' => $divisis,
            'outlets' => $outlets,
            'roles' => $roles,
            'newNik' => $this->generateNik(),
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
            'upload_family_card' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
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

        $path = null;
        $pathKK = null;
        $pathFoto = null;
        
        if ($request->hasFile('upload_id_card')) {

            $path = $request->file('upload_id_card')->store('employee', 'public');
        }

        if ($request->hasFile('upload_family_card')) {

            $pathKK = $request->file('upload_family_card')->store('employee', 'public');
        }

        if ($request->hasFile('upload_latest_color_photo')) {

            $pathFoto = $request->file('upload_latest_color_photo')->store('employee', 'public');
        }

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
            'password' => bcrypt($validated['password']),
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
            'foto_ktp' => $path ?? null,
            'nomor_kk' => $validated['family_card_number'] ?? null,
            'foto_kk' => $pathKK ?? null,
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
            'upload_latest_color_photo' => $pathFoto ?? null,
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
        // dd($customer);
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
            'upload_family_card' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
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
            // 'upload_latest_color_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
        ]);
        
        $customer = DB::table('users')->where('id', $id)->first();
        $oldData = $customer;

        $path = null;
        $pathKK = null;
        $pathFoto = null;
        
        if ($request->hasFile('upload_id_card')) {
            if ($customer->foto_ktp && Storage::disk('public')->exists($customer->foto_ktp)) {
                Storage::disk('public')->delete($customer->foto_ktp);
            }

            $path = $request->file('upload_id_card')->store('employee', 'public');
        }

        if ($request->hasFile('upload_family_card')) {
            if ($customer->foto_kk && Storage::disk('public')->exists($customer->foto_kk)) {
                Storage::disk('public')->delete($customer->foto_kk);
            }

            $pathKK = $request->file('upload_family_card')->store('employee', 'public');
        }

        if ($request->hasFile('upload_latest_color_photo')) {
            if ($customer->upload_latest_color_photo && Storage::disk('public')->exists($customer->upload_latest_color_photo)) {
                Storage::disk('public')->delete($customer->upload_latest_color_photo);
            }

            $pathFoto = $request->file('upload_latest_color_photo')->store('employee', 'public');
        }
  
        DB::table('users')->where('id', $id)->update([
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
            'password' => $validated['password'] ? bcrypt($validated['password']) : $customer->password,
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
            'foto_ktp' => $path ?? $customer->foto_ktp,
            'nomor_kk' => $validated['family_card_number'] ?? null,
            'foto_kk' => $pathKK ?? $customer->foto_kk,
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
            'upload_latest_color_photo' => $pathFoto ?? $customer->upload_latest_color_photo,
            'updated_at' => now(),
        ]);

        $newData = DB::table('users')->where('id', $id)->first();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'update',
            'module' => 'customers',
            'description' => 'Mengupdate customer: ' . $newData->nama_lengkap,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($newData),
        ]);
        // return redirect()->route('employee.edit', $id)->with('success', 'Customer berhasil diupdate!');
    }

    public function generateNik()
    {
        // Ambil tahun sekarang 2 digit
        $yearPrefix = now()->format('y'); // contoh: '25'

        // Cari NIK tertinggi yang dimulai dengan '25'
        $lastNik = DB::table('users')
            ->where('nik', 'like', $yearPrefix . '%')
            ->orderBy('id', 'desc')
            ->value('nik');

        // Ambil 4 digit terakhir (urutannya)
        $lastNumber = $lastNik ? (int)substr($lastNik, 2) : 0;

        // Tambah 1
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        // Gabungkan: contoh → 25 + 1619
        $newNik = $yearPrefix . $nextNumber;

        return $newNik;
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