<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\BrandsModel;
use App\Models\BrandsModelItem;

class ApiLoadDataMenuRestoController extends Controller
{
    public function index(Request $request) {}
    public function loadMenuApp()
    {
        $data = [
            [
                "name" => "home",
                "title" => "Home",
                "status" => 1
            ],
            [
                "name" => "news",
                "title" => "News",
                "status" => 1
            ],
            [
                "name" => "location",
                "title" => "Location",
                "status" => 1
            ],
            [
                "name" => "about_us",
                "title" => "About Us",
                "status" => 1
            ],
            [
                "name" => "justus_menu",
                "title" => "Justus Menu",
                "status" => 0
            ],
            [
                "name" => "justus_menu_new",
                "title" => "Justus Menu",
                "status" => 1
            ],
            [
                "name" => "tempayan_menu",
                "title" => "Tempayan Menu",
                "status" => 1
            ],
            [
                "name" => "reservation",
                "title" => "Reservation",
                "status" => 1
            ],
            [
                "name" => "profile",
                "title" => "Profile",
                "status" => 1
            ]
        ];
        //echo json_encode($data);
        return $this->returnJsonHeader($data);
    }

    public function loadMenuAppTesting()
    {
        $brands = BrandsModel::get();

        
        // foreach ($brands as $index => $brand) {
            
        //     $data2[] = [
        //         "name" => 'tempayan_menu',
        //         "title" => $brand->title,
        //         "status" => 1,
        //     ];

        //     $brandsItem = BrandsModelItem::where('id_webprofile_brands', '=', $brand->id)->get();

        //     foreach ($brandsItem as $indexs => $brandItems){
        //         $data2[$index]['subMenu'][$indexs]['name'] = 'tempayan_menu';
        //         $data2[$index]['subMenu'][$indexs]['title'] = $brandItems->name;
        //         $data2[$index]['subMenu'][$indexs]['status'] = 1;
        //     }
        // }

        
        $data1 = [
            [
                "name" => "home",
                "title" => "Home",
                "status" => 1
            ],
            [
                "name" => "news",
                "title" => "News",
                "status" => 1
            ],
            [
                "name" => "location",
                "title" => "Location",
                "status" => 1
            ],
            [
                "name" => "about_us",
                "title" => "About Us",
                "status" => 1
            ]
            ];

         $data3 = [
            [
                "name" => "tempayan_menu",
                "title" => "Tempayan Menu",
                "status" => 1,
                "subMenu" => [
                    "name" => "latest_news",
                    "title" => "Latest News",
                    "status" => 1
                ]
            ],
            [
                "name" => "reservation",
                "title" => "Reservation",
                "status" => 1
            ],
            [
                "name" => "profile",
                "title" => "Profile",
                "status" => 1
            ]
        ];

        $data = array_merge($data1, $data3);

        return $this->returnJsonHeader($data);
    }

    public function loadMenuJustus(Request $request)
    {
        $justusSteakhouseBrands = BrandsModel::select('id', 'menu_pdf')
            ->where(function ($query) {
                $query->where('title', 'like', '%justus steak house%')
                    ->orWhere('title', 'like', '%justus steakhouse%');
            })
            ->orderByDesc('id')
            ->first();

        $justusSteakhouseBrandItems = BrandsModelItem::select('name','menu_pdf')
            ->where('id_webprofile_brands', '=', $justusSteakhouseBrands->id)
            ->orderByDesc('id')
            ->get();
        $data = [];
        $baseUrl = "https://justusmember.co.id/assets/justusku_web_profile/images/";
        foreach ($justusSteakhouseBrandItems as $row) {
            $data[] = [
                "menu_name" => $row->name,
                "title" => "Justus Steakhouse Menu",
                "pdf_link" => $baseUrl . $row->menu_pdf
            ];
        }

        return $this->returnJsonHeader($data);
    }
    public function loadMenuTempayan(Request $request)
    {
        $tempayanBrands = BrandsModel::select('menu_pdf')
            ->where(function ($query) {
                $query->where('title', 'like', '%tempayan%')
                    ->orWhere('title', 'like', '%tempayan%');
            })
            ->orderByDesc('id')
            ->get();
        $data = [];
        $baseUrl = "https://justusmember.co.id/assets/justusku_web_profile/images/";
        foreach ($tempayanBrands as $row) {
            $data[] = [
                "menu_name" => "Tempayan Menu",
                "title" => "Tempayan Indonesian Bistro Menu",
                "pdf_link" => $baseUrl . $row->menu_pdf
            ];
        }
        return $this->returnJsonHeader($data);
    }
}
