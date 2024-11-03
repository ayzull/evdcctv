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
            <video id="webrtc-video-{{ $camera->id }}" autoplay muted playsinline controls
                style="max-width: 100%; max-height: 100%;">
            </video>
        </div>
    </div>
@endforeach
