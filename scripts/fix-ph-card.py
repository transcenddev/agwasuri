#!/usr/bin/env python3

file = r'c:\Users\Admin\_repos\agwasuri_master\agwasuri_prod\resources\views\livewire\water-quality-card.blade.php'

with open(file, 'r', encoding='utf-8') as f:
    content = f.read()

# Simple string replacements
replacements = [
    # 1. Change pH card div classes
    (
        'px-5 py-6 sm:py-6 text-center rounded-2xl min-h-24 {{ $this->classesForStatus($phStatus) }}">',
        'text-center bg-white rounded-2xl shadow-md transition-all duration-300" x-data="{ compact: false }" @toggle-compact-view.window="compact = !compact" x-bind:class="compact ? \'px-3 py-4 h-32\' : \'px-5 py-6 sm:py-6 min-h-24\'">'
    ),
    # 2. Change label color match to badge color match
    (
        "                        $phLabelColor = match(strtoupper($phClassification['label'])) {\n                            'BEST' => 'text-green-600 font-semibold',\n                            'GOOD' => 'text-blue-600 font-semibold',\n                            'BAD' => 'text-amber-600 font-semibold',\n                            'HARM' => 'text-rose-600 font-semibold',",
        "                        $phBadgeClass = match(strtoupper($phClassification['label'])) {\n                            'BEST' => 'bg-green-100 text-green-800',\n                            'GOOD' => 'bg-blue-100 text-blue-800',\n                            'BAD' => 'bg-yellow-100 text-yellow-800',\n                            'HARM' => 'bg-red-100 text-red-800',"
    ),
    # 3. Add sparkClass definition
    (
        "                        };\n                    @endphp\n                    <span class=\"font-medium {{ $phLabelColor }}\">{{ $phClassification['label'] }}</span>",
        "                        };\n                        $phSparkClass = match(strtoupper($phClassification['label'])) {\n                            'BEST' => 'text-green-500',\n                            'GOOD' => 'text-blue-500',\n                            'BAD' => 'text-yellow-500',\n                            'HARM' => 'text-red-500',\n                            default => 'text-gray-400'\n                        };\n                    @endphp\n                    <span class=\"text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $phBadgeClass }}\">{{ $phClassification['label'] }}</span>"
    ),
    # 4. Remove note span
    (
        "                    @if(!empty($phClassification['note']))\n                        <span class=\"ml-2 text-gray-500\">{{ $phClassification['note'] }}</span>\n                    @endif\n                </div>\n            @endif",
        "                </div>\n            @endif"
    ),
    # 5. Update div wrapper for badge
    (
        "            @if(!empty($phClassification['label']))\n                <div class=\"mt-2 text-xs\">",
        "            @if(!empty($phClassification['label']))\n                <div class=\"mt-2 flex flex-col items-center gap-1\" x-show=\"!compact\">"
    ),
    # 6. Update sparkline SVG
    (
        '<svg class="w-20 h-6" viewBox="0 0 72 28" preserveAspectRatio="none" aria-hidden="true">\n                        <polyline points="{{ $phSparklinePath }}" fill="none" stroke="var(--wq-series-ph)"',
        '<svg class="w-20 h-6 {{ $phSparkClass ?? \'text-gray-400\' }}" viewBox="0 0 72 28" preserveAspectRatio="none" aria-hidden="true">\n                        <polyline points="{{ $phSparklinePath }}" fill="none" stroke="currentColor"'
    ),
]

for old, new in replacements:
    if old in content:
        content = content.replace(old, new)
        print(f"✓ Applied replacement")
    else:
        print(f"✗ Could not find pattern to replace")

with open(file, 'w', encoding='utf-8') as f:
    f.write(content)

print("\n✓ Fixed pH card styling")

