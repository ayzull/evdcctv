{{-- resources/views/anpr/index.blade.php --}}
<div class="container">
    <table class="table-auto w-full bg-white border border-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 border">ID</th>
                <th class="px-4 py-2 border">License Plate</th>
                <th class="px-4 py-2 border">Event Time</th>
                <th class="px-4 py-2 border">XML Path</th>
                <th class="px-4 py-2 border">License Plate Image</th>
                <th class="px-4 py-2 border">Detection Image</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($events as $event)
                <tr>
                    <td class="border px-4 py-2">{{ $event->id }}</td>
                    <td class="border px-4 py-2">{{ $event->license_plate }}</td>
                    <td class="border px-4 py-2">{{ $event->event_time }}</td>
                    <td class="border px-4 py-2">
                        <a href="{{ asset('storage/tcp-data/xml/' . $event->xml_path) }}" target="_blank"
                            class="text-blue-600">{{ $event->xml_path }}</a>
                    </td>
                    <td class="border px-4 py-2">
                        <img src="{{ asset('storage/tcp-data/images/' . $event->license_plate_image_path) }}"
                            alt="License Plate" width="100">
                    </td>
                    <td class="border px-4 py-2">
                        <img src="{{ asset('storage/tcp-data/images/' . $event->detection_image_path) }}"
                            alt="Detection" width="100">
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
        {{ $events->links() }}
    </div>
</div>
