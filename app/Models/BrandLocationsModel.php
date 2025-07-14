<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandLocationsModel extends Model
{
    protected $table = 'webprofile_brand_locations';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function brand()
    {
        return $this->belongsTo(BrandsModel::class, 'id_brand', 'id');
    }
}
