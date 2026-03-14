<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-800">{{ $monthLabel }}</h2>
        <div class="flex gap-2">
            <button wire:click="previousMonth"
                    class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition">← Prev</button>
            <button wire:click="nextMonth"
                    class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition">Next →</button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        {{-- Day headers --}}
        <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                <div class="px-2 py-3 text-center text-xs font-semibold text-gray-500 uppercase">{{ $day }}</div>
            @endforeach
        </div>

        {{-- Calendar grid --}}
        <div class="grid grid-cols-7">
            @foreach($calendarDays as $day)
                @if($day === null)
                    <div class="min-h-[100px] bg-gray-50 border border-gray-100"></div>
                @else
                    <div class="min-h-[100px] border border-gray-100 p-2 hover:bg-gray-50 transition
                        {{ $day['date'] === now()->format('Y-m-d') ? 'bg-indigo-50' : '' }}">
                        <span class="text-sm font-semibold {{ $day['date'] === now()->format('Y-m-d') ? 'text-indigo-600' : 'text-gray-700' }}">
                            {{ $day['day'] }}
                        </span>
                        <div class="mt-1 space-y-1">
                            @foreach($day['bookings']->take(3) as $booking)
                                @php
                                    $colors = ['pending'=>'yellow','confirmed'=>'blue','assigned'=>'indigo',
                                               'in_progress'=>'purple','completed'=>'green','cancelled'=>'red'];
                                    $c = $colors[$booking->status] ?? 'gray';
                                @endphp
                                <a href="{{ route('admin.bookings') }}"
                                   class="block text-xs px-1.5 py-0.5 rounded bg-{{ $c }}-100 text-{{ $c }}-700 truncate hover:opacity-80">
                                    {{ $booking->customer->name }}
                                </a>
                            @endforeach
                            @if($day['bookings']->count() > 3)
                                <p class="text-xs text-gray-400">+{{ $day['bookings']->count() - 3 }} more</p>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
