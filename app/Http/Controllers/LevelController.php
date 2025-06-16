<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Jabatan;
use App\Models\Divisi;
use App\Models\SubDivisi;
use App\Models\Level;
use App\Models\Outlet;
use App\Models\Role;
use Inertia\Inertia;
use App\Models\ActivityLog;

class LevelController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('tbl_data_level');
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_level', 'like', "%$search%")
                //   ->orWhere('tbl_data_divisi.nama_divisi', 'like', "%$search%")
                //   ->orWhere('tbl_data_sub_divisi.nama_sub_divisi', 'like', "%$search%")
                ;
            });
        }
        if ($request->filled('status') OR empty($request->status)) {
            $status = $request->status == 'inactive' ? 'N' : 'A';
            $query->where('status', $status);
        }
        $d = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return Inertia::render('Level/Index', [
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
            'nama_level' => 'required|string|max:100',
            'nilai_level' => 'required|string|max:100',
            'nilai_public_holiday' => 'required|numeric|min:0',
            'nilai_dasar_potongan_bpjs' => 'required|numeric|min:0',
            'nilai_point' => 'required|numeric|min:0',
            'qa_reward' => 'required|numeric|min:0',
            'qa_penalty' => 'required|numeric|min:0',
        ]);

        $id = DB::table('tbl_data_level')->insertGetId([
            'nama_level' => $validated['nama_level'],
            'nilai_level' => $validated['nilai_level'],
            'nilai_public_holiday' => $validated['nilai_public_holiday'],
            'nilai_dasar_potongan_bpjs' => $validated['nilai_dasar_potongan_bpjs'],
            'nilai_point' => $validated['nilai_point'],
            'qa_reward' => $validated['qa_reward'],
            'qa_penalty' => $validated['qa_penalty'],
            'status' => 'A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $customer = DB::table('users')->where('id', Auth::id())->first();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'create',
            'module' => 'Level',
            'description' => 'Menambahkan Level: ' . $customer->nama_level,
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
        $validated = $request->validate([
            'nama_level' => 'required|string|max:100',
            'nilai_level' => 'required|string|max:100',
            'nilai_public_holiday' => 'required|numeric|min:0',
            'nilai_dasar_potongan_bpjs' => 'required|numeric|min:0',
            'nilai_point' => 'required|numeric|min:0',
            'qa_reward' => 'required|numeric|min:0',
            'qa_penalty' => 'required|numeric|min:0',
        ]);
        
        $customer = DB::table('tbl_data_level')->where('id', $id)->first();
        $oldData = $customer;
        
        DB::table('tbl_data_level')->where('id', $id)->update([
            'nama_level' => $validated['nama_level'],
            'nilai_level' => $validated['nilai_level'],
            'nilai_public_holiday' => $validated['nilai_public_holiday'],
            'nilai_dasar_potongan_bpjs' => $validated['nilai_dasar_potongan_bpjs'],
            'nilai_point' => $validated['nilai_point'],
            'qa_reward' => $validated['qa_reward'],
            'qa_penalty' => $validated['qa_penalty'],
            'status' => 'A',
            'updated_at' => now(),
        ]);

        $newData = DB::table('tbl_data_level')->where('id', Auth::id())->first();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'update',
            'module' => 'Level',
            'description' => 'Mengupdate Data Level: ' . $newData->nama_level,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($newData),
        ]);
        return redirect()->route('level.index')->with('success', 'Level berhasil diupdate!');
    }

    public function destroy($id)
    {
        $customer = DB::table('tbl_data_level')->where('id', $id)->first();
        $oldData = $customer;
        DB::table('tbl_data_level')->where('id', $id)->update([
            'status' => 'N',
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
        $status = $request->status == 'inactive' ? 'N' : 'A';
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