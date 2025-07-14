<?php

namespace App\Models_secondary;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class MemberPoints extends Model
{
    protected $connection = 'mysql_secondary';
    protected $table = 'point';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(MemberCostumers::class, 'costumer_id');
    }
    public function outlet()
    {
        return $this->belongsTo(MemberCabangs::class, 'cabang_id', 'id');
    }
    public function getTotalRowsThisYear($year = null)
    {
        $year = $year ?: date('Y');
        return $this->select('costumer_id')
            ->whereYear('created_at', $year)
            ->where('costumer_id', $this->costumer_id)
            ->get()
            ->count();
    }
    public function getTotalRowsFilter($start_date, $end_date)
    {
        return $this->select('costumer_id')
            ->whereBetween('created_at', [$start_date, $end_date])
            ->where('costumer_id', $this->costumer_id)
            ->get()
            ->count();
    }
    public function getTotalTransactionThisYear($year = null)
    {
        $year = $year ?: date('Y');
        return $this->whereYear('created_at', $year)
            ->where('costumer_id', $this->costumer_id)
            ->sum('jml_trans');
    }
    public function getTotalTransactionFilter($start_date = null, $end_date = null)
    {
        if (is_null($start_date) || is_null($end_date)) {
            $year = date('Y');
            return $this->whereYear('created_at', $year)
                ->where('costumer_id', $this->costumer_id)
                ->sum('jml_trans');
        }
        return $this->whereBetween('created_at', [$start_date, $end_date])
            ->where('costumer_id', $this->costumer_id)
            ->sum('jml_trans');
    }
    public function getLastTransactionInThisYear($year = null)
    {
        $year = $year ?: date('Y');
        return $this->whereYear('created_at', $year)
            ->where('costumer_id', $this->costumer_id)
            ->orderBy("id", "DESC")
            ->first();
    }
    public function getLastTransactionThisYear($year = null)
    {
        $year = $year ?: date('Y');
        return $this->whereYear('created_at', $year)
            ->where('costumer_id', $this->costumer_id)
            ->orderBy("id", "DESC")
            ->first();
    }
    public function getTotalPointIn($year = null)
    {
        $year = $year ?: date('Y');
        return $this->whereYear('created_at', $year)
            ->where('type', '1')
            ->where('cabang_id', '!=', '0')
            ->where('costumer_id', $this->costumer_id)
            ->sum('point');
    }
    public function getTotalPointOut($year = null)
    {
        $year = $year ?: date('Y');
        return $this->whereYear('created_at', $year)
            ->where('type', '2')
            ->where('cabang_id', '!=', '0')
            ->where('costumer_id', $this->costumer_id)
            ->sum('point');
    }
}
