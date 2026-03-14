<div>
    {{-- Filters --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search reference…"
               class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-indigo-400">
        <select wire:model.live="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All Statuses</option>
            @foreach(['pending','confirmed','assigned','in_progress','completed','cancelled'] as $s)
                <option value="{{ $s }}">{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <input wire:model.live="dateFilter" type="date"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-gray-500 text-xs uppercase tracking-wider">
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Services</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Staff</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($bookings as $booking)
                    @php
                        $colors = ['pending'=>'yellow','confirmed'=>'blue','assigned'=>'indigo',
                                   'in_progress'=>'purple','completed'=>'green','cancelled'=>'red'];
                        $c = $colors[$booking->status] ?? 'gray';
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $booking->booking_reference }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $booking->customer->name }}</p>
                            <p class="text-xs text-gray-400">{{ $booking->customer->email }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $booking->items->pluck('service.name')->join(', ') }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $booking->service_date->format('d M Y') }}<br>
                            <span class="text-xs text-gray-400">{{ $booking->service_date->format('h:i A') }}</span>
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-800">₦{{ number_format($booking->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $booking->assignedStaff?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-700">
                                {{ ucfirst(str_replace('_',' ',$booking->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1" x-data="{}">
                                @if($booking->status === 'pending')
                                    <button wire:click="updateStatus({{ $booking->id }}, 'confirmed')"
                                            class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded transition">Confirm</button>
                                    <button wire:click="openAssign({{ $booking->id }})"
                                            class="text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-2 py-1 rounded transition">Assign</button>
                                @endif
                                @if($booking->status === 'assigned')
                                    <button wire:click="updateStatus({{ $booking->id }}, 'in_progress')"
                                            class="text-xs bg-purple-100 hover:bg-purple-200 text-purple-700 px-2 py-1 rounded transition">Start</button>
                                @endif
                                @if($booking->status === 'in_progress')
                                    <button wire:click="updateStatus({{ $booking->id }}, 'completed')"
                                            class="text-xs bg-green-100 hover:bg-green-200 text-green-700 px-2 py-1 rounded transition">Complete</button>
                                @endif
                                @if(!in_array($booking->status, ['completed','cancelled']))
                                    <button wire:click="openCancel({{ $booking->id }})"
                                            class="text-xs bg-red-100 hover:bg-red-200 text-red-600 px-2 py-1 rounded transition">Cancel</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-10 text-gray-400">No bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>

    {{-- Assign Staff Modal --}}
    @if($assigningId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h2 class="text-lg font-bold mb-4">Assign Staff</h2>
            <select wire:model="selectedStaff" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4">
                <option value="">Select available staff…</option>
                @foreach($availableStaff as $s)
                    <option value="{{ $s->id }}">{{ $s->user->name }} – ⭐ {{ $s->rating }}</option>
                @endforeach
            </select>
            @error('selectedStaff') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror
            <div class="flex gap-3">
                <button wire:click="confirmAssign" class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm font-medium">Assign</button>
                <button wire:click="$set('assigningId', null)" class="flex-1 border border-gray-300 text-gray-600 py-2 rounded-lg text-sm">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Cancel Modal --}}
    @if($cancellingId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h2 class="text-lg font-bold mb-4 text-red-600">Cancel Booking</h2>
            <textarea wire:model="cancellationReason" rows="3" placeholder="Reason for cancellation…"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-4 focus:ring-2 focus:ring-red-300 focus:outline-none resize-none"></textarea>
            <div class="flex gap-3">
                <button wire:click="confirmCancel" class="flex-1 bg-red-600 text-white py-2 rounded-lg text-sm font-medium">Confirm Cancel</button>
                <button wire:click="$set('cancellingId', null)" class="flex-1 border border-gray-300 text-gray-600 py-2 rounded-lg text-sm">Back</button>
            </div>
        </div>
    </div>
    @endif
</div>
