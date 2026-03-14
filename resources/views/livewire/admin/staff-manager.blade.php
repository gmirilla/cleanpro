<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search staff…"
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-56 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
            <select wire:model.live="availabilityFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Availability</option>
                <option value="available">Available</option>
                <option value="busy">Busy</option>
                <option value="off_duty">Off Duty</option>
            </select>
        </div>
        <button wire:click="openCreate" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Add Staff
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($staffMembers as $staff)
            @php
                $avail = ['available'=>'green','busy'=>'yellow','off_duty'=>'red'];
                $c = $avail[$staff->availability_status] ?? 'gray';
            @endphp
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-600">
                        {{ strtoupper(substr($staff->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $staff->user->name }}</p>
                        <p class="text-xs text-gray-400">{{ $staff->position }}</p>
                    </div>
                    <span class="ml-auto px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-700">
                        {{ ucfirst(str_replace('_',' ',$staff->availability_status)) }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs text-gray-500 mb-4">
                    <div>📞 {{ $staff->phone ?? 'N/A' }}</div>
                    <div>⭐ Rating: {{ number_format($staff->rating, 1) }}</div>
                    <div>✅ Jobs: {{ $staff->completed_jobs }}</div>
                    <div>📧 {{ $staff->user->email }}</div>
                </div>
                <div class="flex gap-2">
                    <button wire:click="openEdit({{ $staff->id }})"
                            class="flex-1 text-xs border border-gray-300 hover:border-indigo-400 text-gray-600 hover:text-indigo-600 py-1.5 rounded-lg">Edit</button>
                    <button wire:click="$set('viewingId', {{ $staff->id }})"
                            class="flex-1 text-xs border border-blue-200 hover:border-blue-400 text-blue-500 hover:text-blue-700 py-1.5 rounded-lg">View</button>
                    <button wire:click="delete({{ $staff->id }})" wire:confirm="Delete this staff member?"
                            class="flex-1 text-xs border border-red-200 hover:border-red-400 text-red-400 hover:text-red-600 py-1.5 rounded-lg">Delete</button>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-12 text-gray-400">
                <p class="text-4xl mb-2">👷</p>
                <p>No staff members yet.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $staffMembers->links() }}</div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
            <h2 class="text-lg font-bold mb-5">{{ $editingId ? 'Edit Staff' : 'Add Staff Member' }}</h2>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input wire:model="name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email {{ $editingId ? '' : '*' }}</label>
                        <input wire:model="email" type="email" {{ $editingId ? 'disabled' : '' }}
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none {{ $editingId ? 'bg-gray-50' : '' }}">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input wire:model="phone" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                        <input wire:model="position" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shift Start</label>
                        <input wire:model="shift_start" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shift End</label>
                        <input wire:model="shift_end" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Working Days</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                            <label class="flex items-center gap-1 text-sm">
                                <input type="checkbox" wire:model="working_days" value="{{ $day }}"
                                       class="rounded border-gray-300 text-indigo-600">
                                {{ ucfirst(substr($day, 0, 3)) }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button wire:click="save" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium">
                    {{ $editingId ? 'Update' : 'Create' }}
                </button>
                <button wire:click="closeModal" class="flex-1 border border-gray-300 text-gray-600 hover:bg-gray-50 py-2 rounded-lg text-sm">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>
