<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Camera;

class CameraSeeder extends Seeder
{
    public function run()
    {
        $cameras = [
            ['ip' => 'test','brand' => 'test','model' => 'test','name' => 'test','location' => 'Monitor','username' => 'test','password' => 'test','rtsp' => 'rtsp://admin:evocity@098@60.48.189.67:5541/stream'],
        ];

        foreach ($cameras as $camera) {
            Camera::create($camera);
        }
    }
}