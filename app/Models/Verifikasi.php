<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Verifikasi extends Model
{
    use HasFactory;

    protected $table = 'verifikasi';
    protected $primaryKey = 'id_verifikasi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_verifikasi',
        'id_pengajuan',
        'id_petugas',
        'tanggal_verifikasi',
        'hasil',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_verifikasi' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            if (empty($model->tanggal_verifikasi)) {
                $model->tanggal_verifikasi = now();
            }
        });
    }

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'id_pengajuan', 'id_pengajuan');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'id_petugas', 'id_petugas');
    }
}
