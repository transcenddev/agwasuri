<div>
    @if($userDetails)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-1/2">
                <h2 class="text-xl font-bold mb-4">User Details</h2>
                <p><strong>ID:</strong> {{ $userDetails->user_id }}</p>
                <p><strong>Full Name:</strong> {{ $userDetails->first_name }} {{ $userDetails->last_name }}</p>
                <p><strong>Email:</strong> {{ $userDetails->email }}</p>
                <p><strong>Fishpond Name:</strong> {{ $userDetails->fishpond_name }}</p>
                <p><strong>Location:</strong> {{ $userDetails->barangay }}, {{ $userDetails->city }}, {{ $userDetails->province }}</p>
                <p><strong>Species Cultured:</strong> {{ $userDetails->species_cultured }}</p>
                <button wire:click="$set('userDetails', null)" class="mt-4 px-4 py-2 bg-red-500 text-white rounded">Close</button>
            </div>
        </div>
    @endif
</div>