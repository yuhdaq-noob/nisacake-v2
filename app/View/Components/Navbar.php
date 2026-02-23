<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

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
     * Mengaktifkan mode gelap untuk navbar
     *
     * @var bool
     */
    public $dark;

    /**
     * Data user yang login (untuk profil)
     *
     * @var \App\Models\User|null
     */
    public $user;

    /**
     * Default links untuk navigasi
     *
     * @var array
     */
    public $defaultLinks;

    /**
     * Buat instance komponen baru.
     */
    public function __construct(
        $active = null,
        $links = null,
        $showLogout = true,
        $dark = false
    ) {
        $this->active = $active;
        $this->links = $links;
        $this->showLogout = $showLogout;
        $this->dark = $dark;
        $this->user = Auth::user();
        $this->defaultLinks = $this->buildDefaultLinks();
    }

    /**
     * Bangun array link navigasi default
     *
     * @return array
     */
    protected function buildDefaultLinks(): array
    {
        return [
            [
                'label' => 'Kasir',
                'href' => route('kasir'),
                'key' => 'kasir',
                'icon' => 'bi bi-cash-stack',
            ],
            [
                'label' => 'Gudang',
                'href' => route('gudang'),
                'key' => 'gudang',
                'icon' => 'bi bi-boxes',
            ],
            [
                'label' => 'Laporan',
                'href' => route('laporan'),
                'key' => 'laporan',
                'icon' => 'bi bi-graph-up-arrow',
            ],
        ];
    }

    /**
     * Dapatkan view yang merepresentasikan komponen.
     */
    public function render(): View|Closure|string
    {
        return view('components.navbar');
    }
}
