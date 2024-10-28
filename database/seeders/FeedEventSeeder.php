<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeedEvent;
use Carbon\Carbon;

class FeedEventSeeder extends Seeder
{
    public function run()
    {
        $events = [
            ['location' => 'Front Gate 2', 'time' => '12:15:00', 'image' => '/placeholder.svg?height=50&width=80'],
            ['location' => 'Front Gate 2', 'time' => '12:10:00', 'image' => '/placeholder.svg?height=50&width=80'],
            ['location' => 'Front Gate 2', 'time' => '12:05:00', 'image' => '/placeholder.svg?height=50&width=80'],
            ['location' => 'Front Gate 1', 'time' => '11:55:00', 'image' => '/placeholder.svg?height=50&width=80'],
            ['location' => 'Front Gate 1', 'time' => '11:50:00', 'image' => '/placeholder.svg?height=50&width=80'],
        ];

        foreach ($events as $event) {
            FeedEvent::create([
                'location' => $event['location'],
                'time' => Carbon::createFromTimeString($event['time']),
                'image' => $event['image'],
            ]);
        }
    }
}