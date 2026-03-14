<?php

namespace App\Console;

use App\Jobs\CheckOverdueInvoicesJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Check overdue invoices every day at 9am
        $schedule->job(new CheckOverdueInvoicesJob)
            ->dailyAt('09:00')
            ->name('check-overdue-invoices')
            ->withoutOverlapping();

        // Auto-assign pending bookings without staff every 15 minutes
        $schedule->command('bookings:auto-assign')
            ->everyFifteenMinutes()
            ->name('auto-assign-staff')
            ->withoutOverlapping();

        // Clean up old unread notifications weekly
        $schedule->command('notifications:prune')
            ->weekly()
            ->sundays()
            ->at('02:00');

        // Prune stale queue jobs older than 7 days
        $schedule->command('queue:prune-failed', ['--hours=168'])
            ->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
