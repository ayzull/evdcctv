<?php

namespace App\Console\Commands;
// app\Console\Commands\RunTCPServer.php
use App\Models\AnprEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RunTcpServer extends Command
{
    // Command - "php artisan tcpserver:run" to run
    protected $signature = 'tcpserver:run';
    protected $description = 'Run the TCP server';

    private $maxRequestTime = 2; // Maximum time to process a single request (seconds)
    private $connectionTimeout = 1; // Connection timeout (seconds)


    // Ensures that all necessary initializations
    //note: seem okay to delete (does not affect process)(test more)
    public function __construct()
    {
        parent::__construct();
    }

    // setting up a TCP server
    public function handle()
    {
        $host = '0.0.0.0';
        $port = 5000;
        

        // Removes the PHP script's execution time limit (eg. 30sec) to continously run
        set_time_limit(0);

        // Create Server Socket object, by ipv4, tcp socket and tcp protocol
        $serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($serverSocket === false) {
            // Error Handling
            die("Socket creation failed: " . socket_strerror(socket_last_error()) . "\n");
        }

        // Enable non-blocking mode
        socket_set_nonblock($serverSocket);
        socket_set_option($serverSocket, SOL_SOCKET, SO_REUSEADDR, 1);

        // Bind to IP and port and return error if failed
        if (socket_bind($serverSocket, $host, $port) === false) {
            die("Binding failed: " . socket_strerror(socket_last_error($serverSocket)) . "\n");
        }

        // Listen to incoming client and return error if failed, set max pending connection to 10
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

            // Set socket options (data receive and sent timeouts) for the client
            socket_set_option($clientSocket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $this->connectionTimeout, 'usec' => 0]);
            socket_set_option($clientSocket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $this->connectionTimeout, 'usec' => 0]);

            // Process client request with timeout
            $this->processClientRequest($clientSocket);
        }
    }

    // Process received data
    private function processClientRequest($clientSocket)
    {
        $startTime = microtime(true);

        // Send immediate response to client for acknowledgement
        $response = "HTTP/1.1 200 OK\r\n\r\n";
        socket_write($clientSocket, $response, strlen($response));

        $data = '';
        $headerComplete = false;
        $boundary = null;
        $isComplete = false;

        // Read headers first
        while (!$headerComplete && (microtime(true) - $startTime) < $this->maxRequestTime) {
            $chunk = @socket_read($clientSocket, 4096);
            if ($chunk === false || $chunk === '') {
                break;
            }
            //$this->info("chunk, {$chunk}")
            $data .= $chunk;
            $this->info("---------------space-------------");
            
            // Check for complete headers
            if (strpos($data, "\r\n\r\n") !== false) {
                $headerComplete = true;

                // Extract boundary
                if (preg_match('/Content-Type: multipart\/form-data; boundary=(.*)$/m', $data, $matches)) {
                    $boundary = trim($matches[1]);
                    break;
                }
            }
        }
        
       

        if (!$boundary) {
            $this->error("Invalid request - no boundary found");
            socket_close($clientSocket);
            return;
        }


        $this->info("data, {$data} -------------HIII!-----------");
        $this->info("-------------BOUNDARY----------- \r\n {$boundary} \r\n-------------BOUNDARY-----------");


        // Read body with boundary detection
        $endMarker = "--$boundary--";
        while ((microtime(true) - $startTime) < $this->maxRequestTime) {
            $chunk = @socket_read($clientSocket, 8192);
            if ($chunk === false || $chunk === '') {
                break;
            }

            $data .= $chunk;

            // Check if we've received the complete multipart data
            if (strpos($data, $endMarker) !== false) {
                $isComplete = true;
                break;
            }
        }
        //$this->info("-------------DATA DONE-----------\r\n {$data} \r\n-------------DATA DONE-----------");

        if (!$isComplete) {
            // $this->error("Incomplete data received");
            socket_close($clientSocket);
            return;
        }

        // Process the complete data
        $timestamp = date('YmdHis');
        $parts = explode("--" . $boundary, $data);

        foreach ($parts as $part) {
            if (empty($part) || strpos($part, 'Content-Disposition: form-data;') === false) {
                continue;
            }

            // Extract filename and content type
            if (preg_match('/filename="(.+?)"/', $part, $matches)) {
                $filename = $matches[1];
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                // Get file content
                $content = substr($part, strpos($part, "\r\n\r\n") + 4);
                if (!empty($content)) {
                    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        $uniqueFilename = pathinfo($filename, PATHINFO_FILENAME) . "_{$timestamp}.$extension";
                        Storage::disk('public')->put('/tcp-data/images/' . $uniqueFilename, $content);
                        $this->info("Saved image: $uniqueFilename");
                    } elseif ($extension === 'xml') {
                        $xmlFilename = "anpr_event_{$timestamp}.xml";
                        $licensePlatePicture = "licensePlatePicture_{$timestamp}.jpg";
                        $detectionPicture = "detectionPicture_{$timestamp}.jpg";
                        Storage::disk('public')->put('/tcp-data/xml/' . $xmlFilename, $content);
                        $this->info("Saved XML: $xmlFilename");

                        $this->storeToDB($content, $xmlFilename, $licensePlatePicture, $detectionPicture);
                    }
                }
            }
        }

        // Properly close the connection
        socket_close($clientSocket);
        $this->info("Connection closed.");
    }

    private function storeToDB($xmlContent, $xmlFilename, $licensePlatePicture, $detectionPicture)
    {
        try {
            $xml = simplexml_load_string($xmlContent);

            // Extract relevant data from XML (customize based on your XML structure)
            $plateNumber = (string) $xml->ANPR->licensePlate ?? 'Unknown';
            $eventTime = (string) now();

            // Insert the data into the database
            AnprEvent::create([
                'license_plate' => $plateNumber,
                'event_time'   => $eventTime,
                'xml_path'   => $xmlFilename,
                'license_plate_image_path'   => $licensePlatePicture,
                'detection_image_path'   => $detectionPicture
            ]);

            $this->info("ANPR event saved: Plate - $plateNumber");
        } catch (\Exception $e) {
            $this->error("Error processing XML: " . $e->getMessage());
        }
    }
}
