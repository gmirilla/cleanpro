<?php

namespace App\Livewire\Admin;

use App\Repositories\BookingRepository;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Calendar')]
class BookingCalendar extends Component
{
    public string $currentYear;
    public string $currentMonth;

    public function mount(): void
    {
        $this->currentYear  = now()->format('Y');
        $this->currentMonth = now()->format('m');
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentYear  = $date->format('Y');
        $this->currentMonth = $date->format('m');
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentYear  = $date->format('Y');
        $this->currentMonth = $date->format('m');
    }

    public function render(BookingRepository $repo)
    {
        $bookings   = $repo->forCalendar($this->currentYear, $this->currentMonth);
        $firstDay   = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $startDow   = $firstDay->dayOfWeek;

        $calendarDays = array_fill(0, $startDow, null);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%s-%s-%02d', $this->currentYear, $this->currentMonth, $d);
            $calendarDays[] = [
                'day'      => $d,
                'date'     => $dateStr,
                'bookings' => $bookings->filter(fn($b) => $b->service_date->format('Y-m-d') === $dateStr)->values(),
            ];
        }

        return view('livewire.admin.booking-calendar', [
            'calendarDays' => $calendarDays,
            'monthLabel'   => $firstDay->format('F Y'),
        ]);
    }
}
