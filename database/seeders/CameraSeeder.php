<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Camera;

class CameraSeeder extends Seeder
{
    public function run()
    {
        $cameras = [
            ['name' => 'Camera 1', 'location' => 'Monitor', 'image' => '/placeholder.svg?height=200&width=300'],
            ['name' => 'Camera 2', 'location' => 'Monitor', 'image' => '/placeholder.svg?height=200&width=300'],
            ['name' => 'Camera 1', 'location' => 'Front Gate', 'image' => '/placeholder.svg?height=200&width=300'],
            ['name' => 'Camera 2', 'location' => 'Front Gate', 'image' => '/placeholder.svg?height=200&width=300'],
            ['name' => 'Camera 1', 'location' => 'Exit Gate', 'image' => '/placeholder.svg?height=200&width=300'],
            ['name' => 'Camera 2', 'location' => 'Exit Gate', 'image' => '/placeholder.svg?height=200&width=300'],
        ];

        foreach ($cameras as $camera) {
            Camera::create($camera);
        }
    }
}