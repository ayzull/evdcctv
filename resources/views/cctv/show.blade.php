{{-- resources/views/cctv/show.blade.php --}}
<!DOCTYPE html>
<html lang="en">
@include('layouts.head')

<body class="bg-gray-100 p-6">
    @include('components.success')
    @include('components.errors')
    <div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-800">{{ $camera->location }} | {{ $camera->name }}</h2>
            <a href="{{ route('cctv.index') }}">
                <button
                    class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md shadow hover:bg-blue-700 transition duration-150">Back</button>
            </a>
        </div>

        {{-- <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8"> --}}
        <div class="grid grid-rows-1 md:grid-rows-1 gap-6 mb-8">
            @include('anpr.analytic')
            {{-- Video Stream Section --}}
            {{-- <div class="border-2 border-blue-500 rounded-lg shadow-lg overflow-hidden"> --}}
            @include('cctv.stream', ['camera' => $camera])
            {{-- </div> --}}

            {{-- Camera Information Section --}}
            <div class="bg-gray-50 p-6 rounded-lg shadow">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Camera Information</h3>
                <div class="text-gray-700">
                    <div class="flex gap-6 mb-4">
                        <div class="w-1/2">
                            <span class="font-semibold">ID:</span> <span>{{ $camera->id }}</span>
                        </div>
                        <div class="w-1/2">
                            <span class="font-semibold">IP:</span> <span>{{ $camera->ip }}</span>
                        </div>
                    </div>
                    <div class="flex gap-6 mb-4">
                        <div class="w-1/2"><span class="font-semibold">Brand:</span> <span>{{ $camera->brand }}</span>
                        </div>
                        <div class="w-1/2"><span class="font-semibold">Model:</span> <span>{{ $camera->model }}</span>
                        </div>
                    </div>
                    <div class="flex gap-6 mb-4">
                        <div class="w-1/2"><span class="font-semibold">Name:</span> <span>{{ $camera->name }}</span>
                        </div>
                        <div class="w-1/2"><span class="font-semibold">Location:</span>
                            <span>{{ $camera->location }}</span>
                        </div>
                    </div>
                    <div class="flex gap-6 mb-4">
                        <div class="w-1/2"><span class="font-semibold">Username:</span>
                            <span>{{ $camera->username }}</span>
                        </div>
                        <div class="w-1/2"><span class="font-semibold">Password:</span>
                            <span>{{ $camera->password }}</span>
                        </div>
                    </div>
                    <div class="flex gap-6 mb-4">
                        <span class="font-semibold">RTSP URL:</span>
                        <span class="break-all text-blue-600">{{ $camera->rtsp }}</span>
                    </div>
                    <div class="flex gap-6 mb-4">
                        <div class="w-1/2"><span class="font-semibold">Created At:</span>
                            <span>{{ $camera->created_at }}</span>
                        </div>
                        <div class="w-1/2"><span class="font-semibold">Updated At:</span>
                            <span>{{ $camera->updated_at }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6 space-x-4">
                    <form method="POST" action="{{ route('cctv.delete', $camera->id) }}"
                        onsubmit="return confirm('Are you sure you want to delete this camera?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white font-semibold rounded-md shadow hover:bg-red-700 transition duration-150">Delete</button>
                    </form>

                    <a href="{{ route('cctv.edit', $camera->id) }}">
                        <button
                            class="px-4 py-2 bg-green-600 text-white font-semibold rounded-md shadow hover:bg-green-700 transition duration-150">Edit</button>
                    </a>
                </div>
            </div>
        </div>

        {{-- ANPR Events Section --}}
        @if ($camera->name == 'ANPR')
            <h3 class="text-2xl font-bold text-gray-800 mb-4">ANPR Detection Events</h3>
            <div class="overflow-x-auto">
                @include('anpr.index', ['events' => $events])
            </div>
        @endif
    </div>
</body>

</html>
