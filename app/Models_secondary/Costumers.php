<?php

namespace App\Models_secondary;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Costumers extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $connection = 'mysql_secondary';
    protected $fillable = [
        'id',
        'costumers_id',
        'nik',
        'name',
        'email',
        'alamat',
        'kota',
        'kode_pos',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'pekerjaan',
        'agama',
        'valid_until',
        'golongan_darah',
        'status_warga_negara',
        'status_kawin',
        'status_aktif',
        'password',
        'android_password',
        'hint',
        'pin',
        'tanggal_aktif',
        'status_block',
        'barcode',
        'last_logged',
        'telepon',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
}
