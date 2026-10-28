<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Masyarakat extends Model
{
    use HasFactory;

    protected $table = 'masyarakat';
    protected $primaryKey = 'id_masyarakat';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_masyarakat',
        'id_user',
        'nik',
        'alamat',
        'no_hp',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'id_masyarakat', 'id_masyarakat');
    }
}
