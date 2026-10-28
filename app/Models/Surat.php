<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Surat extends Model
{
    use HasFactory;

    protected $table = 'surat';
    protected $primaryKey = 'id_surat';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_surat',
        'id_pengajuan',
        'nomor_surat',
        'tanggal_surat',
        'id_pimpinan_penandatangan',
        'file_pdf_path',
        'qr_token',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            if (empty($model->qr_token)) {
                $model->qr_token = Str::random(64);
            }
            if (empty($model->tanggal_surat)) {
                $model->tanggal_surat = now()->toDateString();
            }
        });
    }

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'id_pengajuan', 'id_pengajuan');
    }

    public function pimpinan()
    {
        return $this->belongsTo(KepalaDesa::class, 'id_pimpinan_penandatangan', 'id_pimpinan');
    }

    public function kepalaDesa()
    {
        return $this->belongsTo(KepalaDesa::class, 'id_pimpinan_penandatangan', 'id_pimpinan');
    }
}
