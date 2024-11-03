<!DOCTYPE html>
<html lang="en">
@include('layouts.head')
@include('cctv.add')

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
                    <!-- Success Message -->
                    @include('components.success')
                    <!-- Validation Error Messages -->
                    @include('components.errors')
                    <h2 class="text-4xl font-bold mb-2 text-center mb-6">{{ $activeCategory }}'s Cameras</h2>
                    <!-- Check if 'All' is the active category -->
                    @if ($activeCategory === 'All')
                        @foreach ($cameras as $location => $locationCameras)
                            <div class="bg-white rounded-lg overflow-hidden shadow-md p-4 m-4">
                                <h3 class="text-4xl font-bold mb-2 text-center mb-6">{{ $location }}</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    @include('cctv.cam_card')
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($cameras as $location => $locationCameras)
                                @include('cctv.cam_card')
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <!-- Feed Sidebar -->
            @include('layouts.feedSidebar')
        </div>
    </div>
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

            @foreach ($cameras as $location => $locationCameras)
                @foreach ($locationCameras as $camera)
                    const videoEl{{ $camera->id }} = document.querySelector(
                        '#webrtc-video-{{ $camera->id }}');
                    const webrtcUrl{{ $camera->id }} = document.querySelector('#webrtc-url-{{ $camera->id }}')
                        .value;
                    startPlay(videoEl{{ $camera->id }}, webrtcUrl{{ $camera->id }});
                @endforeach
            @endforeach
        })
    </script>
</body>

</html>
