<?php

namespace App\Models_secondary;

use Illuminate\Database\Eloquent\Model;

class MemberCostumers extends Model
{
    protected $connection = 'mysql_secondary';
    protected $table = 'costumers';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function points()
    {
        return $this->hasMany(MemberPoints::class, 'costumer_id');
    }
    public function getLastTransaction()
    {
        return $this->points()
            ->select('cabang_id', 'created_at')
            ->orderBy('id', 'desc')
            ->first();
    }
    public function getTotalTransactionThisYear($year = null)
    {
        $year = $year ?: date('Y');
        return $this->points()
            ->whereYear('created_at', $year)
            ->sum('jml_trans');
    }
    public function getTotalPointIn($year = null)
    {
        $year = $year ?: date('Y');
        return $this->points()
            ->whereYear('created_at', $year)
            ->where('type', '1')
            ->where('cabang_id', '!=', '0')
            ->sum('point');
    }
    public function getTotalPointOut($year = null)
    {
        $year = $year ?: date('Y');
        return $this->points()
            ->whereYear('created_at', $year)
            ->where('type', '2')
            ->where('cabang_id', '!=', '0')
            ->sum('point');
    }
}
