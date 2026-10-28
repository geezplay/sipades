<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pengajuan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan';
    protected $primaryKey = 'id_pengajuan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pengajuan',
        'nomor_tiket',
        'id_masyarakat',
        'id_pelayanan',
        'tanggal_pengajuan',
        'status',
        'data_formulir',
        'catatan',
        'catatan_revisi',
        'alasan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'data_formulir' => 'array',
            'tanggal_pengajuan' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            if (empty($model->nomor_tiket)) {
                $model->nomor_tiket = 'TIKET-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
            if (empty($model->tanggal_pengajuan)) {
                $model->tanggal_pengajuan = now();
            }
        });
    }

    public function masyarakat()
    {
        return $this->belongsTo(Masyarakat::class, 'id_masyarakat', 'id_masyarakat');
    }

    public function pelayanan()
    {
        return $this->belongsTo(JenisPelayanan::class, 'id_pelayanan', 'id_pelayanan');
    }

    public function jenisPelayanan()
    {
        return $this->belongsTo(JenisPelayanan::class, 'id_pelayanan', 'id_pelayanan');
    }

    public function dokumen()
    {
        return $this->hasMany(Dokumen::class, 'id_pengajuan', 'id_pengajuan');
    }

    public function verifikasi()
    {
        return $this->hasMany(Verifikasi::class, 'id_pengajuan', 'id_pengajuan');
    }

    public function surat()
    {
        return $this->hasOne(Surat::class, 'id_pengajuan', 'id_pengajuan');
    }
}
