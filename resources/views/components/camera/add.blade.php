<!DOCTYPE html>
<html lang="en">

<body class="bg-gray-100">

    <!-- Floating Add Camera Button -->
    <button id="openModalBtn"
        class="fixed bottom-8 right-8 bg-blue-600 hover:bg-blue-500 text-white p-4 rounded-full shadow-lg focus:outline-none transition z-50">
        + Add Camera
    </button>

    <!-- Modal -->
    <div id="cameraModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-lg w-full relative">
            <button id="closeModalBtn"
                class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">
                &times;
            </button>

            <h2 class="text-2xl font-bold mb-6 text-center">Add New Camera</h2>

            <!-- Success Message -->
            @if (session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded mb-6">
                {{ session('success') }}
            </div>
            @endif

            <!-- Validation Error Messages -->
            @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Camera Form -->
            <form action="{{ route('cameras.add') }}" method="POST" class="space-y-4">
                @csrf
                <input type=" text" name="ip" placeholder="IP Address" required class="w-full p-3 border rounded">
                <input type="text" name="brand" placeholder="Brand" required class="w-full p-3 border rounded">
                <input type="text" name="model" placeholder="Model" required class="w-full p-3 border rounded">
                <input type="text" name="name" placeholder="Name" required class="w-full p-3 border rounded">
                <input type="text" name="location" placeholder="Location" required class="w-full p-3 border rounded">
                <input type="text" name="username" placeholder="Username" required class="w-full p-3 border rounded">
                <input type="password" name="password" placeholder="Password" required class="w-full p-3 border rounded">
                <input type="text" name="rtsp" placeholder="RTSP URL" required class="w-full p-3 border rounded">
                <button type="submit" class="w-full bg-gray-700 text-white p-3 rounded hover:bg-gray-600 transition">
                    Add Camera
                </button>
            </form>
        </div>
    </div>

    <script>
        // JavaScript to toggle the modal
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cameraModal = document.getElementById('cameraModal');

        openModalBtn.addEventListener('click', () => {
            cameraModal.classList.remove('hidden');
        });

        closeModalBtn.addEventListener('click', () => {
            cameraModal.classList.add('hidden');
        });

        window.addEventListener('click', (e) => {
            if (e.target === cameraModal) {
                cameraModal.classList.add('hidden');
            }
        });
    </script>

</body>

</html>