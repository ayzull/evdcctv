<!DOCTYPE html>
<html lang="en">
@include('layouts.head')
@include('components.camera.add')

<body class="bg-gray-100">
    <div class="flex h-screen">

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            @include('layouts.nav')
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
                            <!-- debugging by checking output :
                              <div>Camera ID: {{ $camera }}</div>
                            -->
                                <input type="hidden" name="webrtc-url-{{$camera->id}}" id="webrtc-url-{{$camera->id}}"
                                    value="http://localhost:8083/stream/{{$camera->id}}/channel/0/webrtc">
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
                                    value="http://localhost:8083/stream/{{$camera->id}}/channel/0/webrtc">
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
        });
    </script>
        </script>
    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                function startPlay (videoEl, url) {
                    const webrtc = new RTCPeerConnection({
                    iceServers: [{
                        urls: ['stun:stun.l.google.com:19302']
                    }],
                    sdpSemantics: 'unified-plan'
                    })
                    webrtc.ontrack = function (event) {
                    console.log(event.streams.length + ' track is delivered')
                    videoEl.srcObject = event.streams[0]
                    videoEl.play()
                    }
                    webrtc.addTransceiver('video', { direction: 'sendrecv' })
                    webrtc.onnegotiationneeded = async function handleNegotiationNeeded () {
                    const offer = await webrtc.createOffer()

                    await webrtc.setLocalDescription(offer)

                    fetch(url, {
                        method: 'POST',
                        body: new URLSearchParams({ data: btoa(webrtc.localDescription.sdp) })
                    })
                        .then(response => response.text())
                        .then(data => {
                        try {
                            webrtc.setRemoteDescription(
                            new RTCSessionDescription({ type: 'answer', sdp: atob(data) })
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
                    @foreach ($locationCameras as $camera)
                        const videoEl{{ $camera->id }} = document.querySelector('#webrtc-video-{{ $camera->id }}');
                        const webrtcUrl{{ $camera->id }} = document.querySelector('#webrtc-url-{{ $camera->id }}').value;
                        startPlay(videoEl{{ $camera->id }}, webrtcUrl{{ $camera->id }});
                    @endforeach
                @endforeach


                startPlay(videoEl, webrtcUrl)
                })

    </script>
</body>

</html>