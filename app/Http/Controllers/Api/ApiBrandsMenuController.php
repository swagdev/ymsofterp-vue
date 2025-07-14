<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\BrandsModel;
use App\Models\BrandsModelItem;

class ApiBrandsMenuController extends Controller
{
    public function index(Request $request) {}

    public function menu($id)
    {
        $data = BrandsModelItem::where('id_webprofile_brands', $id)
                    ->orderBy('id', 'desc')->get();

        return $this->returnJsonHeader($data);
    }
    
}
