@csrf
<div class="w-full mb-4">
    <label for="ip" class="block text-gray-700 font-medium">IP Address</label>
    <input type="text" id="ip" name="ip" placeholder="IP Address" value="{{ old('ip', $camera->ip ?? '') }}"
        required class="w-full p-3 border rounded">
</div>

<div class="flex gap-6 mb-4">
    <div class="w-1/2">
        <label for="brand" class="block text-gray-700 font-medium">Brand</label>
        <input type="text" id="brand" name="brand" placeholder="Brand"
            value="{{ old('brand', $camera->brand ?? '') }}" required class="w-full p-3 border rounded">
    </div>
    <div class="w-1/2">
        <label for="model" class="block text-gray-700 font-medium">Model</label>
        <input type="text" id="model" name="model" placeholder="Model"
            value="{{ old('model', $camera->model ?? '') }}" required class="w-full p-3 border rounded">
    </div>
</div>

<div>
    <label for="name" class="block text-gray-700 font-medium">Name</label>
    <input type="text" id="name" name="name" placeholder="Name"
        value="{{ old('name', $camera->name ?? '') }}" class="w-full p-3 border rounded">
</div>

<div>
    <label for="location" class="block text-gray-700 font-medium">Location</label>
    <select id="location" required class="w-full p-3 border rounded" onchange="toggleOtherLocation()">
        <option value="">Select Location</option>
        @foreach ($locations as $location)
            <option value="{{ $location }}"
                {{ old('location', $camera->location ?? '') == $location ? 'selected' : '' }}>
                {{ $location }}
            </option>
        @endforeach
        <option value="other">Other</option>
    </select>
    <input type="text" id="otherLocation" placeholder="Enter New Location"
        class="w-full p-3 border rounded mt-3 hidden">
    <!-- Hidden input to store final location value -->
    <input type="hidden" id="hiddenLocation" name="location" value="{{ old('location', $camera->location ?? '') }}">
</div>

<div class="flex gap-6">
    <div class="w-1/2">
        <label for="username" class="block text-gray-700 font-medium">Username</label>
        <input type="text" id="username" name="username" placeholder="Username"
            value="{{ old('username', $camera->username ?? '') }}" required class="w-full p-3 border rounded">
    </div>

    <div class="w-1/2">
        <label for="password" class="block text-gray-700 font-medium">Password</label>
        <input type="password" id="password" name="password" placeholder="Password"
            value="{{ old('password', $camera->password ?? '') }}" required class="w-full p-3 border rounded">
    </div>
</div>

<div>
    <label for="rtsp" class="block text-gray-700 font-medium">RTSP URL</label>
    <textarea id="rtsp" name="rtsp" placeholder="RTSP URL" required class="w-full p-3 border rounded">{{ old('rtsp', $camera->rtsp ?? '') }}</textarea>
</div>

<button type="submit" class="mt-6 w-full bg-gray-700 text-white p-3 rounded hover:bg-gray-600 transition"
    onclick="disableButtonAndSetLocation()">
    Save
</button>

<script>
    function toggleOtherLocation() {
        const locationSelect = document.getElementById('location');
        const otherLocationInput = document.getElementById('otherLocation');

        if (locationSelect.value === 'other') {
            otherLocationInput.classList.remove('hidden');
            otherLocationInput.required = true;
        } else {
            otherLocationInput.classList.add('hidden');
            otherLocationInput.required = false;
        }
    }

    function setLocationValue() {
        const locationSelect = document.getElementById('location');
        const otherLocationInput = document.getElementById('otherLocation');
        const hiddenLocationInput = document.getElementById('hiddenLocation');

        // If "Other" is selected, use the value of the other location input; otherwise, use the selected option.
        hiddenLocationInput.value = locationSelect.value === 'other' ? otherLocationInput.value : locationSelect.value;
    }

    function disableButtonAndSetLocation() {
        // Set the final location value
        setLocationValue();

        // Disable the button and change text to "Saving..."
        const saveButton = document.getElementById('saveButton');
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';

        // Optional: Add a small delay to ensure the form submission isn't blocked by the script
        setTimeout(() => document.forms[0].submit(), 50);
    }
</script>
