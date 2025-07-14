<?php

namespace App\Models_secondary;

use Illuminate\Database\Eloquent\Model;

class MemberCabangs extends Model
{
    protected $connection = 'mysql_secondary';
    protected $table = 'cabangs';
    protected $primaryKey = 'id';
    protected $guarded = [];
}
