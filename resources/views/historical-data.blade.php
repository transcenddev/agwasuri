<x-app-layout>
    <x-container class="pt-20">
        <div class="py-12">
            <x-card>
                <x-slot name="header">
                    <x-card-header>
                        <x-slot name="title">Historical Data</x-slot>
                    </x-card-header>
                </x-slot>

                <livewire:water-quality-data-table />
            </x-card>
        </div>
    </x-container>
</x-app-layout>
