{{-- resources/views/cctv/stream.blade.php --}}
<input type="hidden" name="webrtc-url-{{ $camera->id }}" id="webrtc-url-{{ $camera->id }}"
    value="http://localhost:8083/stream/{{ $camera->id }}/channel/0/webrtc">
<div class="w-full aspect-w-16 aspect-h-9 border-2 border-blue-500 shadow-lg rounded-lg overflow-hidden mb-6">
    <video id="webrtc-video-{{ $camera->id }}" autoplay muted playsinline controls
        class="w-full h-full bg-black"></video>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function startPlay(videoEl, url) {
            const webrtc = new RTCPeerConnection({
                iceServers: [{
                    urls: ['stun:stun.l.google.com:19302']
                }],
                sdpSemantics: 'unified-plan'
            });
            webrtc.ontrack = function(event) {
                console.log(event.streams.length + ' track is delivered');
                videoEl.srcObject = event.streams[0];
                videoEl.play();
            };
            webrtc.addTransceiver('video', {
                direction: 'sendrecv'
            });
            webrtc.onnegotiationneeded = async function handleNegotiationNeeded() {
                const offer = await webrtc.createOffer();
                await webrtc.setLocalDescription(offer);
                fetch(url, {
                        method: 'POST',
                        body: new URLSearchParams({
                            data: btoa(webrtc.localDescription.sdp)
                        })
                    })
                    .then(response => response.text())
                    .then(data => {
                        try {
                            webrtc.setRemoteDescription(new RTCSessionDescription({
                                type: 'answer',
                                sdp: atob(data)
                            }));
                        } catch (e) {
                            console.warn(e);
                        }
                    });
            };
            const webrtcSendChannel = webrtc.createDataChannel('rtsptowebSendChannel');
            webrtcSendChannel.onopen = (event) => {
                console.log(`${webrtcSendChannel.label} has opened`);
                webrtcSendChannel.send('ping');
            };
            webrtcSendChannel.onclose = (_event) => {
                console.log(`${webrtcSendChannel.label} has closed`);
                startPlay(videoEl, url);
            };
            webrtcSendChannel.onmessage = event => console.log(event.data);
        }

        const videoEl = document.getElementById('webrtc-video-{{ $camera->id }}');
        const webrtcUrl = document.getElementById('webrtc-url-{{ $camera->id }}').value;
        startPlay(videoEl, webrtcUrl);
    });
</script>
