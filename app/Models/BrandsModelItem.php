<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandsModelItem extends Model
{
    protected $table = 'webprofile_brand_items';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function locations()
    {
        return $this->hasMany(BrandLocationsModel::class, 'id_brand', 'id');
    }
}
