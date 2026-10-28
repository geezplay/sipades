<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Dokumen extends Model
{
    use HasFactory;

    protected $table = 'dokumen';
    protected $primaryKey = 'id_dokumen';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_dokumen',
        'id_pengajuan',
        'nama_dokumen',
        'file_path',
        'file_size',
        'mime_type',
        'tanggal_upload',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'tanggal_upload' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            if (empty($model->tanggal_upload)) {
                $model->tanggal_upload = now();
            }
        });
    }

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'id_pengajuan', 'id_pengajuan');
    }
}
