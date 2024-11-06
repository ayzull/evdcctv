@foreach ($locationCameras as $camera)
    <div class="bg-white rounded-lg overflow-hidden shadow-md border border-gray-300">
        <div class="relative">
            <div class="w-full p-4 justify-between grid grid-cols-2">
                <h1 class="text-2xl font-bold">{{ $camera->name }}</h1>
                <div class="flex justify-end">
                    <a href="{{ route('cctv.show', $camera->id) }}">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">View
                            Info</button>
                    </a>
                </div>
            </div>
            <input type="hidden" name="webrtc-url-{{ $camera->id }}" id="webrtc-url-{{ $camera->id }}"
                value="http://localhost:8083/stream/{{ $camera->id }}/channel/0/webrtc">
            <video id="x-video-{{ $camera->id }}" autoplay muted playsinline controls
                style="max-width: 100%; max-height: 100%;">
            </video>
        </div>
    </div>
@endforeach

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
