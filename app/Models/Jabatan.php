<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Monolog\Level;

class Jabatan extends Model {
    protected $table = 'tbl_data_jabatan';
    protected $primaryKey = 'id_jabatan';
    public $timestamps = false;
    protected $guarded = [];
    public function namaAtasan($id_atasan)
    {
        return self::where('id_jabatan', $id_atasan)
                    ->value('nama_jabatan') ?? '-';
    }
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi', 'id');
    }
    public function namaDivisi()
    {
        return $this->divisi ? $this->divisi->nama_divisi : '-';
    }
    public function sub_divisi()
    {
        return $this->belongsTo(SubDivisi::class, 'id_sub_divisi', 'id');
    }
    public function namaSubDivisi()
    {
        return $this->sub_divisi ? $this->sub_divisi->nama_sub_divisi : '-';
    }
    public function level()
    {
        return $this->belongsTo(Level::class, 'id_level', 'id');
    }
    public function namaLevel()
    {
        return $this->level ? $this->level->nilai_level." - ".$this->level->nama_level : '-';
    }
} 