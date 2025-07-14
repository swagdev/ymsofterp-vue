<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandsModel extends Model
{
    protected $table = 'webprofile_brands';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function locations()
    {
        return $this->hasMany(BrandLocationsModel::class, 'id_brand', 'id');
    }
}
