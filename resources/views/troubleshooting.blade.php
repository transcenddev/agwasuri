@section('title', 'Troubleshooting')
<x-app-layout>
    <x-container class="py-6">
        <div class="max-w-6xl mx-auto space-y-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Help Center</h1>
                </div>
                <div class="w-full sm:w-96">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input id="troubleshoot-search" type="search" placeholder="Search for a problem..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-800 dark:text-white" />
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-semibold mb-1">Understanding Status Indicators:</p>
                        <div class="space-y-1 text-blue-700 dark:text-blue-300">
                            <p><span class="font-medium">Device Status:</span> Online ✓ | Offline ✗ | Connecting...</p>
                            <p><span class="font-medium">Live Updates:</span> Connected ✓ | Disconnected ✗</p>
                            <p><span class="font-medium">Data Freshness:</span> Recent (3 min ago) | Stale (2+ hours) ⚠️</p>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Common Issues</h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="#issue-no-data" class="group block border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-green-500 dark:hover:border-green-500 hover:shadow-md transition-all">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center group-hover:bg-green-500 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">No data showing</span>
                        </div>
                    </a>
                    <a href="#issue-offline" class="group block border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-red-500 dark:hover:border-red-500 hover:shadow-md transition-all">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center group-hover:bg-red-500 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">Device offline</span>
                        </div>
                    </a>
                    <a href="#issue-live-chart" class="group block border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-md transition-all">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">Chart frozen</span>
                        </div>
                    </a>
                    <a href="#issue-prediction" class="group block border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-purple-500 dark:hover:border-purple-500 hover:shadow-md transition-all">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center group-hover:bg-purple-500 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">Prediction failed</span>
                        </div>
                    </a>
                    <a href="#issue-notifications" class="group block border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-yellow-500 dark:hover:border-yellow-500 hover:shadow-md transition-all">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center group-hover:bg-yellow-500 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">No notifications</span>
                        </div>
                    </a>
                    <a href="#issue-export" class="group block border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-indigo-500 dark:hover:border-indigo-500 hover:shadow-md transition-all">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">Export issues</span>
                        </div>
                    </a>
                    <a href="#issue-login" class="group block border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-orange-500 dark:hover:border-orange-500 hover:shadow-md transition-all">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center group-hover:bg-orange-500 group-hover:text-white transition-colors">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">Login problems</span>
                        </div>
                    </a>
                </div>
            </div>

            <div id="tips-list" class="space-y-6">
                <section id="issue-no-data" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl text-gray-900 dark:text-white">No data showing on my dashboard</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Empty charts or very old readings</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300"><strong class="text-gray-900 dark:text-white">What you'll see:</strong> Empty charts, "No data available," or last update is very old.</p>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-green-500">
                            <p class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Try this (3 steps)
                            </p>
                        <ol class="list-decimal ml-5 space-y-2">
                            <li>Check device power; solar or battery indicator should be on.</li>
                            <li>Change date range to "Last 7 Days" and refresh.</li>
                            <li>Wait 5 minutes and reload; device sends every few minutes.</li>
                        </ol>
                        </div>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer font-semibold text-gray-900 dark:text-white p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            More steps
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Try a different browser (Chrome or Edge).</li>
                            <li>Make sure the device status shows Online.</li>
                            <li>Move closer to the Wi-Fi or router if signal is weak.</li>
                        </ul>
                    </details>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <p class="text-sm text-blue-900 dark:text-blue-100"><strong>Still not fixed?</strong> Click "Send Diagnostic Report" and include the last data time and your device ID.</p>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer text-sm font-semibold text-orange-700 dark:text-orange-400 p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Admin only
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Confirm recent sensor posts are succeeding; rerun a test send.</li>
                            <li>Restart the data collector or service.</li>
                            <li><button class="px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white rounded-lg hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors text-xs font-mono" onclick="copyText('pwsh ./scripts/send-sample-water-data.ps1')">Copy test send command</button></li>
                        </ul>
                    </details>
                </section>

                <section id="issue-offline" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl text-gray-900 dark:text-white">Device shows Offline</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">No connection to your sensor device</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300"><strong class="text-gray-900 dark:text-white">What you'll see:</strong> Red "Offline" badge, "Last seen: hours ago," no new readings.</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-red-500">
                        <p class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Try this (3 steps)
                        </p>
                        <ol class="list-decimal ml-5 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <li>Check LED: green = online; blinking red = trying; off = no power.</li>
                            <li>Power cycle: unplug 10 seconds, replug, wait 2 minutes.</li>
                            <li>Check internet: ensure SIM or Wi-Fi has signal and router is on.</li>
                        </ol>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer font-semibold text-gray-900 dark:text-white p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            More steps
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Move the device closer to the router or window for better signal.</li>
                            <li>Ensure the device clock or time is roughly correct.</li>
                            <li>Try another outlet or power source.</li>
                        </ul>
                    </details>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <p class="text-sm text-blue-900 dark:text-blue-100"><strong>Still not fixed?</strong> Note the LED pattern and the last data time shown; share both with support.</p>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer text-sm font-semibold text-orange-700 dark:text-orange-400 p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Admin only
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Confirm the device can reach the server.</li>
                            <li>Restart the device gateway or bridge service.</li>
                            <li><button class="px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white rounded-lg hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors text-xs font-mono" onclick="copyText('ping <device-host-or-ip>')">Copy connectivity check</button></li>
                        </ul>
                    </details>
                </section>

                <section id="issue-live-chart" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl text-gray-900 dark:text-white">Live chart not updating / frozen</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Real-time updates not working</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300"><strong class="text-gray-900 dark:text-white">What you'll see:</strong> Charts stuck on old data; Live indicator is gray; manual refresh works but live does not.</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-blue-500">
                        <p class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Try this (3 steps)
                        </p>
                        <ol class="list-decimal ml-5 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <li>Hard refresh the page (Ctrl+Shift+R or Cmd+Shift+R).</li>
                            <li>Check the Live status badge and wait 30 seconds for reconnection.</li>
                            <li>Open in a private or incognito window to bypass cache.</li>
                        </ol>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer font-semibold text-gray-900 dark:text-white p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            More steps
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Close heavy tabs that may slow the browser.</li>
                            <li>Restart your router or modem.</li>
                            <li>If on mobile data, move to a stronger signal spot.</li>
                        </ul>
                    </details>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <p class="text-sm text-blue-900 dark:text-blue-100"><strong>Still not fixed?</strong> Take a screenshot showing the chart timestamp and Live status, then send it.</p>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer text-sm font-semibold text-orange-700 dark:text-orange-400 p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Admin only
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Restart the live update or broadcast service.</li>
                            <li>Check browser console for connection errors, then reconnect.</li>
                            <li>Trigger a test event to confirm live updates reach clients.</li>
                        </ul>
                    </details>
                </section>

                <section id="issue-prediction" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl text-gray-900 dark:text-white">Prediction failed / no recommendation</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Fish prediction not working</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300"><strong class="text-gray-900 dark:text-white">What you'll see:</strong> "Prediction failed," spinner never ends, or blank recommendation card.</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-purple-500">
                        <p class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Try this (3 steps)
                        </p>
                        <ol class="list-decimal ml-5 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <li>Wait 2 minutes, then tap "Get Recommendation" again.</li>
                            <li>Make sure you have at least a week of recent readings.</li>
                            <li>Confirm you are online before retrying.</li>
                        </ol>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer font-semibold text-gray-900 dark:text-white p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            More steps
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Clear browser cache for images and files.</li>
                            <li>Use "View Full Analysis" to see details.</li>
                            <li>Try from another browser.</li>
                        </ul>
                    </details>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <p class="text-sm text-blue-900 dark:text-blue-100"><strong>Still not fixed?</strong> Note the exact error message and the date range of your data; send both to support.</p>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer text-sm font-semibold text-orange-700 dark:text-orange-400 p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Admin only
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Restart the prediction service.</li>
                            <li>Check that the model endpoint is reachable.</li>
                            <li>If jobs are queued, clear stuck ones and retry.</li>
                        </ul>
                    </details>
                </section>

                <section id="issue-notifications" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl text-gray-900 dark:text-white">Notifications not received</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Not getting alerts or emails</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300"><strong class="text-gray-900 dark:text-white">What you'll see:</strong> No alerts when water quality is bad; alert history is empty.</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-yellow-500">
                        <p class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Try this (3 steps)
                        </p>
                        <ol class="list-decimal ml-5 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <li>Open Settings to Notifications and turn them on.</li>
                            <li>Confirm your email or phone is correct and verified.</li>
                            <li>Send a test notification from Settings.</li>
                        </ol>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer font-semibold text-gray-900 dark:text-white p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            More steps
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Check spam or junk for emails; add to safe senders.</li>
                            <li>Lower alert thresholds briefly to trigger a test.</li>
                            <li>Ensure Do Not Disturb is off on your device.</li>
                        </ul>
                    </details>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <p class="text-sm text-blue-900 dark:text-blue-100"><strong>Still not fixed?</strong> Tell support which channel failed (email, SMS, or in-app) and when you expected it.</p>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer text-sm font-semibold text-orange-700 dark:text-orange-400 p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Admin only
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Restart the notification or queue worker.</li>
                            <li>Check for recent notification failures and retry.</li>
                            <li>Send a test dispatch command to verify delivery.</li>
                        </ul>
                    </details>
                </section>

                <section id="issue-export" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl text-gray-900 dark:text-white">Export / Download not working</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Cannot download data or reports</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300"><strong class="text-gray-900 dark:text-white">What you'll see:</strong> Download button does nothing, file is empty or corrupt, or "Export failed."</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-indigo-500">
                        <p class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Try this (3 steps)
                        </p>
                        <ol class="list-decimal ml-5 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <li>Export a smaller date range, for example 1 month.</li>
                            <li>Check your downloads folder and allow pop-ups.</li>
                            <li>Try another browser (Chrome, Edge, or Firefox).</li>
                        </ol>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer font-semibold text-gray-900 dark:text-white p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            More steps
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Wait 1 minute and retry if the server was busy.</li>
                            <li>Use a stable connection; avoid very slow networks.</li>
                            <li>Reduce columns or filters to lighten the export.</li>
                        </ul>
                    </details>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <p class="text-sm text-blue-900 dark:text-blue-100"><strong>Still not fixed?</strong> Share the date range and approximate number of rows you tried to export.</p>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer text-sm font-semibold text-orange-700 dark:text-orange-400 p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Admin only
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Restart the export or background worker.</li>
                            <li>Check for timeouts and raise limits temporarily if needed.</li>
                            <li>Run a small test export to confirm the pipeline works.</li>
                        </ul>
                    </details>
                </section>

                <section id="issue-login" class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl text-gray-900 dark:text-white">Login / session issues</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Cannot sign in or getting logged out</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300"><strong class="text-gray-900 dark:text-white">What you'll see:</strong> "Session expired," unexpected logout, or cannot log in with correct details.</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-orange-500">
                        <p class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Try this (3 steps)
                        </p>
                        <ol class="list-decimal ml-5 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <li>Clear site cookies for Agwasuri, then try logging in again.</li>
                            <li>Use "Forgot Password" to reset and log in.</li>
                            <li>Try incognito or private mode to rule out cache issues.</li>
                        </ol>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer font-semibold text-gray-900 dark:text-white p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            More steps
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Ensure Caps Lock is off; retype the password.</li>
                            <li>Wait 15 minutes if there were many failed attempts.</li>
                            <li>Try from another device, phone, or PC.</li>
                        </ul>
                    </details>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <p class="text-sm text-blue-900 dark:text-blue-100"><strong>Still not fixed?</strong> Send your username, email, and a screenshot of the error to support.</p>
                    </div>
                    <details class="group">
                        <summary class="cursor-pointer text-sm font-semibold text-orange-700 dark:text-orange-400 p-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Admin only
                        </summary>
                        <ul class="list-disc ml-5 mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300 p-4">
                            <li>Restart the auth or session service.</li>
                            <li>Check if rate limits are blocking the user, then clear if safe.</li>
                            <li>Run a test login flow with a known good account.</li>
                        </ul>
                    </details>
                </section>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button class="px-6 py-3 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm" onclick="alert('Diagnostic report sent')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Send Diagnostic Report
                </button>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Was this helpful?</span>
                    <button class="px-4 py-2 border-2 border-green-500 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg font-medium transition-colors" onclick="alert('Thanks for your feedback!')">Yes</button>
                    <button class="px-4 py-2 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900 rounded-lg font-medium transition-colors" onclick="alert('We will improve this guide.')">No</button>
                </div>
            </div>
        </div>
    </x-container>

    <script>
        function copyText(t) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(t).then(() => {
                    alert('Copied to clipboard');
                }).catch(() => {
                    prompt('Copy this command', t);
                });
            } else {
                prompt('Copy this command', t);
            }
        }

        const searchInput = document.getElementById('troubleshoot-search');
        const sections = Array.from(document.querySelectorAll('#tips-list section'));

        searchInput?.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase();
            sections.forEach((section) => {
                const text = section.innerText.toLowerCase();
                section.style.display = q ? (text.includes(q) ? '' : 'none') : '';
            });
        });
    </script>
</x-app-layout>@section('title', 'Troubleshooting')

