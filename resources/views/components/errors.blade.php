{{-- resources\views\components\errors.blade.php --}}
@if ($errors->any())
    <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
