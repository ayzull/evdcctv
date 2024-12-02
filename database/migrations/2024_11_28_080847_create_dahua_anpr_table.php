<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dahua_anpr', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate')->nullable();
            $table->string('confidence')->nullable();
            $table->string('vehicle_brand')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->string('json_path')->nullable();
            $table->string('license_plate_image_path')->nullable();
            $table->string('car_image_path')->nullable();
            $table->timestamp('event_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dahua_anpr');
    }
};
