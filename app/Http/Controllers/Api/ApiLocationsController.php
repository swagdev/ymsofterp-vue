<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\BrandsModel;
use App\Models\BrandLocationsModel;


class ApiLocationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin')->except(['load', 'action']);
    }
    public function index()
    {

        $data['brands'] = BrandsModel::orderBy('title', 'asc')->get();
        $data['brand_locations'] = BrandLocationsModel::orderBy('id', 'desc')->get();
        return view('justuskuwebprofile.brand_locations', compact('data'));
    }
    public function save(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:512',
            'id_brand' => 'required|string|max:512',
            'opening_hours' => 'required|string|max:512',
            'phone' => 'required|string|max:512',
            'address' => 'required|string|max:512',
            'google_map_link' => 'nullable|string|max:512',
            'image.required' => 'Gambar wajib diunggah.',
            'image.uploaded' => 'Gambar tidak sesuai dengan ketentuan.',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2048 kilobytes.',
        ]);
        $imageFile = $request['image'];
        $imageName = "";
        if ($imageFile !== null) {
            $imageName = time() . '_' . $imageFile->getClientOriginalName();
            $imageFile->move(public_path('assets/justusku_web_profile/images'), $imageName);
            $this->zeroCrop(public_path('assets/justusku_web_profile/images/' . $imageName));
        }
        $data = [
            'name' => $request->input('name'),
            'id_brand' => $request->input('id_brand'),
            'opening_hours' => $request->input('opening_hours'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'google_map_link' => $request->input('google_map_link'),
            'image' => $imageName,
        ];
        $model = new BrandLocationsModel();
        $model->create($data);
        return redirect()->back()->with('msg', 'Data berhasil disimpan!');
    }
    public function delete(Request $request)
    {
        $id = $request->id;
        $model = new BrandLocationsModel();
        $data = $model->where(['id' => $id])->first();
        if ($data) {
            if (!empty($data->image)) {
                $imagePath = public_path('assets/justusku_web_profile/images/' . $data->image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $model->where(['id' => $id])->delete();

            return redirect()->route("web_profile_locations.index")->with('msg', 'Data berhasil dihapus!');
        } else {
            return redirect()->route("web_profile_locations.index")->with('warning', 'Data tidak ada!');
        }
    }
    public function show(Request $request)
    {
        $id = $request->id;
        $model = new BrandLocationsModel();
        $data['brand_locations'] = $model->where(['id' => $id])->first();
        if (!$data['brand_locations']) {
            return redirect()->route("web_profile_locations.index")->with('warning', 'Data tidak ada!');
        } else {
            $data['brands'] = BrandsModel::orderBy('title', 'asc')->get();
            return view('justuskuwebprofile.brand_locations_show', compact('data'));
        }
    }
    public function update(Request $request)
    {
        $this->validate($request, [
            'id_brand_locations' => 'required',
            'name' => 'required|string|max:512',
            'id_brand' => 'required|string|max:512',
            'opening_hours' => 'required|string|max:512',
            'phone' => 'required|string|max:512',
            'address' => 'required|string|max:512',
            'google_map_link' => 'nullable|string|max:512',
        ]);

        $imageFile = $request['image'];
        $imageName = null;
        if ($imageFile !== null) {
            $imageName = time() . '_' . $imageFile->getClientOriginalName();
            $imageFile->move(public_path('assets/justusku_web_profile/images'), $imageName);
            $this->zeroCrop(public_path('assets/justusku_web_profile/images/' . $imageName));
        }

        $data = [
            'name' => $request->input('name'),
            'id_brand' => $request->input('id_brand'),
            'opening_hours' => $request->input('opening_hours'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'google_map_link' => $request->input('google_map_link'),
        ];
        if ($imageName !== null) {
            $data['image'] = $imageName;
        }
        $model = BrandLocationsModel::where('id', $request->input('id_brand_locations'))->first();
        if ($model) {
            $model->update($data);
            return redirect()->route("web_profile_locations.index")->with('msg', 'Data berhasil diupdate!');
        } else {
            return redirect()->back()->with('error', 'Data gagal diupdate.');
        }
    }
    public function load(Request $request)
    {
        $data['brand_locations'] = BrandLocationsModel::orderBy('id_brand', 'asc')->orderBy('name', 'asc')
        ->get()
            ->map(function ($location) {
                $location->brand_title = $location->brand->title ?? 'N/A';
                return $location;
            });
        return response()->json($data);
        //$this->returnJsonHeader(json_decode(json_encode($data)));
    }
    public function action(Request $request) {}
}
