<?php

namespace App\Console\Commands;

use App\Models\AnprEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RunTcpServer22 extends Command
{
    protected $signature = 'tcpserver:run';
    protected $description = 'Run the TCP server';

    private $maxRequestTime = 2; // Maximum time to process a single request (seconds)
    private $connectionTimeout = 1; // Connection timeout (seconds)

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $host = '0.0.0.0';
        $port = 5000;

        set_time_limit(0);

        $serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($serverSocket === false) {
            die("Socket creation failed: " . socket_strerror(socket_last_error()) . "\n");
        }

        socket_set_nonblock($serverSocket);
        socket_set_option($serverSocket, SOL_SOCKET, SO_REUSEADDR, 1);

        if (socket_bind($serverSocket, $host, $port) === false) {
            die("Binding failed: " . socket_strerror(socket_last_error($serverSocket)) . "\n");
        }

        if (socket_listen($serverSocket, 10) === false) {
            die("Listen failed: " . socket_strerror(socket_last_error($serverSocket)) . "\n");
        }

        $this->info("TCP server listening on {$host}:{$port}");

        while (true) {
            $clientSocket = @socket_accept($serverSocket);
            if ($clientSocket === false) {
                usleep(100000); // Sleep for 100ms if no connection
                continue;
            }

            socket_set_option($clientSocket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $this->connectionTimeout, 'usec' => 0]);
            socket_set_option($clientSocket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $this->connectionTimeout, 'usec' => 0]);

            $this->processClientRequest($clientSocket);
        }
    }

    private function processClientRequest($clientSocket)
    {
        $startTime = microtime(true);

        // Send immediate response to client for acknowledgement
        $response = "HTTP/1.1 200 OK\r\n\r\n";
        socket_write($clientSocket, $response, strlen($response));

        $data = '';

        // Read the incoming data
        while ((microtime(true) - $startTime) < $this->maxRequestTime) {
            $chunk = @socket_read($clientSocket, 8192);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $data .= $chunk;
        }

        if (empty($data)) {
            $this->error("No data received");
            socket_close($clientSocket);
            return;
        }

        // Extract JSON body from the HTTP request
        $jsonStart = strpos($data, '{');
        $jsonEnd = strrpos($data, '}');
        if ($jsonStart === false || $jsonEnd === false) {
            $this->error("Invalid request - JSON not found");
            socket_close($clientSocket);
            return;
        }

        $jsonString = substr($data, $jsonStart, $jsonEnd - $jsonStart + 1);
        $jsonData = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Invalid JSON: " . json_last_error_msg());
            socket_close($clientSocket);
            return;
        }

        // Process the JSON data
        $this->processJsonData($jsonData);

        socket_close($clientSocket);
        $this->info("Connection closed.");
    }

    private function processJsonData($jsonData)
    {
        try {
            $picture = $jsonData['Picture'] ?? [];
            $plate = $picture['Plate'] ?? [];
            $vehicle = $jsonData['Picture']['Vehicle'] ?? [];

            // Decode the base64 image content
            $imageContent = base64_decode($picture['NormalPic']['Content'] ?? '');
            $imageName = $picture['NormalPic']['PicName'] ?? 'unknown.jpg';

            // Extract license plate number and vehicle color
            $licensePlate = $plate['PlateNumber'] ?? 'Unknown';
            $vehicleColor = $vehicle['VehicleColor'] ?? 'Unknown';

            // Save the image
            if ($imageContent && !empty($imageName)) {
                Storage::disk('public')->put("/tcp-data/images/{$imageName}", $imageContent);
                $this->info("Saved image: {$imageName}");
            } else {
                $this->error("Failed to decode or save image.");
            }

            // Store data in the database
            $this->storeToDB($licensePlate, $vehicleColor, $imageName);

            $this->info("Processed data - Plate: {$licensePlate}, Color: {$vehicleColor}");
        } catch (\Exception $e) {
            $this->error("Error processing JSON data: " . $e->getMessage());
        }
    }

    private function storeToDB($licensePlate, $vehicleColor, $imageName)
    {
        try {
            $eventTime = now();

            // Insert the data into the database
            AnprEvent::create([
                'license_plate' => $licensePlate,
                'vehicle_color' => $vehicleColor,
                'event_time' => $eventTime,
                'image_path' => $imageName,
            ]);

            $this->info("ANPR event saved: Plate - {$licensePlate}, Color - {$vehicleColor}");
        } catch (\Exception $e) {
            $this->error("Error saving to database: " . $e->getMessage());
        }
    }
}
