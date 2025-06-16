<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TransaksiGaji extends Model {
    protected $table = 'tbl_transaksi_gaji';
    protected $primaryKey = 'id';
    public $timestamps = false;
} 