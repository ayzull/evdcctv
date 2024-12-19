<?php

namespace App\Console\Commands;

use App\Models\DahuaEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Dahuatest extends Command
{
    // Command to run the server: "php artisan Dahuatest:run"
    protected $signature = 'Dahuatest:run';
    protected $description = 'Run the TCP server';

    private $maxRequestTime = 5; // Maximum time to process a single request (seconds)
    private $connectionTimeout = 2; // Connection timeout (seconds)

    public function __construct()
    {
        parent::__construct();
    }

    // Function to set up a TCP Server
    public function handle()
    {
        $host = '0.0.0.0';
        $port = 5001;

        // Remove the PHP script's execution time limit
        set_time_limit(0);

        // Create Server Socket
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

        // Accept connections continuously
        while (true) {
            $clientSocket = @socket_accept($serverSocket);
            if ($clientSocket === false) {
                usleep(100000); // Sleep for 100ms if no connection
                continue;
            }

            socket_set_option($clientSocket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $this->connectionTimeout, 'usec' => 0]);
            socket_set_option($clientSocket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $this->connectionTimeout, 'usec' => 0]);

            $this->info("--Connection Open--");
            $this->processClientRequest($clientSocket);
        }
    }

    // Process the received client's data
    private function processClientRequest($clientSocket)
    {
        $startTime = microtime(true);

        // Send immediate HTTP response to client for acknowledgment
        $response = "HTTP/1.1 200 OK\r\n\r\n";
        socket_write($clientSocket, $response, strlen($response));

        // Read the incoming data
        $data = $this->readFromSocket($clientSocket);

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
        $jsonData = json_decode($jsonString, true); // Decode the JSON into a PHP array

        // Close the socket if invalid JSON is found
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Invalid JSON: " . json_last_error_msg());
            socket_close($clientSocket);
            return;
        }

        // Process the decoded JSON data
        $this->processJsonData($jsonData);

        // Close the socket after data processing
        socket_close($clientSocket);
        $this->info("--Connection closed--");
    }

    // Function to fully read the socket with proper timeout and content length
    private function readFromSocket($socket)
    {
        $buffer = '';
        $contentLength = null;

        while (true) {
            $chunk = @socket_read($socket, 65536, PHP_BINARY_READ); // Read 64KB chunks
            if ($chunk === false) {
                $lastError = socket_last_error($socket);
                if ($lastError === SOCKET_EWOULDBLOCK || $lastError === SOCKET_EAGAIN) {
                    usleep(50000); // Wait for 50ms and retry
                    continue;
                }
                $this->error("Socket read error: " . socket_strerror($lastError));
                break;
            }

            if ($chunk === '') {
                break; // Connection closed or no data
            }

            $buffer .= $chunk;

            // Extract Content-Length if not already set
            if ($contentLength === null && preg_match('/Content-Length: (\d+)/', $buffer, $matches)) {
                $contentLength = (int)$matches[1];
            }

            // Stop when Content-Length is reached
            if ($contentLength && strlen($buffer) >= $contentLength) {
                break;
            }
        }

        return $buffer;
    }

    // Process and store the decoded JSON data
    private function processJsonData($jsonData)
    {
        try {
            // Check for the presence of 'Picture' data and process accordingly
            $notData = $jsonData['Picture'] ?? '';
            if (!$notData) {
                $this->info("Not ANPR data, skipping...");
                return;
            }

            // Decode base64 image content
            $picture = $jsonData['Picture'] ?? [];
            $plate = $picture['Plate'] ?? [];
            $vehicle = $picture['Vehicle'] ?? [];

            // Extract image content and license plate data
            $imageContent = base64_decode($picture['NormalPic']['Content'] ?? '');
            $imageName = $picture['NormalPic']['PicName'] ?? 'unknown.jpg';

            $plateImg = base64_decode($picture['CutoutPic']['Content'] ?? '');
            $plateName = $picture['CutoutPic']['PicName'] ?? 'unknown_plate.jpg';

            // Store images in public storage
            if ($imageContent && !empty($imageName)) {
                Storage::disk('public')->put("/tcp-data/images/{$imageName}", $imageContent);
                $this->info("Saved car image: {$imageName}");
            }

            if ($plateImg && !empty($plateName)) {
                Storage::disk('public')->put("/tcp-data/images/{$plateName}", $plateImg);
                $this->info("Saved plate image: {$plateName}");
            }

            // Extract license plate details and store in DB
            $licensePlate = $plate['PlateNumber'] ?? 'Unknown';
            $confidenceLevel = $plate['Confidence'] ?? 'Unknown';
            $vehicleColor = $vehicle['VehicleColor'] ?? 'Unknown';
            $vehicleBrand = $vehicle['VehicleSign'] ?? 'Unknown';
            $vehicleType = $vehicle['VehicleType'] ?? 'Unknown';

            // Save the event to the database
            $timestamp = now()->format('YmdHis');
            $jsonFilename = "event_data_{$timestamp}.json";
            $jsonContent = json_encode($jsonData, JSON_PRETTY_PRINT);

            // Save JSON content to file
            Storage::disk('public')->put("/tcp-data/json/{$jsonFilename}", $jsonContent);
            $this->info("Saved JSON file: {$jsonFilename}");

            // Store event to database
            $this->storeToDB($licensePlate, $vehicleColor, $confidenceLevel, $vehicleBrand, $vehicleType, $imageName, $jsonFilename, $plateName);
        } catch (\Exception $e) {
            $this->error("Error processing JSON data: " . $e->getMessage());
        }
    }

    // Store the extracted data into the database
    private function storeToDB($licensePlate, $vehicleColor, $confidenceLevel, $vehicleBrand, $vehicleType, $imageName, $jsonFilename, $plateName)
    {
        try {
            DahuaEvent::create([
                'license_plate' => $licensePlate,
                'vehicle_color' => $vehicleColor,
                'confidence' => $confidenceLevel,
                'vehicle_brand' => $vehicleBrand,
                'vehicle_type' => $vehicleType,
                'event_time' => now('Asia/Kuala_Lumpur'),
                'json_path' => $jsonFilename,  // Save the JSON file path
                'license_plate_image_path'  => "{$plateName}",
                'car_image_path'  => "{$imageName}"
            ]);
            $this->info("Data stored in database.");
        } catch (\Exception $e) {
            $this->error("Error saving to database: " . $e->getMessage());
        }
    }
}
