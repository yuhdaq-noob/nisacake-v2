<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component
{
    /**
     * Buat instance komponen baru.
     */
    public function __construct()
    {
        //
    }

    /**
     * Dapatkan view / isi yang merepresentasikan komponen.
     */
    public function render(): View|Closure|string
    {
        return view('components.alert');
    }
}
