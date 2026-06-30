<x-filament-panels::page>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Halaman ini menampilkan akun media sosial yang tersambung dengan kredensial API Anda.
        Untuk Instagram, data diambil langsung (live) dari akun yang terhubung.
    </p>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($connections as $conn)
            <x-filament::section>
                {{-- Header: platform name + status badge --}}
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                        {{ $conn['platform'] }}
                    </h3>

                    @if ($conn['connected'])
                        <x-filament::badge color="success">Tersambung</x-filament::badge>
                    @else
                        <x-filament::badge color="gray">Belum tersambung</x-filament::badge>
                    @endif
                </div>

                {{-- Connected Instagram profile --}}
                @if ($conn['connected'] && $conn['profile'])
                    <div class="mt-4 flex items-start gap-4">
                        @if ($conn['profile']['avatar'])
                            <img
                                src="{{ $conn['profile']['avatar'] }}"
                                alt="avatar"
                                class="h-16 w-16 flex-shrink-0 rounded-full object-cover ring-2 ring-primary-500"
                            />
                        @endif

                        <div class="min-w-0">
                            <p class="truncate font-semibold text-gray-950 dark:text-white">
                                {{ $conn['profile']['name'] ?? $conn['profile']['username'] }}
                            </p>
                            @if ($conn['profile']['username'])
                                <p class="truncate text-sm text-primary-600 dark:text-primary-400">
                                    &#64;{{ $conn['profile']['username'] }}
                                </p>
                            @endif
                            @if ($conn['profile']['account_type'])
                                <p class="mt-0.5 text-xs uppercase tracking-wide text-gray-400">
                                    {{ $conn['profile']['account_type'] }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if (!empty($conn['profile']['biography']))
                        <p class="mt-3 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300">
                            {{ $conn['profile']['biography'] }}
                        </p>
                    @endif

                    @if (!empty($conn['profile']['stats']))
                        <div class="mt-4 grid grid-cols-3 gap-2 border-t border-gray-100 pt-4 dark:border-white/10">
                            @foreach ($conn['profile']['stats'] as $label => $value)
                                <div class="text-center">
                                    <p class="text-lg font-bold text-gray-950 dark:text-white">
                                        {{ number_format($value) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                {{-- Connected but no live profile (other platforms) --}}
                @elseif ($conn['connected'])
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                        Kredensial sudah diisi dan siap digunakan untuk auto-posting.
                    </p>

                {{-- Not connected / error --}}
                @else
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $conn['error'] ?? 'Belum tersambung.' }}
                    </p>
                @endif
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
