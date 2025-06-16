<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Jabatan;
use App\Models\Divisi;
use App\Models\SubDivisi;
use App\Models\level;
use App\Models\Outlet;
use App\Models\Role;
use Inertia\Inertia;
use App\Models\ActivityLog;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('tbl_data_jabatan')
            ->select('tbl_data_jabatan.*', 
            'jabatan_atasan.nama_jabatan as nama_atasan_jabatan', 
            'tbl_data_divisi.nama_divisi', 
            'tbl_data_sub_divisi.nama_sub_divisi', 'tbl_data_level.nama_level')
            ->leftJoin('tbl_data_jabatan as jabatan_atasan', 'tbl_data_jabatan.id_atasan', '=', 'jabatan_atasan.id_jabatan')
            ->leftJoin('tbl_data_divisi', 'tbl_data_jabatan.id_divisi', '=', 'tbl_data_divisi.id')
            ->leftJoin('tbl_data_sub_divisi', 'tbl_data_jabatan.id_sub_divisi', '=', 'tbl_data_sub_divisi.id')
            ->leftjoin('tbl_data_level', 'tbl_data_jabatan.id_level', '=', 'tbl_data_level.id');
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tbl_data_jabatan.nama_jabatan', 'like', "%$search%")
                  ->orWhere('tbl_data_divisi.nama_divisi', 'like', "%$search%")
                  ->orWhere('tbl_data_sub_divisi.nama_sub_divisi', 'like', "%$search%")
                ;
            });
        }
        if ($request->filled('status') OR empty($request->status)) {
            $status = $request->status == 'inactive' ? 'N' : 'A';
            $query->where('tbl_data_jabatan.status', $status);
        }
        $d = $query->orderBy('id_jabatan', 'desc')->paginate(10)->withQueryString();
        $jabatans = Jabatan::where('status', 'A')->orderBy('nama_jabatan', 'asc')->get();
        $divisis = Divisi::whereNull('deleted_at')->orderBy('nama_divisi', 'asc')->get();
        $subDivisis = SubDivisi::orderBy('nama_sub_divisi', 'asc')->get();
        $levels = level::where('status', 'A')->orderBy('nama_level', 'asc')->get();

        return Inertia::render('Position/Index', [
            'd' => $d,
            'jabatans' => $jabatans,
            'divisis' => $divisis,
            'subDivisis' => $subDivisis,
            'levels' => $levels,
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
            'nama_jabatan' => 'required|string|max:100',
            'id_atasan' => 'nullable|exists:tbl_data_jabatan,id_jabatan',
            'id_divisi' => 'nullable|exists:tbl_data_divisi,id',
            'id_sub_divisi' => 'nullable|exists:tbl_data_sub_divisi,id',
            'id_level' => 'nullable|exists:tbl_data_level,id',
            'standard_qa' => 'nullable|numeric|min:0',
        ]);

        $id = DB::table('tbl_data_jabatan')->insertGetId([
            'nama_jabatan' => $validated['nama_jabatan'],
            'id_atasan' => $validated['id_atasan'] ?? null,
            'id_divisi' => $validated['id_divisi'] ?? null,
            'id_sub_divisi' => $validated['id_sub_divisi'] ?? null,
            'id_level' => $validated['id_level'] ?? null,
            'standard_qa' => $validated['standard_qa'] ?? null,
            'status' => 'A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $customer = DB::table('users')->where('id', Auth::id())->first();
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
        return redirect()->route('position.index')->with('success', 'Customer berhasil ditambahkan!');
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
            'nama_jabatan' => 'required|string|max:100',
            'id_atasan' => 'nullable|exists:tbl_data_jabatan,id_jabatan',
            'id_divisi' => 'nullable|exists:tbl_data_divisi,id',
            'id_sub_divisi' => 'nullable|exists:tbl_data_sub_divisi,id',
            'id_level' => 'nullable|exists:tbl_data_level,id',
            'standard_qa' => 'nullable|numeric|min:0',
        ]);
        
        $customer = DB::table('tbl_data_jabatan')->where('id_jabatan', $id)->first();
        $oldData = $customer;
        
        DB::table('tbl_data_jabatan')->where('id_jabatan', $id)->update([
            'nama_jabatan' => $validated['nama_jabatan'],
            'id_atasan' => $validated['id_atasan'] ?? null,
            'id_divisi' => $validated['id_divisi'] ?? null,
            'id_sub_divisi' => $validated['id_sub_divisi'] ?? null,
            'id_level' => $validated['id_level'] ?? null,
            'standard_qa' => $validated['standard_qa'] ?? null,
            'status' => 'A',
            'updated_at' => now(),
        ]);

        $newData = DB::table('users')->where('id', Auth::id())->first();
        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'update',
            'module' => 'Jabatan',
            'description' => 'Mengupdate Data Jabatan: ' . $newData->nama_lengkap,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_data' => json_encode($oldData),
            'new_data' => json_encode($newData),
        ]);
        return redirect()->route('position.index')->with('success', 'Jabatan berhasil diupdate!');
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