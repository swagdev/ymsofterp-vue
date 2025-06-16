<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\MasterGaji;
use App\Models\TransaksiGaji;
use App\models\Jabatan;
use App\models\Divisi;
use App\models\Outlet;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'id_role',
        'id_outlet',
        'division_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];

    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }

    public function mgaji() {
        return $this->hasOne(MasterGaji::class, 'user_id', 'id');
    }

    public function tgajisatuan() {
        return $this->hasOne(TransaksiGaji::class, 'user_id', 'id')->latestOfMany();
    }

    public function hasjabatan() {
        return $this->hasOne(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }

    public function hasdivisi() {
        return $this->hasOne(Divisi::class, 'division_id', 'id');
    }

    public function hasoutlet() {
        return $this->hasOne(Outlet::class, 'id_outlet', 'id_outlet');
    }

    public function jabatan() {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }
    public function divisi() {
        return $this->belongsTo(Divisi::class, 'division_id', 'id');
    }
    public function outlet() {
        return $this->belongsTo(Outlet::class, 'id_outlet', 'id_outlet');
    }
}
