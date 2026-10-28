<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KepalaDesa extends Model
{
    use HasFactory;

    protected $table = 'kepala_desa_lurah';
    protected $primaryKey = 'id_pimpinan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pimpinan',
        'id_user',
        'jabatan',
        'nip',
        'tanda_tangan_digital',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function surat()
    {
        return $this->hasMany(Surat::class, 'id_pimpinan_penandatangan', 'id_pimpinan');
    }
}
