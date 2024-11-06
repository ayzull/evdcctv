<?php

namespace App\Models;
// app\Models\camera.php
use Illuminate\Database\Eloquent\Model;

class Camera extends Model
{
    protected $fillable = [
        'ip',
        'brand',
        'model',
        'name',
        'location',
        'username',
        'password',
        'rtsp'
    ];
}
