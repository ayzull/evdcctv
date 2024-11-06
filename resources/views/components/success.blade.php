{{-- resources\views\components\success.blade.php --}}
@if (session('success'))
    <div class="bg-green-100 text-green-700 p-4 rounded mb-6">
        {{ session('success') }}
    </div>
@endif
