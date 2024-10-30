<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camera Streams</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">

    <div class="container mx-auto mt-10">
        <h1 class="text-3xl font-bold mb-8 text-center">Camera Streams</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($cameras as $camera)
            <div class="bg-white rounded-lg shadow-lg p-4">
                <h2 class="text-xl font-semibold mb-4">{{ $camera->name }} ({{ $camera->location }})</h2>

                <video id="webrtc-video-{{ $camera->id }}"
                    autoplay muted playsinline
                    class="w-full h-64 bg-black rounded-md"
                    style="max-width: 100%;">
                </video>

                <input type="hidden" id="webrtc-url-{{ $camera->id }}" value="{{ $camera->rtsp }}">
            </div>
            @endforeach
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
                });

                webrtc.ontrack = function(event) {
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
                            webrtc.setRemoteDescription(new RTCSessionDescription({
                                type: 'answer',
                                sdp: atob(data)
                            }));
                        })
                        .catch(e => console.warn(e));
                };

                const webrtcSendChannel = webrtc.createDataChannel('rtsptowebSendChannel');
                webrtcSendChannel.onopen = () => console.log(`${webrtcSendChannel.label} has opened`);
                webrtcSendChannel.onclose = () => startPlay(videoEl, url);
                webrtcSendChannel.onmessage = event => console.log(event.data);
            }

            @foreach($cameras as $camera)
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
        });
    </script>

</body>

</html>