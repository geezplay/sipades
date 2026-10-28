<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_notifikasi',
        'id_user',
        'judul',
        'pesan',
        'link_url',
        'status_baca',
    ];

    protected function casts(): array
    {
        return [
            'status_baca' => 'boolean',
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
}
