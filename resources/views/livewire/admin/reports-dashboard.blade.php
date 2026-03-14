<div>
    {{-- Controls --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <div class="flex rounded-lg border border-gray-300 overflow-hidden text-sm">
            @foreach(['week'=>'Week','month'=>'Month','quarter'=>'Quarter','year'=>'Year'] as $val => $label)
                <button wire:click="$set('period', '{{ $val }}')"
                        class="{{ $period === $val ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-50' }} px-4 py-2 transition">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        <div class="flex rounded-lg border border-gray-300 overflow-hidden text-sm">
            @foreach(['revenue'=>'Revenue','bookings'=>'Bookings','staff'=>'Staff','services'=>'Services'] as $val => $label)
                <button wire:click="$set('reportType', '{{ $val }}')"
                        class="{{ $reportType === $val ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-50' }} px-4 py-2 transition">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    @if($reportType === 'revenue')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-gray-500 text-sm">Total Revenue</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">₦{{ number_format($data['total'] ?? 0, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-gray-500 text-sm">Payments Received</p>
                <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $data['count'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <p class="text-gray-500 text-sm mb-3">By Method</p>
                @foreach($data['by_method'] ?? [] as $method => $amount)
                    <div class="flex justify-between text-sm py-1">
                        <span class="text-gray-600">{{ ucfirst($method) }}</span>
                        <span class="font-semibold">₦{{ number_format($amount, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <h3 class="font-semibold text-gray-700 mb-4">Monthly Trend</h3>
            @foreach($data['trend'] ?? [] as $month => $amount)
                @php $max = max($data['trend'] ?: [1]); $pct = $max > 0 ? ($amount / $max * 100) : 0; @endphp
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-xs text-gray-500 w-14 text-right">{{ $month }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-5">
                        <div class="bg-indigo-500 h-5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="text-xs text-gray-600 w-20 text-right">₦{{ number_format($amount, 0) }}</span>
                </div>
            @endforeach
        </div>

    @elseif($reportType === 'bookings')
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-gray-500 text-sm">Total</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $data['total'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-gray-500 text-sm">Completed</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $data['completed'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm text-center">
                <p class="text-gray-500 text-sm">Cancelled</p>
                <p class="text-3xl font-bold text-red-500 mt-1">{{ $data['cancelled'] ?? 0 }}</p>
            </div>
        </div>

    @elseif($reportType === 'staff')
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr class="text-xs text-gray-500 uppercase tracking-wider text-left">
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Jobs Completed</th>
                        <th class="px-4 py-3">Rating</th>
                        <th class="px-4 py-3">Availability</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($data as $row)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $row['name'] }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $row['jobs'] }}</td>
                            <td class="px-4 py-3 text-yellow-500">★ {{ number_format($row['rating'], 1) }}</td>
                            <td class="px-4 py-3">
                                <span class="{{ $row['availability'] === 'available' ? 'text-green-600' : 'text-gray-500' }} text-xs font-medium">
                                    {{ ucfirst(str_replace('_', ' ', $row['availability'])) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @elseif($reportType === 'services')
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr class="text-xs text-gray-500 uppercase tracking-wider text-left">
                        <th class="px-4 py-3">Service</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Times Booked</th>
                        <th class="px-4 py-3">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($data as $row)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $row->name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $row->category === 'cleaning' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                    {{ $row->category }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $row->times_booked }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-800">₦{{ number_format($row->revenue, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
