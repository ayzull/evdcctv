{{-- resources\views\layouts\nav.blade.php --}}
<nav class="bg-white border-b px-6 py-4 flex items-center justify-between">
    <h1 class="text-2xl font-bold">EVO CCTV</h1>
    <div class="flex space-x-2">
        @foreach ($categories as $category)
            <a href="{{ route('cctv.index', ['category' => $category]) }}"
                class="px-4 py-2 rounded-full text-sm font-medium 
                           {{ $activeCategory === $category ? 'bg-black text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }}">
                {{ $category }}
            </a>
        @endforeach
    </div>
    <div class="flex items-center space-x-4">
        <span class="text-sm font-medium">Feed</span>
        <svg id="feedToggleBtn" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 cursor-pointer" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </div>
</nav>
