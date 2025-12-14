@section('title', 'Dashboard')
<x-app-layout>
   
    <x-container class="pt-6 sm:pt-10 py-6">
            @php
                $activeUserId = request('user') ?? session('selected_user', Auth::id());
            @endphp
        {{-- Top Filters removed: topbar now contains the user/pond search; date filtering moved above chart --}}

        <div class="grid grid-cols-12 gap-6">

            <x-card class="col-span-12">
            <x-slot name="header">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 w-full">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold text-gray-700">Water Quality Summary</h3>
                        {{-- Pond selector removed here to avoid duplicate search inputs; top filter remains --}}
                    </div>
                    {{-- Connection status moved into WaterQualityCard component --}}
                </div>
            </x-slot>

            {{-- KPI cards: mobile-first 2x2 grid, desktop single row --}}
            <div class="mt-2">
                <livewire:water-quality-card :userId="$activeUserId" wire:key="wq-card-{{ $activeUserId }}" />
            </div>

            {{-- Recommendation placeholder card (separate Livewire component)
                 Hidden temporarily — re-enable by changing the condition below to true. --}}
            @if(false)
                <div class="mt-4">
                    <livewire:recommendation-card :userId="$activeUserId" wire:key="rec-card-{{ $activeUserId }}" />
                </div>
            @endif
        </x-card>

            <x-card class="col-span-12 lg:col-span-8">
                <x-slot name="header">
                    <div class="flex items-center justify-between w-full gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-700">Range</span>
                            <div class="inline-flex items-center gap-2">
                                <button type="button" data-range="1h" class="time-range-btn inline-flex items-center px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm hover:bg-gray-50">1h</button>
                                <button type="button" data-range="24h" class="time-range-btn inline-flex items-center px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm hover:bg-gray-50">24h</button>
                                <button type="button" data-range="7d" class="time-range-btn inline-flex items-center px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm hover:bg-gray-50">7d</button>
                                <button type="button" data-range="30d" class="time-range-btn inline-flex items-center px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm hover:bg-gray-50">30d</button>
                                <div x-data="{ open:false }" class="relative">
                                    <button @click.prevent="open = !open" type="button" class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm hover:bg-gray-50">Custom ▾</button>
                                    <div x-show="open" @click.outside="open=false" x-cloak class="absolute z-50 mt-2 w-72 bg-white rounded-md shadow-lg p-3">
                                        <div class="flex flex-col gap-2">
                                            <label class="text-xs text-gray-600">Start Date</label>
                                            <input type="date" id="custom-start" class="block w-full h-10 px-3 py-2 border border-gray-200 rounded-md">
                                            <label class="text-xs text-gray-600">End Date</label>
                                            <input type="date" id="custom-end" class="block w-full h-10 px-3 py-2 border border-gray-200 rounded-md">
                                            <div class="flex items-center gap-2 mt-2">
                                                <button type="button" id="custom-apply" class="inline-flex items-center px-3 py-1.5 rounded-md bg-cvsu text-white">Apply</button>
                                                <button type="button" @click.prevent="open=false" class="inline-flex items-center px-3 py-1.5 rounded-md border border-gray-200">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">&nbsp;</div>
                    </div>
                </x-slot>

                <livewire:water-quality-chart :userId="$activeUserId" wire:key="wq-trends-{{ $activeUserId }}" />

                <script>
                    (function(){
                        function isoNowMinus(ms){ return new Date(Date.now() - ms).toISOString(); }
                        function isoNow(){ return new Date().toISOString(); }

                        document.querySelectorAll('.time-range-btn').forEach(function(btn){
                            btn.addEventListener('click', function(){
                                const r = btn.getAttribute('data-range');
                                let start=null, end=null;
                                end = isoNow();
                                if (r === '1h') start = isoNowMinus(1000*60*60);
                                else if (r === '24h') start = isoNowMinus(1000*60*60*24);
                                else if (r === '7d') start = isoNowMinus(1000*60*60*24*7);
                                else if (r === '30d') start = isoNowMinus(1000*60*60*24*30);
                                // Dispatch browser event expected by Livewire component
                                window.dispatchEvent(new CustomEvent('date-range-updated', { detail: [ start, end ] }));
                            });
                        });

                        const applyBtn = document.getElementById('custom-apply');
                        if (applyBtn){
                            applyBtn.addEventListener('click', function(){
                                const s = document.getElementById('custom-start').value;
                                const e = document.getElementById('custom-end').value;
                                if (!s || !e){
                                    alert('Please pick both start and end dates');
                                    return;
                                }
                                // send date-only strings (YYYY-MM-DD) for server-side date handling
                                window.dispatchEvent(new CustomEvent('date-range-updated', { detail: [ s, e ] }));
                                // close the dropdown via a small Alpine-friendly hack: dispatch click outside
                                document.activeElement.blur();
                            });
                        }
                    })();
                </script>
            </x-card>

            <x-card class="col-span-12 lg:col-span-4">
                <x-slot name="header">
                    <x-card-header>
                        <x-slot name="title">Recommendation</x-slot>
                    </x-card-header>
                </x-slot>

                {{-- Recommendation component (compact) --}}
                <livewire:recommendation-card :userId="$activeUserId" wire:key="rec-card-{{ $activeUserId }}" />
            </x-card>

            <x-card class="col-span-12">
                <x-slot name="header">
                    <x-card-header>
                        <x-slot name="title">Measurements</x-slot>
                    </x-card-header>
                </x-slot>
                <livewire:water-quality-data-table :userId="$activeUserId" wire:key="wq-table-{{ $activeUserId }}" />
            </x-card>
    </x-container>
</x-app-layout>
