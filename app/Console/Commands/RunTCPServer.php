<?php

namespace App\Console\Commands;

use App\Models\AnprEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RunTcpServer extends Command
{
    protected $signature = 'tcpserver:run';
    protected $description = 'Run the TCP server';

    private $maxRequestTime = 2; // Maximum time for reading data from socket
    private $connectionTimeout = 1; // Timeout for connection

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $host = '0.0.0.0'; // Listen on all interfaces
        $port = 5000; // Port number for the server

        set_time_limit(0); // Set the script to run indefinitely for the server

        // Create socket for the server
        $serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$serverSocket) {
            $this->error("Socket creation failed: " . socket_strerror(socket_last_error()));
            return;
        }

        // Set socket to non-blocking mode
        socket_set_nonblock($serverSocket);
        socket_set_option($serverSocket, SOL_SOCKET, SO_REUSEADDR, 1);

        // Bind the socket to address and port
        if (!socket_bind($serverSocket, $host, $port)) {
            $this->error("Binding failed: " . socket_strerror(socket_last_error($serverSocket)));
            return;
        }

        // Start listening for incoming connections
        if (!socket_listen($serverSocket, 10)) {
            $this->error("Listen failed: " . socket_strerror(socket_last_error($serverSocket)));
            return;
        }

        $this->info("TCP server listening on {$host}:{$port}");

        while (true) {
            // Non-blocking accept for incoming client connections
            $clientSocket = @socket_accept($serverSocket);
            if ($clientSocket) {
                $this->handleClient($clientSocket); // Handle the connection if found
            } else {
                usleep(100000); // Sleep briefly to free up CPU and allow incoming connections
            }
        }
    }

    private function handleClient($clientSocket)
    {
        socket_set_option($clientSocket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $this->connectionTimeout, 'usec' => 0]);
        socket_set_option($clientSocket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $this->connectionTimeout, 'usec' => 0]);

        $data = $this->readFromSocket($clientSocket);
        if (!$data) {
            socket_close($clientSocket); // If no data received, close the connection
            return;
        }

        $boundary = $this->extractBoundary($data);
        if (!$boundary) {
            $this->error("Invalid request - no boundary found");
            socket_close($clientSocket);
            return;
        }

        $parts = $this->parseMultipart($data, $boundary);

        $timestamp = date('YmdHis');
        foreach ($parts as $part) {
            $this->processPart($part, $timestamp); // Process each part of the multipart data
        }

        socket_close($clientSocket); // Close the client connection after handling
    }

    // Function to read data from socket until max request time is reached
    // private function readFromSocket($socket)
    // {
    //     $buffer = '';
    //     $startTime = microtime(true);

    //     // Loop until the maximum request time is reached and data is still being received.
    //     while ((microtime(true) - $startTime) < $this->maxRequestTime) {
    //         $chunk = @socket_read($socket, 65536); // Read 8KB of data at a time

    //         if ($chunk === false) {
    //             break; // If reading fails, break out
    //         }

    //         if ($chunk === '') {
    //             usleep(50000); // Sleep to allow more data to arrive
    //             continue;
    //         }

    //         $buffer .= $chunk; // Append new data to buffer

    //         // Stop reading if we detect the end of data chunk (like multipart form data)
    //         if (strpos($buffer, "\r\n\r\n") !== false && strlen($buffer) > 1024) {
    //             break; // Break if data looks complete
    //         }
    //     }

    //     return $buffer;
    // }

    private function readFromSocket($socket)
    {
        $buffer = '';
        $startTime = microtime(true);

        while ((microtime(true) - $startTime) < $this->maxRequestTime) {
            $chunk = @socket_read($socket, 65536, PHP_BINARY_READ); // Read 64KB chunks

            if ($chunk === false) {
                $lastError = socket_last_error($socket);

                // Ignore non-blocking operation error
                if ($lastError === SOCKET_EWOULDBLOCK || $lastError === SOCKET_EAGAIN) {
                    usleep(50000); // Wait and retry
                    continue;
                }

                // Other socket errors are fatal
                $this->error("Socket read error: " . socket_strerror($lastError));
                break;
            }

            if ($chunk === '') {
                usleep(50000); // Sleep briefly and retry
                continue;
            }

            $buffer .= $chunk;
        }

        return $buffer;
    }


    // Function to extract the boundary from multipart form-data
    private function extractBoundary($data)
    {
        if (preg_match('/Content-Type: multipart\/form-data; boundary=(.*)$/m', $data, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    // Function to parse multipart form-data
    private function parseMultipart($data, $boundary)
    {
        $parts = explode("--{$boundary}", $data);
        return array_filter($parts, fn($part) => strpos($part, 'Content-Disposition: form-data;') !== false);
    }

    // Process each part of the multipart data (image or XML)
    private function processPart($part, $timestamp)
    {
        if (!preg_match('/filename="(.+?)"/', $part, $matches)) {
            return; // No file part found, so return
        }

        $filename = $matches[1];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $content = substr($part, strpos($part, "\r\n\r\n") + 4); // Extract content from the part

        if (empty($content)) return; // Skip empty content

        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $uniqueFilename = pathinfo($filename, PATHINFO_FILENAME) . "_{$timestamp}.{$extension}";
            Storage::disk('public')->put("tcp-data/images/{$uniqueFilename}", $content); // Save image to storage
            $this->info("Saved image: {$uniqueFilename}");
        } elseif ($extension === 'xml') {
            $this->processXmlContent($content, $timestamp); // Process XML content
        }
    }

    // Process XML content (assumed to be ANPR data)
    private function processXmlContent($content, $timestamp)
    {
        $xmlFilename = "anpr_event_{$timestamp}.xml";
        $licensePlatePicture = "licensePlatePicture_{$timestamp}.jpg";
        $detectionPicture = "detectionPicture_{$timestamp}.jpg";

        Storage::disk('public')->put("tcp-data/xml/{$xmlFilename}", $content); // Save XML data to storage

        try {
            $xml = simplexml_load_string($content); // Parse XML content
            $plateNumber = (string) $xml->ANPR->licensePlate ?? 'Unknown';

            // Save ANPR event to database
            AnprEvent::create([
                'license_plate' => $plateNumber,
                'event_time' => now('Asia/Kuala_Lumpur'),
                'xml_path' => $xmlFilename,
                'license_plate_image_path' => $licensePlatePicture,
                'detection_image_path' => $detectionPicture,
            ]);

            $this->info("ANPR event saved: Plate - {$plateNumber}");
        } catch (\Exception $e) {
            $this->error("Error processing XML: " . $e->getMessage());
        }
    }
}
