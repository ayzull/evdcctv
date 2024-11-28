<?php

namespace App\Console\Commands;

use App\Models\AnprEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Dahuatest extends Command
{

    // Command - "php artisan tcpserver:run" to run
    protected $signature = 'Dahuatest:run';
    protected $description = 'Run the TCP server';

    private $maxRequestTime = 2; // Maximum time to process a single request (seconds)
    private $connectionTimeout = 1; // Connection timeout (seconds)


    // Ensures that all necessary initializations
    public function __construct()
    {
        parent::__construct();
    }

    // Function to set up a TCP Server
    public function handle()
    {
        // set the host ip address and port they want to use for tcp listening
        $host = '0.0.0.0';
        $port = 5001;

        // Removes the PHP script's execution time limit (eg. 30sec) to continously run
        set_time_limit(0);

        // Create Server Socket object, by ipv4, tcp socket and tcp protocol
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

            $this->info("Connection Open\r\n");
            $this->processClientRequest($clientSocket);
        }
    }

   
    
    // Process the received client's data
    private function processClientRequest($clientSocket)
    {
        // start timeclock for max processing time per data received
        $startTime = microtime(true);

        // Send immediate response to client for acknowledgement
        $response = "HTTP/1.1 200 OK\r\n\r\n";
        socket_write($clientSocket, $response, strlen($response));

        // Read the incoming data
        $data = '';
        while ((microtime(true) - $startTime) < $this->maxRequestTime) {
            $chunk = @socket_read($clientSocket, 8192);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $data .= $chunk;
        }

        $this->info($data);

        // Close the socket if data received is null
        if (empty($data)) {
            $this->error("No data received");
            socket_close($clientSocket);
            return;
        }

        // Extract JSON body from the HTTP request
        $jsonStart = strpos($data, '{');
        $jsonEnd = strrpos($data, '}');
        $this->info($jsonStart,$jsonEnd);
        if ($jsonStart === false || $jsonEnd === false) {
            $this->error("Invalid request - JSON not found");
            socket_close($clientSocket);
            return;
        }

        $jsonString = substr($data, $jsonStart, $jsonEnd - $jsonStart + 1);
        $jsonData = json_decode($jsonString, true); //Decode the json into parsable php variable

        // Close the socket if no JSON body exist
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Invalid JSON: " . json_last_error_msg());
            socket_close($clientSocket);
            return;
        }

        //close the socket after data is retrieved
        socket_close($clientSocket);
        $this->info("Connection closed.");

        // Process the JSON data 
        $this->processJsonData($jsonData);
    }



    // Process the decode JSON body
    private function processJsonData($jsonData)
    {
        try {
            
            // Stop processing if it is a keep alive notice (sended by dahua)
            $keepAlive = $jsonData['Active'] ?? '';
            if ($keepAlive){
                $this->info("------------- Keep Alive -------------");    
            }
            else{

                // Extract picture, plate, and vehicle jso separately (for faster processing)
                $picture = $jsonData['Picture'] ?? [];
                $plate = $picture['Plate'] ?? [];
                $vehicle = $jsonData['Picture']['Vehicle'] ?? [];

                // Decode the base64 content to image 
                $imageContent = base64_decode($picture['NormalPic']['Content'] ?? '');
                $imageName = $picture['NormalPic']['PicName'] ?? 'unknown.jpg';

                // Extract license plate number and vehicle color
                $licensePlate = $plate['PlateNumber'] ?? 'Unknown';
                $vehicleColor = $vehicle['VehicleColor'] ?? 'Unknown';

                // Save the image to public/storage/tcp-data/images (laravel)
                if ($imageContent && !empty($imageName)) {
                    $this->info($picture['NormalPic']['Content']);
                    Storage::disk('public')->put("/tcp-data/images/{$imageName}.jpg", $imageContent);
                    $this->info("Saved image: {$imageName}");
                } else {
                    $this->error("Failed to decode or save image or save json.");
                }

                // Save the JSON file to public/tcp-data/json/
                $timestamp = now()->format('YmdHis');
                $jsonFilename = "event_data_{$timestamp}.json";
                $jsonContent = json_encode($jsonData, JSON_PRETTY_PRINT);

                if (!empty($jsonContent)) {
                    Storage::disk('public')->put("/tcp-data/json/{$jsonFilename}", $jsonContent);
                    $this->info("Saved JSON file: {$jsonFilename}");
                } else {
                    $this->error("Failed to save JSON file.");
            }


            // Store data in the database
            $this->storeToDB($licensePlate, $vehicleColor, $imageName, $jsonFilename);

            $this->info("Processed data - Plate: {$licensePlate}, Color: {$vehicleColor}");

            }


        } catch (\Exception $e) {
            $this->error("Error processing JSON data: " . $e->getMessage());
        }

    }

    // Store the extracted data to database
    private function storeToDB($licensePlate, $vehicleColor, $imageName, $jsonFilename)
    {
        try {

            
            $eventTime = now();
    
            // Insert the data into the database
            AnprEvent::create([
                'license_plate' => $licensePlate,
                //'vehicle_color' => $vehicleColor,
                'event_time' => $eventTime,
                'xml_path' => $jsonFilename,  // Save the JSON file path
                //'image_path' => $imageName,
                'license_plate_image_path'   => "test",
                'detection_image_path'   => "test"
            ]);

        } catch (\Exception $e) {
            $this->error("Error saving to database: " . $e->getMessage());
        }
    }
    

}
