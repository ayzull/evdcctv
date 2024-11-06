<?php

namespace App\Http\Controllers;

use App\Models\AnprEvent;
use App\Models\Camera;
use App\Models\FeedEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CCTVController extends Controller
{
    public function index(Request $request)
    {
        $activeCategory = $request->query('category', 'All');
        $categories = Camera::distinct('location')->pluck('location')->toArray();
        array_unshift($categories, 'All');
        sort($categories);
        $locations = Camera::distinct()->pluck('location');

        // Fetch cameras grouped by location when 'All' is selected
        // Always group cameras by location for consistent data structure
        if ($activeCategory === 'All') {
            $cameras = Camera::all()->groupBy('location');
        } else {
            // Group the filtered cameras by location as well
            $cameras = Camera::where('location', $activeCategory)
                ->get()
                ->groupBy('location');
        }

        $feedEvents = AnprEvent::latest('event_time')->take(5)->get();
        //dd($cameras);
        return view('cctv.index', compact('categories', 'cameras', 'feedEvents', 'activeCategory', 'locations'));
    }

    public function stream()
    {
        $cameras = Camera::all();  // Fetch all cameras

        $cameraStreams = $cameras->map(function ($camera) {
            return [
                'id' => $camera->id,
                'name' => $camera->name,
                'rtsp_url' => $camera->rtsp,
            ];
        });

        return response()->json($cameraStreams);
    }

    public function create()
    {
        return view('components.camera.add');
    }

    public function add(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'ip' => 'required|ipv4',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:50',
            'rtsp' => 'required|string',
        ]);

        // Create the camera record in the database
        $camera = Camera::create($validated);

        // Define the API URL and stream ID
        $streamId = $camera->id; // Assuming the camera ID is the stream ID
        $urlToSend = $validated['rtsp'];
        // dd($urlToSend);
        $apiUrl = "http://demo:demo@127.0.0.1:8083/stream/{$streamId}/add";
        //dd($apiUrl);


        // Prepare the API request data
        $apiData = [
            "name" => $validated['name'],
            "channels" => (object) [
                "0" => [
                    "name" => "ch1", // Set a specific name for the channel
                    "url" => $urlToSend,
                    "on_demand" => true,
                    "debug" => false,

                ], //

            ],
        ];

        // Log the API data to check its structure
        Log::info('API Request Data', ['apiData' => $apiData]);

        // Make the API call
        $apiResponse = $this->callApi($apiUrl, $apiData, 'POST');

        // Handle API response
        if ($apiResponse['status'] === 1) {
            return redirect()->back()->with('success', 'Camera added successfully!');
        } else {
            Log::error('Failed to add camera to media server', [
                'api_response' => $apiResponse,
            ]);
            return redirect()->back()->withErrors(['Failed to add camera to the media server: ' . json_encode($apiResponse)]);
        }
    }

    // Method to call the media server API
    private function callApi($url, $data, $method = 'POST')
    {
        // Log the URL and request data
        Log::info('Calling API', [
            'url' => $url,
            'data' => $data,
        ]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else if ($method == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        } else if ($method == 'UPDATE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        // Log the response and error
        Log::info('API Response', [
            'response' => $response,
            'error' => $error,
        ]);

        if ($error) {
            // Return the error in the response
            return ['status' => 0, 'payload' => 'Error communicating with the API: ' . $error];
        }

        return json_decode($response, true);
    }

    public function show($cameraId) #
    {
        $camera = Camera::findOrFail($cameraId);
        #TODO ADD camera_id in ANPR EVENT TABLE DATABASE 
        $events = AnprEvent::orderBy('event_time', 'desc')->paginate(10);
        return view('cctv.show', compact('camera', 'events'));
    }

    public function edit(Camera $camera)
    {
        $locations = Camera::distinct()->pluck('location');
        return view('cctv.edit', compact('camera'), compact('locations'));
    }

    public function update(Request $request, Camera $camera)
    {

        $validated = $request->validate([
            'ip' => 'required|ipv4',
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:50',
            'rtsp' => 'required|string',
        ]);

        $newRTSP = $validated['rtsp'];

        $apiUrl = "http://demo:demo@127.0.0.1:8083/stream/{$camera->id}/edit";

        $apiData = [
            "name" => $validated['name'],
            "channels" => (object) [
                "0" => [
                    "name" => $camera->name, // Set a specific name for the channel
                    "url" => $newRTSP,
                    "on_demand" => true,
                    "debug" => false,
                ], // Use string key for the channel

            ],
        ];

        $response = $this->callApi($apiUrl, $apiData, 'UPDATE');

        if (isset($response['status']) && $response['status'] === 0) {
            return redirect()->route('cctv.show')->with('error', $response['payload']);
        }

        $camera->update($validated);
        return redirect()->route('cctv.show', compact('camera'))->with('success', 'Camera updated successfully!');
    }

    public function delete(Camera $camera)
    {
        $apiUrl = "http://demo:demo@127.0.0.1:8083/stream/{$camera->id}/delete";
        $response = $this->callApi($apiUrl, $camera, 'DELETE');

        if (isset($response['status']) && $response['status'] === 0) {
            return redirect()->route('cctv.index')->with('error', $response['payload']);
        }

        $camera->delete();
        return redirect()->route('cctv.index')->with('success', 'Camera deleted successfully!');
    }
}
