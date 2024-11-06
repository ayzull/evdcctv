<!-- Floating Add Camera Button -->
<button id="openModalBtn"
    class="fixed bottom-8 right-8 bg-blue-600 hover:bg-blue-500 text-white p-4 rounded-full shadow-lg focus:outline-none transition z-50">
    + Add Camera
</button>

<!-- Modal -->
<div id="cameraModal"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4 sm:p-8">
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-md w-full max-w-md sm:max-w-lg md:max-w-xl relative">
        <button id="closeModalBtn" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 text-2xl">
            &times;
        </button>

        <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-center">Add New Camera</h2>
        <!-- Camera Form -->
        <form action="{{ route('cctv.add') }}" method="POST" class="space-y-4">
            @include('components.cctv.form')
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
