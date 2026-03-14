<?php

namespace App\Livewire\Admin;

use App\Services\DashboardService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard')]
class CompanyDashboard extends Component
{
    public array $stats = [];

    public function mount(DashboardService $dashboardService): void
    {
        $this->stats = $dashboardService->getAdminStats();
    }

    public function render()
    {
        return view('livewire.admin.company-dashboard');
    }
}
