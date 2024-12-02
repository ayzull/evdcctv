{{-- resources/views/anpr/index.blade.php --}}
@include('layouts.head')
<div class="container">
    <table class="table-auto w-full bg-white border border-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 border">ID</th>
                <th class="px-4 py-2 border">License Plate</th>
                <th class="px-4 py-2 border">Confidence Level</th>
                <th class="px-4 py-2 border">Brand</th>
                <th class="px-4 py-2 border">Type</th>
                <th class="px-4 py-2 border">Color</th>
                <th class="px-4 py-2 border">Event Time</th>
                <th class="px-4 py-2 border">JSON Path</th>
                <th class="px-4 py-2 border">License Plate Image</th>
                <th class="px-4 py-2 border">Vehicle Image</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dahua_events as $event)
                <tr>
                    <td class="border px-4 py-2">{{ $event->id }}</td>
                    <td class="border px-4 py-2">{{ $event->license_plate }}</td>
                    <td class="border px-4 py-2">{{ $event->confidence }}</td>
                    <td class="border px-4 py-2">{{ $event->vehicle_brand }}</td>
                    <td class="border px-4 py-2">{{ $event->vehicle_type }}</td>
                    <td class="border px-4 py-2">{{ $event->vehicle_color }}</td>
                    <td class="border px-4 py-2">{{ $event->event_time }}</td>
                    <td class="border px-4 py-2">
                        <a href="{{ asset('storage/tcp-data/json/' . $event->json_path) }}" target="_blank"
                            class="text-blue-600">View</a>
                    </td>
                    <td class="border px-4 py-2">
                        <img src="{{ asset('storage/tcp-data/images/' . $event->license_plate_image_path) }}"
                            alt="License Plate" width="100">
                    </td>
                    <td class="border px-4 py-2">
                        <img src="{{ asset('storage/tcp-data/images/' . $event->car_image_path) }}" alt="Detection"
                            width="100">
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="border text-center px-4 py-2">No events found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">
        {{ $dahua_events->links() }}
    </div>
</div>
