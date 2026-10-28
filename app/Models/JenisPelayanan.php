<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisPelayanan extends Model
{
    use HasFactory;

    protected $table = 'jenis_pelayanan';
    protected $primaryKey = 'id_pelayanan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pelayanan',
        'nama_pelayanan',
        'deskripsi',
        'persyaratan',
        'icon',
        'formulir_schema',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'formulir_schema' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'id_pelayanan', 'id_pelayanan');
    }
}
