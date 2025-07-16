<?php

namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;
class DashboardAbsensi extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'dashboard_absensi';
    protected $fillable = ['field1', 'field2'];
}
