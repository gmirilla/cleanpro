<div>
    <div class="flex items-center justify-between mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search customers…"
               class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-64 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        <button wire:click="$set('showModal', true)"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Add Customer
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-xs text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Bookings</th>
                    <th class="px-4 py-3">Joined</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $customer->name }}</p>
                            <p class="text-xs text-gray-400">{{ $customer->email }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $customer->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $customer->bookings_count }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $customer->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <button wire:click="view({{ $customer->id }})"
                                    class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded">View</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-10 text-gray-400">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $customers->links() }}</div>

    {{-- Create Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <h2 class="text-lg font-bold mb-5">Add Customer</h2>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input wire:model="name" type="text"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input wire:model="email" type="email"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input wire:model="phone" type="text"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input wire:model="city" type="text"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input wire:model="address" type="text"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                        <input wire:model="state" type="text"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <input wire:model="notes" type="text"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button wire:click="create"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium">Create</button>
                <button wire:click="closeModal"
                        class="flex-1 border border-gray-300 text-gray-600 hover:bg-gray-50 py-2 rounded-lg text-sm">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    {{-- View Modal --}}
    @if($viewingCustomer)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl p-6 max-h-screen overflow-y-auto">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $viewingCustomer->name }}</h2>
                    <p class="text-sm text-gray-400">{{ $viewingCustomer->email }}</p>
                </div>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="space-y-4 text-sm">
                <div>
                    <p class="font-semibold text-gray-600 mb-2">Addresses</p>
                    @forelse($viewingCustomer->addresses as $addr)
                        <div class="bg-gray-50 rounded-lg p-3 mb-2">
                            <span class="font-medium">{{ $addr->label }}:</span> {{ $addr->full_address }}
                            @if($addr->is_default) <span class="text-green-600 text-xs ml-1">(Default)</span> @endif
                        </div>
                    @empty <p class="text-gray-400">No addresses on file.</p>
                    @endforelse
                </div>
                <div>
                    <p class="font-semibold text-gray-600 mb-2">Recent Bookings</p>
                    @forelse($viewingCustomer->bookings->take(5) as $b)
                        <div class="flex justify-between py-1 border-b border-gray-100">
                            <span class="font-mono text-xs">{{ $b->booking_reference }}</span>
                            <span class="text-xs text-gray-500">{{ $b->service_date->format('d M Y') }}</span>
                            <span class="text-xs font-medium">₦{{ number_format($b->total_amount, 2) }}</span>
                        </div>
                    @empty <p class="text-gray-400">No bookings yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
