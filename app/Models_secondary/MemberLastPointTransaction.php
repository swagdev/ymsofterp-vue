<?php

namespace App\Models_secondary;

use Illuminate\Database\Eloquent\Model;

class MemberLastPointTransaction extends Model
{
    protected $connection = 'mysql_secondary';
    protected $table = 'viewMemberPointLastTransaction';
    protected $guarded = [];
    //atribut: costumer_id, jml_trans, cabang_id, created_at

    public function outlet()
    {
        return $this->belongsTo(MemberCabangs::class, 'cabang_id', 'id');
    }
    public function points()
    {
        return $this->hasMany(MemberPoints::class, 'costumer_id', 'costumer_id');
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
    public function getTotalTransactionFilter($start_date = null, $end_date = null)
    {
        if (is_null($start_date) || is_null($end_date)) {
            $year = date('Y');
            return $this->points()
                ->whereYear('created_at', $year)
                ->sum('jml_trans');
        }
        return $this->points()
            ->whereBetween('created_at', [$start_date, $end_date])
            ->sum('jml_trans');
    }
    public function getLastTransactionInFilter($start_date = null, $end_date = null)
    {
        if (is_null($start_date) || is_null($end_date)) {
            $year = date('Y');
            return $this->points()
                ->whereYear('created_at', $year)
                ->orderBy("id", "DESC")
                ->first();
        }
        return $this->points()
            ->whereBetween('created_at', [$start_date, $end_date])
            ->orderBy("id", "DESC")
            ->first();
    }
    public function getTotalRowsThisYear($year = null)
    {
        $year = $year ?: date('Y');
        return $this->points()
            ->whereYear('created_at', $year)
            ->count();
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
