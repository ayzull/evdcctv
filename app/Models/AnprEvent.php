<?php

namespace App\Models;
// app\Models\AnprEvent.php
use Illuminate\Database\Eloquent\Model;

class AnprEvent extends Model
{
    protected $fillable = [
        'license_plate',
        'event_time',
        'xml_path',
        'license_plate_image_path',
        'detection_image_path'
    ];
}


