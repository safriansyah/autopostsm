<x-filament-panels::page>
    <div x-data="{ activeTab: 'twitter' }" class="space-y-6">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Kelola kredensial API tiap platform. Pilih platform di bawah, isi kredensialnya, lalu simpan.
            Aktifkan <span class="font-medium">Auto-post</span> agar platform tersebut diikutkan saat penjadwalan.
        </p>

        <div class="overflow-x-auto">
            <x-filament::tabs>
                <x-filament::tabs.item alpine-active="activeTab === 'twitter'" x-on:click="activeTab = 'twitter'" icon="heroicon-m-hashtag">
                    Twitter (X)
                </x-filament::tabs.item>
                <x-filament::tabs.item alpine-active="activeTab === 'facebook'" x-on:click="activeTab = 'facebook'" icon="heroicon-m-users">
                    Facebook
                </x-filament::tabs.item>
                <x-filament::tabs.item alpine-active="activeTab === 'linkedin'" x-on:click="activeTab = 'linkedin'" icon="heroicon-m-briefcase">
                    LinkedIn
                </x-filament::tabs.item>
                <x-filament::tabs.item alpine-active="activeTab === 'instagram'" x-on:click="activeTab = 'instagram'" icon="heroicon-m-camera">
                    Instagram
                </x-filament::tabs.item>
                <x-filament::tabs.item alpine-active="activeTab === 'tiktok'" x-on:click="activeTab = 'tiktok'" icon="heroicon-m-musical-note">
                    TikTok
                </x-filament::tabs.item>
                <x-filament::tabs.item alpine-active="activeTab === 'whatsapp'" x-on:click="activeTab = 'whatsapp'" icon="heroicon-m-chat-bubble-left-right">
                    WhatsApp
                </x-filament::tabs.item>
            </x-filament::tabs>
        </div>

        {{-- Twitter (x.com) --}}
        <div x-show="activeTab === 'twitter'" x-cloak>
            <x-filament-panels::form wire:submit="saveTwitter">
                {{ $this->twitterForm }}
                <x-filament-panels::form.actions :actions="$this->getTwitterActions()" />
            </x-filament-panels::form>
        </div>

        {{-- Facebook --}}
        <div x-show="activeTab === 'facebook'" x-cloak>
            <x-filament-panels::form wire:submit="saveFacebook">
                {{ $this->facebookForm }}
                <x-filament-panels::form.actions :actions="$this->getFacebookActions()" />
            </x-filament-panels::form>
        </div>

        {{-- LinkedIn --}}
        <div x-show="activeTab === 'linkedin'" x-cloak>
            <x-filament-panels::form wire:submit="saveLinkedin">
                {{ $this->linkedinForm }}
                <x-filament-panels::form.actions :actions="$this->getLinkedinActions()" />
            </x-filament-panels::form>
        </div>

        {{-- Instagram --}}
        <div x-show="activeTab === 'instagram'" x-cloak>
            <x-filament-panels::form wire:submit="saveInstagram">
                {{ $this->instagramForm }}
                <x-filament-panels::form.actions :actions="$this->getInstagramActions()" />
            </x-filament-panels::form>
        </div>

        {{-- TikTok --}}
        <div x-show="activeTab === 'tiktok'" x-cloak>
            <x-filament-panels::form wire:submit="saveTiktok">
                {{ $this->tiktokForm }}
                <x-filament-panels::form.actions :actions="$this->getTiktokActions()" />
            </x-filament-panels::form>
        </div>

        {{-- WhatsApp --}}
        <div x-show="activeTab === 'whatsapp'" x-cloak>
            <x-filament-panels::form wire:submit="saveWhatsapp">
                {{ $this->whatsappForm }}
                <x-filament-panels::form.actions :actions="$this->getWhatsappActions()" />
            </x-filament-panels::form>
        </div>

        <x-filament-actions::modals />
    </div>
</x-filament-panels::page>
