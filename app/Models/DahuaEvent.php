<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DahuaEvent extends Model
{
    use HasFactory;

    protected $table = 'dahua_anpr'; // Set the correct table name

    protected $fillable = [
        'license_plate',
        'confidence',
        'vehicle_brand',
        'vehicle_type',
        'vehicle_color',
        'json_path',
        'license_plate_image_path',
        'car_image_path',
        'event_time'
    ];

}
