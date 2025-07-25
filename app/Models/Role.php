<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_role'
    ];

    public function users()
    {
        return $this->hasMany(Users::class, 'id_role');
    }
}
