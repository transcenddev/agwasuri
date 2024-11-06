<div>
    <button wire:click="predictFishType" class="btn btn-primary">Predict Best Fish Type for Previous Day</button>

    @if(!empty($predictionResults))
        <h3>Top 3 Recommended Fish Types for the Previous Day:</h3>
        <ul>
            @foreach($predictionResults as $fishType => $probability)
            <li>{{ $fishType }}: {{ round($probability * 100, 2) }}%</li>
            @endforeach
        </ul>
    @endif
</div>
