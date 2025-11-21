<div>
    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex flex-col px-4 py-8 text-center rounded-lg bg-cvsu-50">
            <dt class="order-last text-lg font-medium text-gray-500">Temperature</dt>

            <dd class="text-4xl font-extrabold text-cvsu-600 md:text-5xl">{{ $this->temperature ?? '-' }}</dd>
        </div>

        <div class="flex flex-col px-4 py-8 text-center rounded-lg bg-cvsu-50">
            <dt class="order-last text-lg font-medium text-gray-500">Salinity</dt>

            <dd class="text-4xl font-extrabold text-cvsu-600 md:text-5xl">{{ $this->salinity ?? '-' }}</dd>
        </div>

        <div class="flex flex-col px-4 py-8 text-center rounded-lg bg-cvsu-50">
            <dt class="order-last text-lg font-medium text-gray-500">Dissolved Oxygen</dt>

            <dd class="text-4xl font-extrabold text-cvsu-600 md:text-5xl">{{ $this->dissolved_oxygen ?? '-' }}</dd>
        </div>

        <div class="flex flex-col px-4 py-8 text-center rounded-lg bg-cvsu-50">
            <dt class="order-last text-lg font-medium text-gray-500">pH</dt>

            <dd class="text-4xl font-extrabold text-cvsu-600 md:text-5xl">{{ $this->ph_level ?? '-'}}</dd>
        </div>
    </dl>
</div>
