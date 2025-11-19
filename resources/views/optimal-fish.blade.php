<x-app-layout>
    <x-container>
        <div class="py-8">
            <x-card class="max-w-4xl mx-auto">
                <x-slot name="header">
                    <x-card-header>
                        <x-slot name="title">Optimal Fish</x-slot>
                    </x-card-header>
                </x-slot>

                <livewire:classify-fish />
            </x-card>
        </div>
    </x-container>
</x-app-layout>
