<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PasswordResetToken extends Model
{
    // Tentukan bahwa primary key menggunakan UUID
    protected $primaryKey = 'id';
    public $incrementing = false;  // Non-aktifkan auto-increment
    protected $keyType = 'string'; // Tentukan bahwa tipe key adalah string (UUID)

    // Isi mass-assignment untuk kolom yang boleh diisi
    protected $fillable = ['email', 'token','expires_at'];

    // Menggunakan UUID untuk ID secara otomatis
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate UUID jika tidak diberikan
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
