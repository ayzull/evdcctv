<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCTV Monitoring System</title>
    <!-- Use CDN for Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        video {
            width: 100%;
            height: 48vh;
            background-color: black;
        }
    </style>
        <style>
        /* CSS styles for the new camera form */
        .new-camera-form {
            max-width: 400px;
            margin: 0 auto;
            padding: 2rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .new-camera-form input,
        .new-camera-form textarea {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
        }
        
        .new-camera-form button {
            width: 100%;
            padding: 0.5rem 1rem;
            background-color: #4B5563;
            color: white;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
        }
        
        .new-camera-form button:hover {
            background-color: #374151;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen">

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <nav class="bg-white border-b px-6 py-4 flex items-center justify-between">
                <h1 class="text-2xl font-bold">EVO CCTV</h1>
                <div class="flex space-x-2">
                    @foreach($categories as $category)
                    <a href="{{ route('cctv.index', ['category' => $category]) }}"
                        class="px-4 py-2 rounded-full text-sm font-medium 
                           {{ $activeCategory === $category ? 'bg-black text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }}">
                        {{ $category }}
                    </a>
                    @endforeach
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium">Feed</span>
                    <svg id="feedToggleBtn" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor">
<<<<<<< HEAD
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
=======
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
>>>>>>> 3441f4c5c313d0d8fc92fd532afb488eabacd6da
                    </svg>

                </div>
            </nav>

            <!-- Camera Grid and Feed -->
            <div class="flex-1 flex overflow-hidden">
                <!-- Camera Grid -->
                <div class="flex-1 overflow-y-auto p-6">
                    <h2 class="text-2xl font-bold mb-4">{{ $activeCategory }}</h2>

                    <!-- Check if 'All' is the active category -->
                    @if ($activeCategory === 'All')
                    @foreach ($cameras as $location => $locationCameras)

                    <h3 class="text-xl font-semibold mb-2">{{ $location }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        @foreach ($locationCameras as $camera)

<<<<<<< HEAD
                        <div class="bg-white rounded-lg overflow-hidden shadow-md">
                            <div class="relative">

                                <input type="hidden" name="webrtc-url-{{$camera->id}}" id="webrtc-url-{{$camera->id}}"
                                    value="http://localhost:8083/stream/aaa/channel/0/webrtc">
                                <video id="webrtc-video-{{$camera->id}}" autoplay muted playsinline controls
                                    style="max-width: 100%; max-height: 100%;">
                                </video>

                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4">
                                    <div class="flex items-center justify-between text-white">
                                        <span>{{ $camera->name }}</span>
                                        <span x-data x-init="$el.textContent = new Date().toLocaleTimeString()"></span>
=======
                                        <input type="hidden" name="webrtc-url-{{$camera->id}}" id="webrtc-url-{{$camera->id}}"
                                            value="http://localhost:8083/stream/aaa/channel/0/webrtc">
                                        <video id="webrtc-video-{{$camera->id}}" autoplay muted playsinline controls
                                            style="max-width: 100%; max-height: 100%;">
                                        </video>
                                        
                                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4">
                                                <div class="flex items-center justify-between text-white">
                                                    <span>{{ $camera->name }}</span>
                                                    <span x-data x-init="$el.textContent = new Date().toLocaleTimeString()"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @else
                    
                        <!-- Default behavior for other categories -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($cameras as $location => $locationCameras)
                            @foreach ($locationCameras as $camera)  
                            <!-- debugging by checking output :
                              <div>Camera ID: {{ $camera }}</div>
                            -->
                                <div class="bg-white rounded-lg overflow-hidden shadow-md">
                                    <div class="relative">
                                        <input type="hidden" name="webrtc-url-{{$camera->id}}" id="webrtc-url-{{$camera->id}}"
                                            value="http://localhost:8083/stream/aaa/channel/0/webrtc">
                                        <video id="webrtc-video-{{$camera->id}}" autoplay muted playsinline controls
                                            style="max-width: 100%; max-height: 100%;">
                                        </video>
                                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4">
                                            <div class="flex items-center justify-between text-white">
                                                <span>{{ $camera->name }}</span>
                                                <span x-data x-init="$el.textContent = new Date().toLocaleTimeString()"></span>
                                            </div>
                                        </div>
>>>>>>> 3441f4c5c313d0d8fc92fd532afb488eabacd6da
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                    @else

                    <!-- Default behavior for other categories -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($cameras as $location => $locationCameras)
                        @foreach ($locationCameras as $camera)
                        <div class="bg-white rounded-lg overflow-hidden shadow-md">
                            <div class="relative">
                                <input type="hidden" name="webrtc-url-{{$camera->id}}" id="webrtc-url-{{$camera->id}}"
                                    value="http://localhost:8083/stream/aaa/channel/0/webrtc">
                                <video id="webrtc-video-{{$camera->id}}" autoplay muted playsinline controls
                                    style="max-width: 100%; max-height: 100%;">
                                </video>
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4">
                                    <div class="flex items-center justify-between text-white">
                                        <span>{{ $camera->name }}</span>
                                        <span x-data x-init="$el.textContent = new Date().toLocaleTimeString()"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- New Camera Form -->
                <div class="new-camera-form">
                    <h2 class="text-2xl font-bold mb-4">Add New Camera</h2>
                    <form id="newCameraForm">
                        <input type="text" id="cameraName" placeholder="Camera Name" required>
                        <input type="text" id="cameraLocation" placeholder="Camera Location" required>
                        <textarea id="cameraRtspUrl" rows="3" placeholder="RTSP API URL" required></textarea>
                        <button type="submit">Add Camera</button>
                    </form>
                </div>
            </div>

                <!-- Feed Sidebar -->
                <div id="feedSidebar" class="fixed right-0 top-0 h-full w-80 bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out" style="margin-top: 73px;">

                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold">Feed</h3>
                    </div>
                    <div class="divide-y overflow-y-auto" style="height: calc(100vh - 121px);">
                        @foreach($feedEvents as $event)
                        <div class="p-4 flex items-center space-x-4">
                            <img src="{{ $event->image }}" alt="{{ $event->location }}" class="w-20 h-12 object-cover rounded">
                            <div>
                                <h4 class="font-medium">{{ $event->location }}</h4>
                                <p class="text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($event->time)->format('h:i A') }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

<<<<<<< HEAD
        <script>
            // JavaScript code to initiate WebRTC stream for each camera
            const cameras = @json($cameras); // Pass the cameras list to JavaScript

            cameras.forEach(camera => {
                // This part depends on your WebRTC signaling logic (example placeholder)
                const videoElement = document.getElementById('liveStream-' + camera.id);
                startWebRTCStream(videoElement, camera.streamUrl); // Call your WebRTC function
            });

            function startWebRTCStream(videoElement, streamUrl) {
                // WebRTC connection logic to fetch stream from signaling server
                const peerConnection = new RTCPeerConnection();

                // Placeholder signaling server logic
                // In reality, use WebSocket or another signaling method to exchange SDP offers/answers
                fetch(streamUrl)
                    .then(response => response.json())
                    .then(streamData => {
                        peerConnection.setRemoteDescription(new RTCSessionDescription(streamData));
                        peerConnection.createAnswer().then(answer => {
                            peerConnection.setLocalDescription(answer);
                        });
                    });

                peerConnection.ontrack = (event) => {
                    videoElement.srcObject = event.streams[0];
                };
            }
            document.addEventListener('DOMContentLoaded', function() {
                const feedToggleBtn = document.getElementById('feedToggleBtn');
                const feedSidebar = document.getElementById('feedSidebar');

                feedToggleBtn.addEventListener('click', function() {
                    feedSidebar.classList.toggle('hidden');
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                function startPlay(videoEl, url) {
=======
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const feedToggleBtn = document.getElementById('feedToggleBtn');
            const feedSidebar = document.getElementById('feedSidebar');
            let isOpen = false;
            
            feedToggleBtn.addEventListener('click', function() {
                isOpen = !isOpen;
                if (isOpen) {
                    feedSidebar.classList.add('open');
                    feedSidebar.style.transform = 'translateX(0)';
                } else {
                    feedSidebar.classList.remove('open');
                    feedSidebar.style.transform = 'translateX(100%)';
                }
            });
            
            // Close sidebar when clicking outside
            document.addEventListener('click', function(event) {
                if (isOpen && 
                    !feedSidebar.contains(event.target) && 
                    !feedToggleBtn.contains(event.target)) {
                    isOpen = false;
                    feedSidebar.classList.remove('open');
                    feedSidebar.style.transform = 'translateX(100%)';
                }
            });

                        // New camera form submission
                        const newCameraForm = document.getElementById('newCameraForm');
            newCameraForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                const cameraName = document.getElementById('cameraName').value;
                const cameraLocation = document.getElementById('cameraLocation').value;
                const cameraRtspUrl = document.getElementById('cameraRtspUrl').value;

                try {
                    const response = await fetch('http://127.0.0.1:8083/stream/abc123/add', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Basic ' + btoa('demo:demo'), // Encode the username and password
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            name: cameraName,
                            channels: {
                                '0': {
                                    name: cameraName,
                                    url: cameraRtspUrl,
                                    on_demand: true,
                                    debug: false
                                }
                            }
                        })
                    });

                    const data = await response.json();
                    console.log('API response:', data);

                    if (data.status === 1) {
                        // Camera added successfully, save to database
                        const videoEndpoint = `http://127.0.0.1:8083/stream/abc123/channel/0/webrtc`;
                        await saveCameraToDatabase(cameraName, cameraLocation, videoEndpoint);
                        alert('Camera added successfully!');
                    } else if (data.status === 0 && data.payload === 'stream already exists') {
                        alert('The stream already exists'); 
                    } else {
                        alert('Failed to add camera. Please try again.');
                    }
                } catch (error) {
                    console.error('Error adding camera:', error);
                    alert('An error occurred while adding the camera. Please try again later.');
                }
            });

            async function saveCameraToDatabase(name, location, videoEndpoint) {
                // Code to save camera to the database
                // You'll need to implement this based on your database setup
                console.log('Saving camera to database:', { name, location, videoEndpoint });
            }
   

        });

        
    </script>
    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                function startPlay (videoEl, url) {
>>>>>>> 3441f4c5c313d0d8fc92fd532afb488eabacd6da
                    const webrtc = new RTCPeerConnection({
                        iceServers: [{
                            urls: ['stun:stun.l.google.com:19302']
                        }],
                        sdpSemantics: 'unified-plan'
                    })
                    webrtc.ontrack = function(event) {
                        console.log(event.streams.length + ' track is delivered')
                        videoEl.srcObject = event.streams[0]
                        videoEl.play()
                    }
                    webrtc.addTransceiver('video', {
                        direction: 'sendrecv'
                    })
                    webrtc.onnegotiationneeded = async function handleNegotiationNeeded() {
                        const offer = await webrtc.createOffer()

                        await webrtc.setLocalDescription(offer)

                        fetch(url, {
                                method: 'POST',
                                body: new URLSearchParams({
                                    data: btoa(webrtc.localDescription.sdp)
                                })
                            })
                            .then(response => response.text())
                            .then(data => {
                                try {
                                    webrtc.setRemoteDescription(
                                        new RTCSessionDescription({
                                            type: 'answer',
                                            sdp: atob(data)
                                        })
                                    )
                                } catch (e) {
                                    console.warn(e)
                                }
                            })
                    }

                    const webrtcSendChannel = webrtc.createDataChannel('rtsptowebSendChannel')
                    webrtcSendChannel.onopen = (event) => {
                        console.log(`${webrtcSendChannel.label} has opened`)
                        webrtcSendChannel.send('ping')
                    }
                    webrtcSendChannel.onclose = (_event) => {
                        console.log(`${webrtcSendChannel.label} has closed`)
                        startPlay(videoEl, url)
                    }
                    webrtcSendChannel.onmessage = event => console.log(event.data)
                }

                @foreach($cameras as $location => $locationCameras)
                @foreach($locationCameras as $camera)
                const videoEl {
                    {
                        $camera - > id
                    }
                } = document.querySelector('#webrtc-video-{{ $camera->id }}');
                const webrtcUrl {
                    {
                        $camera - > id
                    }
                } = document.querySelector('#webrtc-url-{{ $camera->id }}').value;
                startPlay(videoEl {
                    {
                        $camera - > id
                    }
                }, webrtcUrl {
                    {
                        $camera - > id
                    }
                });
                @endforeach
                @endforeach


                startPlay(videoEl, webrtcUrl)
            })
        </script>
</body>

</html>