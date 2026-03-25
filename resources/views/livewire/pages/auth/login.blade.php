<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(
            default: auth()->user()->getDashboardRoute(),
            navigate: true
        );
    }
}; ?>

<form wire:submit.prevent="login" class="space-y-4" autocomplete="on">

    <div>
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input wire:model.defer="form.email" id="email"
            class="block mt-1 w-full"
            type="email"
            name="email"
            required autofocus autocomplete="username" />
        <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="__('Password')" />
        <x-text-input wire:model.defer="form.password" id="password"
            class="block mt-1 w-full"
            type="password"
            name="password"
            required autocomplete="current-password" />
        <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
    </div>

    <label for="remember" class="flex items-center">
        <input wire:model.defer="form.remember" id="remember" name="remember"
            type="checkbox" value="1"
            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <span class="ms-2 text-sm text-gray-600">Remember me</span>
    </label>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

        <div class="flex gap-3">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate
                   class="text-sm text-gray-600 hover:text-gray-900 underline">
                    Forgot password?
                </a>
            @endif

            @if (Route::has('register'))
                <a  href="{{ route('register') }}" wire:navigate
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md text-sm">
                    Register
                </a>
            @endif
        </div>

        <x-primary-button wire:loading.attr="disabled" class="flex items-center gap-2">
            <span wire:loading.remove>Log in</span>
            <span wire:loading>Logging in...</span>
        </x-primary-button>

    </div>

</form>