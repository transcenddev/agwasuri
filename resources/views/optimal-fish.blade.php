<x-app-layout>
    <x-container class="pt-20">
        <x-card class="mb-6">
                <x-slot name="header">
                    <x-card-header>
                        <x-slot name="title">Optimal Fish</x-slot>
                    </x-card-header>
                </x-slot>

                <livewire:classify-fish />
        </x-card>
    </x-container>
</x-app-layout>
