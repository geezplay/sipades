<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_user',
        'nama',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getAuthIdentifierName(): string
    {
        return 'id_user';
    }

    public function masyarakat()
    {
        return $this->hasOne(Masyarakat::class, 'id_user', 'id_user');
    }

    public function petugas()
    {
        return $this->hasOne(Petugas::class, 'id_user', 'id_user');
    }

    public function kepalaDesa()
    {
        return $this->hasOne(KepalaDesa::class, 'id_user', 'id_user');
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class, 'id_user', 'id_user');
    }
}
