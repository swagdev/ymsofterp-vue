<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Role extends Model {
    protected $table = 'tbl_data_role';
    protected $primaryKey = 'id_role';
    public $timestamps = false;
} 