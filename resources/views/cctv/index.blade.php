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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
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

                <!-- Feed Sidebar -->
                <div id="feedSidebar" class="w-80 bg-white border-l overflow-y-auto transition-all duration-300 ease-in-out hidden">

                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold">Feed</h3>
                    </div>
                    <div class="divide-y">
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