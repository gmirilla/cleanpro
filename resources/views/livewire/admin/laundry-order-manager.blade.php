<div>
    <div class="flex items-center justify-between mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by booking reference…"
               class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-64 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
    </div>

    <div class="space-y-4">
        @forelse($orders as $order)
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $order->booking->booking_reference }}</p>
                        <p class="text-xs text-gray-400">Customer: {{ $order->booking->customer->name }}</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $order->booking->service_date->format('d M Y') }}
                        @if($order->express_service)
                            <span class="ml-2 bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full text-xs font-semibold">Express</span>
                        @endif
                    </div>
                </div>

                @if($order->special_instructions)
                    <p class="text-xs text-gray-500 bg-yellow-50 border border-yellow-100 rounded p-2 mb-3">
                        📝 {{ $order->special_instructions }}
                    </p>
                @endif

                {{-- Laundry items progress --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-gray-400 uppercase border-b border-gray-100">
                                <th class="py-1 text-left">Garment</th>
                                <th class="py-1 text-center">Qty</th>
                                <th class="py-1 text-left">Status</th>
                                <th class="py-1">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                @php
                                    $sc = ['received'=>'gray','washing'=>'blue','drying'=>'yellow',
                                           'ironing'=>'orange','ready'=>'green','delivered'=>'teal'];
                                    $c = $sc[$item->status] ?? 'gray';
                                @endphp
                                <tr class="border-b border-gray-50">
                                    <td class="py-1.5 font-medium text-gray-700">{{ ucfirst($item->garment_type) }}</td>
                                    <td class="py-1.5 text-center text-gray-600">{{ $item->quantity }}</td>
                                    <td class="py-1.5">
                                        <span class="px-2 py-0.5 rounded-full bg-{{ $c }}-100 text-{{ $c }}-700 text-xs">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="py-1.5">
                                        <select wire:change="updateItemStatus({{ $item->id }}, $event.target.value)"
                                                class="border border-gray-200 rounded px-2 py-0.5 text-xs text-gray-600">
                                            @foreach(\App\Models\LaundryItem::$statuses as $s)
                                                <option value="{{ $s }}" {{ $item->status === $s ? 'selected' : '' }}>
                                                    {{ ucfirst($s) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 flex gap-2">
                    <button wire:click="advanceAll({{ $order->id }})"
                            class="text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-3 py-1.5 rounded-lg transition">
                        ⏩ Advance All Items
                    </button>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-400">
                <p class="text-4xl mb-2">👕</p>
                <p>No laundry orders found.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>
