{{-- resources\views\layouts\feedSidebar.blade.php --}}
<div id="feedSidebar"
    class="fixed right-0 top-0 h-full w-80 bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out"
    style="margin-top: 73px;">
    <div class="p-4 border-b">
        <h3 class="text-lg font-semibold">Feed</h3>
        <a href="{{ route('anpr.index') }}">View ANPR Events</a>
    </div>
    <div class="divide-y overflow-y-auto" style="height: calc(100vh - 121px);">
        @foreach ($feedEvents as $event)
            <div class="p-4 flex items-center space-x-4">
                <img src="{{ asset('storage/tcp-data/images/' . $event->license_plate_image_path) }}"
                    class="w-20 h-12 object-contain rounded">
                <div>
                    <h4 class="font-medium">{{ $event->license_plate }}</h4>
                    <p class="text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
                    </p>
                </div>
            </div>
        @endforeach
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
