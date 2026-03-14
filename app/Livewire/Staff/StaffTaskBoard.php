<?php

namespace App\Livewire\Staff;

use App\Models\Booking;
use App\Models\JobPhoto;
use App\Repositories\BookingRepository;
use App\Services\BookingService;
use App\Services\DashboardService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.staff')]
#[Title('My Tasks')]
class StaffTaskBoard extends Component
{
    use WithPagination, WithFileUploads;

    public string $statusFilter='';
    public ?int   $uploadingBookingId=null;
    public        $photo=null;
    public string $photoType='after', $photoCaption='';

    protected function rules(): array
    {
        return [
            'photo'       => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'photoType'   => 'required|in:before,after,issue',
            'photoCaption'=> 'nullable|string|max:255',
        ];
    }

    public function startJob(int $bookingId, BookingService $bookingService): void
    {
        $booking=Booking::findOrFail($bookingId);
        $bookingService->updateStatus($booking,'in_progress');
        $this->dispatch('notify',type:'success',message:'Job started!');
    }

    public function completeJob(int $bookingId, BookingService $bookingService): void
    {
        $booking=Booking::findOrFail($bookingId);
        $bookingService->updateStatus($booking,'completed');
        $this->dispatch('notify',type:'success',message:'Job completed.');
    }

    public function openPhotoUpload(int $bookingId): void
    {
        $this->uploadingBookingId=$bookingId;
        $this->photo=null;
        $this->photoType='after';
        $this->photoCaption='';
    }

    public function uploadPhoto(): void
    {
        $this->validate();
        $path=$this->photo->store('job_photos','public');
        JobPhoto::create([
            'booking_id'  => $this->uploadingBookingId,
            'uploaded_by' => auth()->id(),
            'path'        => $path,
            'type'        => $this->photoType,
            'caption'     => $this->photoCaption,
        ]);
        $this->uploadingBookingId=null;
        $this->photo=null;
        $this->dispatch('notify',type:'success',message:'Photo uploaded.');
    }

    public function render(BookingRepository $repo, DashboardService $dashboardService)
    {
        $staffId  = auth()->user()->staffProfile?->id;
        $bookings = $repo->forStaff($staffId, ['status'=>$this->statusFilter]);
        $stats    = $dashboardService->getStaffStats($staffId);
        return view('livewire.staff.staff-task-board', compact('bookings','stats'));
    }
}
