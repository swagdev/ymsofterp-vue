<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Jabatan;
use App\Models\Divisi;
use App\Models\SubDivisi;
use App\Models\level;
use App\Models\Outlet;
use App\Models\Role;
use Inertia\Inertia;
use App\Models\ActivityLog;

class WebProfileBrandsController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::connection('mysql_secondary')->table('webprofile_brands as wb');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('wb.title', 'like', "%$search%");
            });
        }
        // if ($request->filled('status') OR empty($request->status)) {
        //     $status = $request->status == 'inactive' ? 'N' : 'A';
        //     $query->where('tbl_data_jabatan.status', $status);
        // }
        $d = $query->orderBy('wb.id', 'asc')->paginate(10)->withQueryString();
        $jabatans = Jabatan::where('status', 'A')->orderBy('nama_jabatan', 'asc')->get();
        $divisis = Divisi::whereNull('deleted_at')->orderBy('nama_divisi', 'asc')->get();
        $subDivisis = SubDivisi::orderBy('nama_sub_divisi', 'asc')->get();
        $levels = level::where('status', 'A')->orderBy('nama_level', 'asc')->get();

        return Inertia::render('WebProfileBrands/Index', [
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
        $request->validate([
        'title' => 'required|string|max:255',
        'link_menu' => 'nullable|url',
        'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'menu_pdf' => 'nullable|file|mimes:pdf|max:4048',
        'content' => 'nullable|string',
        ]);

        // Upload files
        $filenameThumb = time().'_'.$request->file('thumbnail')->getClientOriginalName();
        $filenameImage = time().'_'.$request->file('image')->getClientOriginalName();
        $filenamePdf = time().'_'.$request->file('menu_pdf')->getClientOriginalName();

        $thumbnailPath = null;
        $imagePath = null;
        $pdfPath = null;

        if ($request->hasFile('thumbnail')) {
            $request->file('thumbnail')->move(public_path('assets/justusku_web_profile/images'), $filenameThumb);
            $thumbnailPath = 'assets/justusku_web_profile/images/' . $filenameThumb;
        }

        if ($request->hasFile('image')) {
            $request->file('image')->move(public_path('assets/justusku_web_profile/images'), $filenameImage);
            $imagePath = 'assets/justusku_web_profile/images/' . $filenameImage;
        }

        if ($request->hasFile('menu_pdf')) {
            $request->file('menu_pdf')->move(public_path('assets/justusku_web_profile/pdf'), $filenamePdf);
            $imagePath = 'assets/justusku_web_profile/images/' . $filenamePdf;
        }

        DB::table('webprofile_brands')->insert([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'link_menu' => $request->link_menu,
            'thumbnail' => $thumbnailPath,
            'image' => $imagePath,
            'menu_pdf' => $pdfPath,
            'content' => $request->content,
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan!');
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
        $request->validate([
        'title' => 'required|string|max:255',
        'link_menu' => 'nullable|url',
        'thumbnail' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'menu_pdf' => 'nullable|file|mimes:pdf|max:4048',
        'content' => 'nullable|string',
        ]);
        
        $brand = DB::connection('mysql_secondary')->table('webprofile_brands')->where('id', $id)->first();
        
        if (!$brand) {
            return back()->with('error', 'Data tidak ditemukan');
        }

        // Upload files jika ada file baru
        $thumbnailPath = $brand->thumbnail;
        $imagePath = $brand->image;
        $pdfPath = $brand->menu_pdf;

        if ($request->hasFile('thumbnail')) {
            // Hapus file lama
            if ($brand->thumbnail && File::exists(public_path($brand->thumbnail))) {
                File::delete(public_path($brand->thumbnail));
            }

            $filenameThumb = time().'_'.$request->file('thumbnail')->getClientOriginalName();
            $request->file('thumbnail')->move(public_path('assets/justusku_web_profile/images'), $filenameThumb);
            $thumbnailPath = 'assets/justusku_web_profile/images/' . $filenameThumb;
        }

        if ($request->hasFile('image')) {
            // Hapus file lama
            if ($brand->image && File::exists(public_path($brand->image))) {
                File::delete(public_path($brand->image));
            }

            $filenameImage = time().'_'.$request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('assets/justusku_web_profile/images'), $filenameImage);
            $imagePath = 'assets/justusku_web_profile/images/' . $filenameImage;
        }

        if ($request->hasFile('menu_pdf')) {
            // Hapus file lama
            if ($brand->menu_pdf && File::exists(public_path($brand->menu_pdf))) {
                File::delete(public_path($brand->menu_pdf));
            }

            $filenamePdf = time().'_'.$request->file('menu_pdf')->getClientOriginalName();
            $request->file('menu_pdf')->move(public_path('assets/justusku_web_profile/pdf'), $filenamePdf);
            $pdfPath = 'assets/justusku_web_profile/pdf/' . $filenamePdf;
        }
        dd($id);
        DB::table('webprofile_brands')->where('id', $id)->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'link_menu' => $request->link_menu,
            'thumbnail' => $thumbnailPath,
            'image' => $imagePath,
            'menu_pdf' => $pdfPath,
            'content' => $request->content,
            'updated_at' => now(),
        ]);

        // return redirect()->route('position.index')->with('success', 'Jabatan berhasil diupdate!');
        return redirect()->back()->with('success', 'Data berhasil diedit!');
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

    public function list_menu(Request $request, $id)
    {
        $query = DB::connection('mysql_secondary')->table('webprofile_brand_items as wbi')
                    ->where('wbi.id_webprofile_brands', $id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('wbi.name', 'like', "%$search%");
            });
        }

        $d = $query->orderBy('wbi.id', 'asc')->paginate(10)->withQueryString();

        $jabatans = Jabatan::where('status', 'A')->orderBy('nama_jabatan', 'asc')->get();
        $divisis = Divisi::whereNull('deleted_at')->orderBy('nama_divisi', 'asc')->get();
        $outlets = Outlet::where('status', 'A')->orderBy('nama_outlet', 'asc')->get();
        $roles = Role::where('status', 'A')->orderBy('nama_role', 'asc')->get();

        return Inertia::render('WebProfileBrands/ListMenuIndex', [
            'id' => $id,
            'd' => $d,
            'jabatans' => $jabatans,
            'divisis' => $divisis,
            'outlets' => $outlets,
            'roles' => $roles,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    public function list_menu_store(Request $request, $id)
    {
        $request->validate([
        'name' => 'required|string|max:255',
        'menu_pdf' => 'nullable|file|mimes:pdf|max:4048',
        ]);

        // Upload files
        $filenamePdf = time().'_'.$request->file('menu_pdf')->getClientOriginalName();
        $pdfPath = null;

        if ($request->hasFile('menu_pdf')) {
            $request->file('menu_pdf')->move(public_path('assets/justusku_web_profile/pdf'), $filenamePdf);
            $imagePath = 'assets/justusku_web_profile/images/' . $filenamePdf;
        }
        dd($id);
        DB::table('webprofile_brand_items')->insert([
            'id_webprofile_brands' => $id,
            'name' => $request->name,
            'slug_items' => Str::slug($request->title),
            'menu_pdf' => $pdfPath,
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan!');
    }

    public function list_menu_update(Request $request, $id)
    {
        $request->validate([
        'name' => 'required|string|max:255',
        'menu_pdf' => 'nullable|file|mimes:pdf|max:4048',
        ]);
        
        $brand = DB::connection('mysql_secondary')->table('webprofile_brand_items')->where('id', $id)->first();
        
        if (!$brand) {
            return back()->with('error', 'Data tidak ditemukan');
        }

        $pdfPath = $brand->menu_pdf;

        if ($request->hasFile('menu_pdf')) {
            // Hapus file lama
            if ($brand->menu_pdf && File::exists(public_path($brand->menu_pdf))) {
                File::delete(public_path($brand->menu_pdf));
            }

            $filenamePdf = time().'_'.$request->file('menu_pdf')->getClientOriginalName();
            $request->file('menu_pdf')->move(public_path('assets/justusku_web_profile/pdf'), $filenamePdf);
            $pdfPath = 'assets/justusku_web_profile/pdf/' . $filenamePdf;
        }
        dd($id);
        DB::table('webprofile_brands')->where('id', $id)->update([
            'name' => $request->title,
            'slug_item' => Str::slug($request->slug_item),
            'menu_pdf' => $pdfPath,
            'updated_at' => now(),
        ]);

        // return redirect()->route('position.index')->with('success', 'Jabatan berhasil diupdate!');
        return redirect()->back()->with('success', 'Data berhasil diedit!');
    }
} 