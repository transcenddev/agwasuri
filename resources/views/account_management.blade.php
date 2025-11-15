<x-app-layout>
    <div class="py-4">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">

                        <div class="flex items-center justify-between min-w-full py-4">
                            <h2 class="text-xl font-semibold leading-tight">
                                Manage User
                            </h2>
                            <a href="#"
                                class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white transition bg-blue-500 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Add User
                            </a>
                        </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">
                                        Account type
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">
                                        Type of Farm
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
