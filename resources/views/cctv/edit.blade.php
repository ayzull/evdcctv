@include('layouts.head')
<div class="bg-white p-8 m-auto rounded-lg shadow-md max-w-lg w-full relative">
    <a href="{{ route('cctv.show', $camera) }}" class="absolute top-4 left-4">
        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Back</button>
    </a>
    <h1 class="text-2xl font-bold mb-6 text-center">Edit Camera</h1>
    <form method="POST" action="{{ route('cctv.update', $camera) }}">
        @method('PATCH')
        @include('components.cctv.form', ['camera' => $camera])
    </form>
</div>
