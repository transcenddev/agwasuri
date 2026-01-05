@section('title', 'Troubleshooting')
<x-app-layout>
    <x-container class="py-6">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Troubleshooting</h1>
                <div class="w-1/3">
                    <input id="troubleshoot-search" type="search" placeholder="Search tips (e.g. sensor, chart, prediction)" class="w-full px-4 py-2 border rounded-lg" />
                </div>
            </div>

            <p class="text-sm text-gray-600 mb-4">Quick guides to diagnose and fix common issues: no data, charts not updating, device offline, model prediction errors, and real-time disconnects.</p>

            <div id="tips-list" class="space-y-4">
                {{-- Quick Fixes --}}
                <details class="bg-white p-4 rounded-lg shadow-sm" open>
                    <summary class="font-semibold cursor-pointer">Quick Fixes</summary>
                    <div class="mt-3 text-sm text-gray-700 space-y-2">
                        <p><strong>No data on dashboard?</strong> Check: sensor power, network, and if device is authenticated. Use the <em>Test Sensor Connection</em> script under <code>scripts/</code>.</p>
                        <p><strong>Charts not updating?</strong> Confirm Reverb process is running and browser console shows no Echo errors. Try refreshing or run <code>php artisan reverb:start</code>.</p>
                        <p><strong>Prediction failed?</strong> Check ML API availability (https://api.agwasuri.app). Look for timeouts in <code>storage/logs/laravel.log</code>.</p>
                    </div>
                </details>

                {{-- Sensor Connectivity --}}
                <details class="bg-white p-4 rounded-lg shadow-sm">
                    <summary class="font-semibold cursor-pointer">Sensor Connectivity</summary>
                    <div class="mt-3 text-sm text-gray-700 space-y-2">
                        <p><strong>Device offline:</strong> verify power, SIM/Wi-Fi connectivity, and that the device clock is correct. Check the last POST to <code>/store-water-quality-data</code> in server logs.</p>
                        <p><strong>Authentication errors:</strong> ensure the device is sending the correct Bearer API key and the request body contains the expected <code>user_id</code>.</p>
                        <div class="flex gap-2 mt-2">
                            <button class="px-3 py-2 bg-gray-100 rounded text-sm" onclick="copyText('pwsh ./scripts/send-sample-water-data.ps1')">Copy test script</button>
                            <button class="px-3 py-2 bg-gray-100 rounded text-sm" onclick="copyText('curl -X POST http://localhost:8000/store-water-quality-data -H "Authorization: Bearer <API_KEY>" -H "Content-Type: application/json" -d "{\"user_id\":1,\"temperature\":28.5,\"salinity\":15,\"dissolved_oxygen\":6.8,\"ph_level\":7.2}"')">Copy curl</button>
                        </div>
                    </div>
                </details>

                {{-- Real-time Issues --}}
                <details class="bg-white p-4 rounded-lg shadow-sm">
                    <summary class="font-semibold cursor-pointer">Real-time / Broadcasting</summary>
                    <div class="mt-3 text-sm text-gray-700 space-y-2">
                        <p>If charts or Livewire components aren't updating, confirm the Reverb server is running and Livewire Echo is connected. Check browser console for Pusher/Echo errors.</p>
                        <p>Restart Reverb:</p>
                        <pre class="bg-gray-100 p-2 rounded text-xs">php artisan reverb:start</pre>
                        <p>Test broadcast locally (Tinker):</p>
                        <pre class="bg-gray-100 p-2 rounded text-xs">php artisan tinker
event(new \App\Events\WaterQualityCreated(\App\Models\WaterQualityData::first()));</pre>
                    </div>
                </details>

                {{-- ML Prediction Errors --}}
                <details class="bg-white p-4 rounded-lg shadow-sm">
                    <summary class="font-semibold cursor-pointer">ML Prediction Errors</summary>
                    <div class="mt-3 text-sm text-gray-700 space-y-2">
                        <p>If the prediction API times out or returns errors, check network connectivity to <code>https://api.agwasuri.app</code> and inspect Laravel logs for response details.</p>
                        <p>Increase prediction timeout temporarily in <code>app/Livewire/ClassifyFish.php</code> if needed.</p>
                    </div>
                </details>

                {{-- Admin Tools --}}
                <details class="bg-white p-4 rounded-lg shadow-sm">
                    <summary class="font-semibold cursor-pointer">Admin Tools</summary>
                    <div class="mt-3 text-sm text-gray-700 space-y-2">
                        <p>Useful commands for admins:</p>
                        <ul class="list-disc ml-5">
                            <li><code>php artisan migrate</code> / <code>php artisan migrate:fresh --seed</code></li>
                            <li><code>php artisan cache:clear</code></li>
                            <li><code>php artisan config:cache</code></li>
                        </ul>
                        <p>View logs:</p>
                        <pre class="bg-gray-100 p-2 rounded text-xs">tail -n 200 storage/logs/laravel.log</pre>
                    </div>
                </details>

                {{-- Contact Support --}}
                <details class="bg-white p-4 rounded-lg shadow-sm">
                    <summary class="font-semibold cursor-pointer">Contact & Logs</summary>
                    <div class="mt-3 text-sm text-gray-700 space-y-2">
                        <p>When reporting an issue, include:</p>
                        <ol class="list-decimal ml-5">
                            <li>Steps to reproduce</li>
                            <li>Relevant screenshots and exact timestamps</li>
                            <li>Contents of <code>storage/logs/laravel.log</code> for the time of the incident</li>
                        </ol>
                        <p class="mt-2">Download logs:</p>
                        <button class="px-3 py-2 bg-gray-100 rounded text-sm" onclick="copyText('zip -r logs.zip storage/logs')">Copy zip command</button>
                    </div>
                </details>
            </div>

        </div>

    </x-container>

    <script>
        function copyText(t) {
            navigator.clipboard && navigator.clipboard.writeText(t).then(()=>{alert('Copied to clipboard')}).catch(()=>{prompt('Copy this command', t)});
        }

        document.getElementById('troubleshoot-search').addEventListener('input', function(e){
            const q = e.target.value.toLowerCase();
            document.querySelectorAll('#tips-list details').forEach(d=>{
                const text = d.innerText.toLowerCase();
                if(!q) { d.style.display = ''; return; }
                d.style.display = text.includes(q) ? '' : 'none';
            });
        });
    </script>
</x-app-layout>
