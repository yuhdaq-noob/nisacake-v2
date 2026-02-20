<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Navbar extends Component
{
    /**
     * Kunci seksi aktif (kasir|gudang|laporan)
     *
     * @var string|null
     */
    public $active;

    /**
     * Array link opsional untuk menimpa default
     *
     * @var array|null
     */
    public $links;

    /**
     * Menentukan apakah tombol logout ditampilkan
     *
     * @var bool
     */
    public $showLogout;

    /**
     * Buat instance komponen baru.
     */
    public function __construct($active = null, $links = null, $showLogout = true)
    {
        $this->active = $active;
        $this->links = $links;
        $this->showLogout = $showLogout;
    }

    /**
     * Dapatkan view / isi yang merepresentasikan komponen.
     */
    public function render(): View|Closure|string
    {
        return view('components.navbar');
    }
}
