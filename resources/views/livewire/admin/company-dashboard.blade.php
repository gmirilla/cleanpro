<div>
    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @php
            $cards = [
                ['label'=>'Total Bookings',    'value'=> $stats['total_bookings'],    'color'=>'indigo',  'icon'=>'📋'],
                ['label'=>'Active Jobs',        'value'=> $stats['active_jobs'],       'color'=>'yellow',  'icon'=>'⚡'],
                ['label'=>'Total Customers',   'value'=> $stats['total_customers'],   'color'=>'green',   'icon'=>'👥'],
                ['label'=>'Monthly Revenue',   'value'=> '₦'.number_format($stats['monthly_revenue'],2), 'color'=>'blue', 'icon'=>'💰'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-{{ $card['color'] }}-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $card['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $card['value'] }}</p>
                    </div>
                    <span class="text-3xl">{{ $card['icon'] }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Revenue Chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-700 mb-4">Revenue (Last 6 Months)</h3>
            <div class="space-y-3">
                @forelse($stats['revenue_chart'] as $month => $amount)
                    @php $max = max($stats['revenue_chart'] ?: [1]); $pct = $max > 0 ? ($amount / $max * 100) : 0; @endphp
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 w-16 text-right">{{ $month }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-5">
                            <div class="bg-indigo-500 h-5 rounded-full transition-all duration-500 flex items-center justify-end pr-2"
                                 style="width: {{ $pct }}%">
                                <span class="text-white text-xs font-medium">₦{{ number_format($amount,0) }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No revenue data yet</p>
                @endforelse
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-600 mb-3">Today's Overview</h3>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Completed Today</span>
                        <span class="font-bold text-green-600">{{ $stats['completed_today'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pending</span>
                        <span class="font-bold text-yellow-600">{{ $stats['pending_bookings'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Available Staff</span>
                        <span class="font-bold text-blue-600">{{ $stats['available_staff'] }} / {{ $stats['total_staff'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Unpaid Invoices</span>
                        <span class="font-bold text-red-600">{{ $stats['unpaid_invoices'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Top Services --}}
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-600 mb-3">Top Services</h3>
                <div class="space-y-2">
                    @forelse($stats['top_services'] as $svc)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 truncate">{{ $svc['name'] }}</span>
                            <span class="font-semibold text-indigo-600">{{ $svc['total_booked'] }} bookings</span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400">No data yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Staff Performance --}}
    @if(!empty($stats['staff_performance']))
    <div class="mt-6 bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-base font-semibold text-gray-700 mb-4">Staff Performance</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">Name</th>
                        <th class="pb-2">Rating</th>
                        <th class="pb-2">Completed Jobs</th>
                        <th class="pb-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($stats['staff_performance'] as $staff)
                        <tr>
                            <td class="py-2 font-medium text-gray-800">{{ $staff['name'] }}</td>
                            <td class="py-2">
                                <span class="text-yellow-500">★</span>
                                {{ number_format($staff['rating'], 1) }}
                            </td>
                            <td class="py-2 text-gray-700">{{ $staff['completed_jobs'] }}</td>
                            <td class="py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $staff['availability'] === 'available' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst(str_replace('_', ' ', $staff['availability'])) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
