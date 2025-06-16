<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SubDivisi extends Model {
    protected $table = 'tbl_data_sub_divisi';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
} 