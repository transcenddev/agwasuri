<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-2xl shadow-xl p-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Manual Data Entry</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Input water quality readings manually (for testing without IoT device)</p>
        </div>

        <!-- Success Message -->
        @if($successMessage)
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="@this.on('success-message-shown', () => { show = true; setTimeout(() => show = false, 3000) })"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-800 dark:text-green-400 font-medium">✓ {{ $successMessage }}</p>
            </div>
        @endif

        <form wire:submit.prevent="submit" class="space-y-6">
            <!-- Temperature -->
            <div>
                <label for="temperature" class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Temperature (°C)
                </label>
                <input type="number" step="0.01" id="temperature" wire:model="temperature" 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-cvsu dark:focus:ring-cvsu-400 focus:border-transparent transition">
                @error('temperature') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Typical range: 25-32°C</p>
            </div>

            <!-- pH Level -->
            <div>
                <label for="ph_level" class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    pH Level
                </label>
                <input type="number" step="0.01" id="ph_level" wire:model="ph_level" 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-cvsu dark:focus:ring-cvsu-400 focus:border-transparent transition">
                @error('ph_level') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Typical range: 6.5-8.5</p>
            </div>

            <!-- Dissolved Oxygen -->
            <div>
                <label for="dissolved_oxygen" class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Dissolved Oxygen (mg/L)
                </label>
                <input type="number" step="0.01" id="dissolved_oxygen" wire:model="dissolved_oxygen" 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-cvsu dark:focus:ring-cvsu-400 focus:border-transparent transition">
                @error('dissolved_oxygen') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Typical range: 4.0-7.5 mg/L</p>
            </div>

            <!-- Salinity -->
            <div>
                <label for="salinity" class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Salinity (ppt)
                </label>
                <input type="number" step="0.01" id="salinity" wire:model="salinity" 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-cvsu dark:focus:ring-cvsu-400 focus:border-transparent transition">
                @error('salinity') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Typical range: 0-35 ppt</p>
            </div>

            <!-- Recorded At -->
            <div>
                <label for="recorded_at" class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                    Date & Time
                </label>
                <input type="datetime-local" id="recorded_at" wire:model="recorded_at" 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-cvsu dark:focus:ring-cvsu-400 focus:border-transparent transition">
                @error('recorded_at') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4 pt-4">
                <button type="submit" 
                    class="flex-1 px-6 py-3 bg-cvsu hover:bg-cvsu-600 dark:bg-cvsu-400 dark:hover:bg-cvsu-500 text-white font-medium rounded-lg transition duration-200 shadow-lg hover:shadow-xl">
                    Save Reading
                </button>
                
                <button type="button" wire:click="generateRandomData"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100 font-medium rounded-lg transition duration-200">
                    🎲 Random
                </button>
            </div>
        </form>

        <!-- Helper Info -->
        <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-400 mb-2">💡 Quick Tips:</h3>
            <ul class="text-sm text-blue-800 dark:text-blue-300 space-y-1">
                <li>• Click "Random" to generate realistic test values</li>
                <li>• Use this page until your IoT device is ready</li>
                <li>• Data appears on Dashboard and Historical Data pages</li>
                <li>• All readings are saved with your user account</li>
            </ul>
        </div>
    </div>
</div>
