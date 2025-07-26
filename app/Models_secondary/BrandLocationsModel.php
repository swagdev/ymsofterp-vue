<?php

namespace App\Models_secondary;

use Illuminate\Database\Eloquent\Model;

class BrandLocationsModel extends Model
{
    protected $connection = 'mysql_secondary';
    protected $table = 'webprofile_brand_locations';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function brand()
    {
        return $this->belongsTo(BrandsModel::class, 'id_brand', 'id');
    }
}
