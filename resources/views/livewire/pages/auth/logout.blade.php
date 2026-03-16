<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function logout()
    {
        Auth::logout();

        return redirect('/');
    }
};
?>

<form method="POST" wire:submit="logout">
    @csrf
</form>
