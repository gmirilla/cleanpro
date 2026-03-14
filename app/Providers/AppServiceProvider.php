<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\LaundryOrder;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Staff;
use App\Policies\BookingPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LaundryOrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ServicePolicy;
use App\Policies\StaffPolicy;
use App\Repositories\BookingRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\StaffRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Role-based login redirect
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        // Bind repositories
        $this->app->bind(BookingRepository::class,  fn($app) => new BookingRepository($app->make(Booking::class)));
        $this->app->bind(StaffRepository::class,    fn($app) => new StaffRepository($app->make(Staff::class)));
        $this->app->bind(CustomerRepository::class, fn($app) => new CustomerRepository($app->make(Customer::class)));
    }

    public function boot(): void
    {
        // Register policies
        Gate::policy(Booking::class,      BookingPolicy::class);
        Gate::policy(Service::class,      ServicePolicy::class);
        Gate::policy(Staff::class,        StaffPolicy::class);
        Gate::policy(Customer::class,     CustomerPolicy::class);
        Gate::policy(Invoice::class,      InvoicePolicy::class);
        Gate::policy(Payment::class,      PaymentPolicy::class);
        Gate::policy(LaundryOrder::class, LaundryOrderPolicy::class);
    }
}
