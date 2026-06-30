@php
    $record = $getRecord();
    $url = $record->mediaUrl();
    $isVideo = $record->isVideo();
@endphp

@if ($url && $isVideo)
    {{-- Video: show the first frame with a play overlay --}}
    <div class="relative h-12 w-12 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
        <video class="h-12 w-12 object-cover" muted preload="metadata">
            <source src="{{ $url }}#t=0.1" />
        </video>
        <span class="pointer-events-none absolute inset-0 flex items-center justify-center">
            <x-filament::icon icon="heroicon-s-play-circle" class="h-6 w-6 text-white drop-shadow" />
        </span>
    </div>
@elseif ($url)
    {{-- Image preview --}}
    <img src="{{ $url }}" alt="media" class="h-12 w-12 rounded-lg object-cover" />
@else
    {{-- No media --}}
    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
        <x-filament::icon icon="heroicon-o-photo" class="h-5 w-5 text-gray-400" />
    </div>
@endif
