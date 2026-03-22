<div>
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your personal information, addresses, and security settings.</p>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl w-fit mb-8">
        @foreach(['profile' => '👤 Personal Info', 'addresses' => '📍 Addresses', 'security' => '🔒 Security'] as $tab => $label)
            <button wire:click="$set('activeTab', '{{ $tab }}')"
                    class="px-5 py-2 text-sm font-medium rounded-lg transition-all duration-150
                        {{ $activeTab === $tab
                            ? 'bg-white text-indigo-700 shadow-sm font-semibold'
                            : 'text-gray-500 hover:text-gray-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ════════════════════════════════════════════
         TAB: PERSONAL INFO
    ════════════════════════════════════════════ --}}
    @if($activeTab === 'profile')
    <div class="max-w-2xl">
        {{-- Avatar / name hero --}}
        <div class="flex items-center gap-5 bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-2xl p-6 mb-6 text-white">
            <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center text-2xl font-bold shadow-inner">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-xl font-bold">{{ auth()->user()->name }}</p>
                <p class="text-indigo-200 text-sm">{{ auth()->user()->email }}</p>
                <p class="text-indigo-200 text-xs mt-0.5 capitalize">
                    Member since {{ auth()->user()->created_at->format('M Y') }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-5">Personal Information</h3>

            <div class="space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name *</label>
                        <input wire:model="name" type="text"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none transition">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                        <input wire:model="phone" type="tel" placeholder="+234 800 000 0000"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none transition">
                        @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address *</label>
                    <input wire:model="email" type="email"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none transition">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes / Preferences</label>
                    <textarea wire:model="notes" rows="3" placeholder="Any preferences or notes for our staff…"
                              class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none transition resize-none"></textarea>
                    @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end mt-6 pt-5 border-t border-gray-100">
                <button wire:click="saveProfile"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5 rounded-xl text-sm font-semibold transition shadow-sm">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════════
         TAB: ADDRESSES
    ════════════════════════════════════════════ --}}
    @if($activeTab === 'addresses')
    <div class="max-w-2xl">
        <div class="flex justify-between items-center mb-5">
            <p class="text-sm text-gray-500">Manage your saved delivery addresses.</p>
            <button wire:click="openCreateAddress"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition flex items-center gap-1.5 shadow-sm">
                <span class="text-base leading-none">＋</span> Add Address
            </button>
        </div>

        @if($addresses->isEmpty())
            <div class="bg-white rounded-2xl border border-dashed border-gray-300 p-12 text-center">
                <p class="text-4xl mb-3">📍</p>
                <p class="text-gray-500 font-medium">No addresses saved yet.</p>
                <p class="text-sm text-gray-400 mt-1 mb-4">Add a delivery address to speed up bookings.</p>
                <button wire:click="openCreateAddress"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-xl text-sm font-medium transition">
                    Add Your First Address
                </button>
            </div>
        @else
            <div class="space-y-3">
                @foreach($addresses as $address)
                    <div class="bg-white rounded-2xl border {{ $address->is_default ? 'border-indigo-300 shadow-md shadow-indigo-100' : 'border-gray-100 shadow-sm' }} p-5 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl {{ $address->is_default ? 'bg-indigo-100' : 'bg-gray-100' }} flex items-center justify-center text-lg flex-shrink-0">
                                    {{ match($address->label) { 'Home' => '🏠', 'Office' => '🏢', default => '📍' } }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-gray-800">{{ $address->label }}</p>
                                        @if($address->is_default)
                                            <span class="bg-indigo-100 text-indigo-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                                Default
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 mt-0.5">{{ $address->address }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $address->city }}, {{ $address->state }}{{ $address->postal_code ? ' ' . $address->postal_code : '' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 ml-4 flex-shrink-0">
                                @if(!$address->is_default)
                                    <button wire:click="setDefaultAddress({{ $address->id }})"
                                            class="text-xs text-gray-400 hover:text-indigo-600 border border-gray-200 hover:border-indigo-300 px-2.5 py-1.5 rounded-lg transition">
                                        Set Default
                                    </button>
                                @endif
                                <button wire:click="openEditAddress({{ $address->id }})"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:border-indigo-400 px-2.5 py-1.5 rounded-lg transition">
                                    Edit
                                </button>
                                <button wire:click="deleteAddress({{ $address->id }})"
                                        wire:confirm="Delete this address?"
                                        class="text-xs text-red-400 hover:text-red-600 border border-red-200 hover:border-red-400 px-2.5 py-1.5 rounded-lg transition">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Address Modal --}}
        @if($showAddressModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-gray-800">
                        {{ $editingAddressId ? 'Edit Address' : 'Add New Address' }}
                    </h2>
                    <button wire:click="closeAddressModal" class="text-gray-400 hover:text-gray-600 text-xl leading-none">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Label *</label>
                        <select wire:model="addr_label"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            <option value="Home">🏠 Home</option>
                            <option value="Office">🏢 Office</option>
                            <option value="Other">📍 Other</option>
                        </select>
                        @error('addr_label') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Street Address *</label>
                        <input wire:model="addr_address" type="text" placeholder="15 Admiralty Way, Lekki Phase 1"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        @error('addr_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">City *</label>
                            <input wire:model="addr_city" type="text" placeholder="Lagos"
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            @error('addr_city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">State *</label>
                            <input wire:model="addr_state" type="text" placeholder="Lagos"
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            @error('addr_state') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Postal Code</label>
                        <input wire:model="addr_postal_code" type="text" placeholder="100001"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>

                    <label class="flex items-center gap-3 cursor-pointer bg-gray-50 rounded-xl p-3.5">
                        <input wire:model="addr_is_default" type="checkbox"
                               class="rounded border-gray-300 text-indigo-600 w-4 h-4">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Set as default address</p>
                            <p class="text-xs text-gray-400">Used automatically for new bookings</p>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="saveAddress"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-sm font-semibold transition">
                        {{ $editingAddressId ? 'Save Changes' : 'Add Address' }}
                    </button>
                    <button wire:click="closeAddressModal"
                            class="flex-1 border border-gray-300 text-gray-600 hover:bg-gray-50 py-2.5 rounded-xl text-sm transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ════════════════════════════════════════════
         TAB: SECURITY
    ════════════════════════════════════════════ --}}
    @if($activeTab === 'security')
    <div class="max-w-2xl space-y-4">

        {{-- Account info card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">Account Information</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between items-center py-3 border-b border-gray-50">
                    <span class="text-gray-500">Account Type</span>
                    <span class="font-medium text-gray-700 capitalize px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs">
                        Customer
                    </span>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-50">
                    <span class="text-gray-500">Email Verified</span>
                    @if(auth()->user()->email_verified_at)
                        <span class="text-green-600 font-medium text-xs flex items-center gap-1">
                            <span>✓</span> Verified {{ auth()->user()->email_verified_at->format('d M Y') }}
                        </span>
                    @else
                        <span class="text-orange-500 font-medium text-xs">⚠ Not verified</span>
                    @endif
                </div>
                <div class="flex justify-between items-center py-3">
                    <span class="text-gray-500">Member Since</span>
                    <span class="font-medium text-gray-700">{{ auth()->user()->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Change password --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-800">Change Password</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Use a strong, unique password to keep your account safe.</p>
                </div>
                <button wire:click="$toggle('showPasswordSection')"
                        class="text-sm text-indigo-600 hover:text-indigo-800 border border-indigo-200 hover:border-indigo-400 px-3 py-1.5 rounded-lg transition">
                    {{ $showPasswordSection ? 'Cancel' : 'Change' }}
                </button>
            </div>

            @if($showPasswordSection)
            <div class="space-y-4 border-t border-gray-100 pt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Current Password *</label>
                    <input wire:model="current_password" type="password"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password *</label>
                        <input wire:model="new_password" type="password"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        @error('new_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm New Password *</label>
                        <input wire:model="new_password_confirm" type="password"
                               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        @error('new_password_confirm') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex justify-end">
                    <button wire:click="savePassword"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-2.5 rounded-xl text-sm font-semibold transition shadow-sm">
                        Update Password
                    </button>
                </div>
            </div>
            @else
            <div class="flex items-center gap-3 bg-gray-50 rounded-xl p-4 text-sm text-gray-500">
                <span class="text-2xl">🔑</span>
                <span>Password last changed: not available. Click "Change" to update it.</span>
            </div>
            @endif
        </div>

        {{-- Danger zone --}}
        <div class="bg-red-50 rounded-2xl border border-red-100 p-6">
            <h3 class="text-base font-semibold text-red-800 mb-1">Danger Zone</h3>
            <p class="text-sm text-red-600 mb-4">These actions are permanent and cannot be undone.</p>
            <a href="{{ route('profile') }}"
               class="text-sm text-red-600 hover:text-red-800 border border-red-300 hover:border-red-500 px-4 py-2 rounded-lg transition inline-flex items-center gap-2">
                🗑 Delete Account
            </a>
        </div>

    </div>
    @endif
</div>
