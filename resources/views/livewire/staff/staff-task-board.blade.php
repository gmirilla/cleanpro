<div>
    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['label'=>"Today's Jobs", 'val'=>$stats['today_jobs'],      'color'=>'blue'],
            ['label'=>'In Progress',  'val'=>$stats['in_progress'],     'color'=>'purple'],
            ['label'=>'Upcoming',     'val'=>$stats['upcoming_jobs'],   'color'=>'indigo'],
            ['label'=>'Total Done',   'val'=>$stats['completed_total'], 'color'=>'green'],
        ] as $s)
            <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-{{ $s['color'] }}-500">
                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $s['label'] }}</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $s['val'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <div class="flex gap-3 mb-4">
        <select wire:model.live="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">All Jobs</option>
            <option value="assigned">Assigned</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
        </select>
    </div>

    {{-- Job Cards --}}
    <div class="space-y-4">
        @forelse($bookings as $booking)
            @php
                $colors = ['assigned'=>'indigo','in_progress'=>'purple','completed'=>'green','confirmed'=>'blue'];
                $c = $colors[$booking->status] ?? 'gray';
            @endphp
            <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-{{ $c }}-400">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <span class="font-mono text-xs font-bold text-gray-500">{{ $booking->booking_reference }}</span>
                        <p class="font-semibold text-gray-800 mt-0.5">{{ $booking->customer->name }}</p>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-700">
                        {{ ucfirst(str_replace('_',' ',$booking->status)) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm text-gray-600 mb-4">
                    <div>📅 {{ $booking->service_date->format('D, d M Y') }}</div>
                    <div>⏰ {{ $booking->service_date->format('h:i A') }}</div>
                    <div>📍 {{ $booking->address?->city ?? 'Address on file' }}</div>
                    <div class="col-span-2 md:col-span-3">
                        🧺 {{ $booking->items->pluck('service.name')->join(', ') }}
                    </div>
                </div>

                @if($booking->notes)
                    <p class="text-xs text-gray-500 bg-gray-50 rounded p-2 mb-3">📝 {{ $booking->notes }}</p>
                @endif

                {{-- Action Buttons --}}
                <div class="flex flex-wrap gap-2">
                    @if($booking->status === 'assigned')
                        <button wire:click="startJob({{ $booking->id }})"
                                class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg transition">
                            ▶ Start Job
                        </button>
                    @endif
                    @if($booking->status === 'in_progress')
                        <button wire:click="completeJob({{ $booking->id }})"
                                class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg transition">
                            ✓ Mark Complete
                        </button>
                    @endif
                    @if(in_array($booking->status, ['in_progress','assigned']))
                        <button wire:click="openPhotoUpload({{ $booking->id }})"
                                class="border border-gray-300 hover:border-indigo-400 text-gray-600 hover:text-indigo-600 text-sm px-4 py-2 rounded-lg transition">
                            📷 Upload Photo
                        </button>
                    @endif
                </div>

                {{-- Photos --}}
                @if($booking->photos->count())
                    <div class="flex gap-2 mt-3 flex-wrap">
                        @foreach($booking->photos->take(4) as $photo)
                            <a href="{{ $photo->url }}" target="_blank">
                                <img src="{{ $photo->url }}" alt="{{ $photo->caption }}"
                                     class="w-14 h-14 rounded-lg object-cover border border-gray-200 hover:opacity-80 transition">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-12 text-gray-400">
                <p class="text-4xl mb-2">✅</p>
                <p>No jobs assigned yet.</p>
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $bookings->links() }}</div>

    {{-- Photo Upload Modal --}}
    @if($uploadingBookingId)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h2 class="text-lg font-bold mb-4">Upload Job Photo</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo Type</label>
                    <select wire:model="photoType" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="before">Before</option>
                        <option value="after">After</option>
                        <option value="issue">Issue</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo *</label>
                    <input wire:model="photo" type="file" accept="image/*"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    @error('photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-400 mt-1">Max 2MB. JPEG, PNG, WebP only.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                    <input wire:model="photoCaption" type="text"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex gap-3 mt-5">
                <button wire:click="uploadPhoto"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium">
                    Upload
                </button>
                <button wire:click="$set('uploadingBookingId', null)"
                        class="flex-1 border border-gray-300 text-gray-600 hover:bg-gray-50 py-2 rounded-lg text-sm">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>
