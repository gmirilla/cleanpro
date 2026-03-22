<div>
    <div class="flex items-center justify-between mb-6">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search customers…"
               class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-64 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
        <div class="flex items-center gap-2">
            {{-- Quick-create (admin sets a default password) --}}
            <button wire:click="$set('showModal', true)"
                    class="border border-indigo-300 text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg text-sm font-medium transition">
                + Quick Add
            </button>
            {{-- Full registration with password --}}
            <button wire:click="openRegisterModal"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                + Register Customer
            </button>
        </div>
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


    {{-- ════════════════════════════════════════════
         REGISTER CUSTOMER MODAL (full, with password)
    ════════════════════════════════════════════ --}}
    @if($showRegisterModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">

            {{-- Header --}}
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Register New Customer</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Create a full customer account with login credentials.</p>
                </div>
                <button wire:click="closeRegisterModal" class="text-gray-400 hover:text-gray-600 text-xl leading-none">✕</button>
            </div>

            <div class="space-y-5">

                {{-- Personal Info Section --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Personal Information</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input wire:model="reg_name" type="text" placeholder="Adaeze Okafor"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            @error('reg_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input wire:model="reg_phone" type="tel" placeholder="+234 800 000 0000"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            @error('reg_phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Login Credentials Section --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Login Credentials</p>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                            <input wire:model="reg_email" type="email" placeholder="adaeze@example.com"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            @error('reg_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                                <input wire:model="reg_password" type="password" placeholder="Min. 8 characters"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                @error('reg_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                                <input wire:model="reg_password_confirm" type="password" placeholder="Repeat password"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                                @error('reg_password_confirm') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Address Section (optional) --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">
                        Default Address <span class="text-gray-300 font-normal normal-case">(optional)</span>
                    </p>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                            <input wire:model="reg_address" type="text" placeholder="15 Admiralty Way, Lekki Phase 1"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                <input wire:model="reg_city" type="text" placeholder="Lagos"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                <input wire:model="reg_state" type="text" placeholder="Lagos"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Info note --}}
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 flex items-start gap-2 text-xs text-blue-700">
                    <span class="mt-0.5 flex-shrink-0">ℹ</span>
                    <span>The customer will be able to log in immediately using the email and password you set. You can share credentials with them directly.</span>
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-5 border-t border-gray-100">
                <button wire:click="register"
                        wire:loading.attr="disabled"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    <span wire:loading.remove wire:target="register">Register Customer</span>
                    <span wire:loading wire:target="register">Registering…</span>
                </button>
                <button wire:click="closeRegisterModal"
                        class="flex-1 border border-gray-300 text-gray-600 hover:bg-gray-50 py-2.5 rounded-lg text-sm transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif


    {{-- ════════════════════════════════════════════
         QUICK ADD MODAL (admin creates, no password)
    ════════════════════════════════════════════ --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <h2 class="text-lg font-bold">Quick Add Customer</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Default password will be "password".</p>
                </div>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl leading-none">✕</button>
            </div>
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


    {{-- ════════════════════════════════════════════
         CUSTOMER DETAIL MODAL
    ════════════════════════════════════════════ --}}
    @if($viewingCustomer)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl p-6 max-h-screen overflow-y-auto">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center text-lg font-bold text-indigo-600">
                        {{ strtoupper(substr($viewingCustomer->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">{{ $viewingCustomer->name }}</h2>
                        <p class="text-sm text-gray-400">{{ $viewingCustomer->email }}</p>
                    </div>
                </div>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>

            {{-- Stats strip --}}
            <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="bg-indigo-50 rounded-xl p-3 text-center">
                    <p class="text-xl font-bold text-indigo-700">{{ $viewingCustomer->bookings->count() }}</p>
                    <p class="text-xs text-indigo-500">Bookings</p>
                </div>
                <div class="bg-green-50 rounded-xl p-3 text-center">
                    <p class="text-xl font-bold text-green-700">
                        {{ $viewingCustomer->bookings->where('status', 'completed')->count() }}
                    </p>
                    <p class="text-xs text-green-500">Completed</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <p class="text-xl font-bold text-gray-700">
                        ₦{{ number_format($viewingCustomer->bookings->sum('total_amount'), 0) }}
                    </p>
                    <p class="text-xs text-gray-500">Total Spent</p>
                </div>
            </div>

            <div class="space-y-4 text-sm">
                <div>
                    <p class="font-semibold text-gray-600 mb-2">📞 Contact</p>
                    <div class="bg-gray-50 rounded-lg p-3 space-y-1">
                        <p class="text-gray-600">Phone: <span class="font-medium">{{ $viewingCustomer->phone ?? '—' }}</span></p>
                        @if($viewingCustomer->notes)
                            <p class="text-gray-500 text-xs">Notes: {{ $viewingCustomer->notes }}</p>
                        @endif
                    </div>
                </div>

                <div>
                    <p class="font-semibold text-gray-600 mb-2">📍 Addresses</p>
                    @forelse($viewingCustomer->addresses as $addr)
                        <div class="bg-gray-50 rounded-lg p-3 mb-2 flex items-start justify-between">
                            <div>
                                <span class="font-medium">{{ $addr->label }}:</span>
                                <span class="text-gray-600"> {{ $addr->full_address }}</span>
                            </div>
                            @if($addr->is_default)
                                <span class="text-green-600 text-xs ml-2 flex-shrink-0 bg-green-100 px-2 py-0.5 rounded-full">Default</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-400">No addresses on file.</p>
                    @endforelse
                </div>

                <div>
                    <p class="font-semibold text-gray-600 mb-2">📋 Recent Bookings</p>
                    @forelse($viewingCustomer->bookings->take(5) as $b)
                        @php
                            $colors = ['pending'=>'yellow','confirmed'=>'blue','assigned'=>'indigo',
                                       'in_progress'=>'purple','completed'=>'green','cancelled'=>'red'];
                            $bc = $colors[$b->status] ?? 'gray';
                        @endphp
                        <div class="flex justify-between py-1.5 border-b border-gray-100 last:border-0">
                            <span class="font-mono text-xs text-gray-500">{{ $b->booking_reference }}</span>
                            <span class="text-xs text-gray-500">{{ $b->service_date->format('d M Y') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $bc }}-100 text-{{ $bc }}-700">
                                {{ ucfirst(str_replace('_',' ',$b->status)) }}
                            </span>
                            <span class="text-xs font-medium text-gray-700">₦{{ number_format($b->total_amount, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-gray-400">No bookings yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="mt-5">
                <button wire:click="closeModal"
                        class="w-full border border-gray-300 text-gray-600 hover:bg-gray-50 py-2 rounded-lg text-sm transition">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
