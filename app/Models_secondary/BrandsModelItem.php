<?php

namespace App\Models_secondary;

use Illuminate\Database\Eloquent\Model;

class BrandsModelItem extends Model
{
    protected $connection = 'mysql_secondary';
    protected $table = 'webprofile_brand_items';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function locations()
    {
        return $this->hasMany(BrandLocationsModel::class, 'id_brand', 'id');
    }
}
