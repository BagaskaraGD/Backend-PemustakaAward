<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// Import Event dan Listener Anda di sini
use App\Events\AksaraDinamikaDisetujui;
use App\Listeners\UpdatePoinAksaraDinamika;
use App\Events\KehadiranKegiatanDicatat;
use App\Listeners\UpdatePoinKegiatan;
use App\Events\KunjunganDicatat;
use App\Listeners\UpdatePoinKunjungan;
use App\Events\PeminjamanDicatat;
use App\Listeners\UpdatePoinPeminjaman;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Event Aksara Dinamika
        AksaraDinamikaDisetujui::class => [
            UpdatePoinAksaraDinamika::class,
        ],

        // Event Kehadiran Kegiatan
        KehadiranKegiatanDicatat::class => [
            UpdatePoinKegiatan::class,
        ],

        // Event Kunjungan
        KunjunganDicatat::class => [
            UpdatePoinKunjungan::class,
        ],

        // Event Peminjaman
        PeminjamanDicatat::class => [
            UpdatePoinPeminjaman::class,
        ],

        // Anda bisa menambahkan event dan listener lain di sini jika ada
        // Contoh:
        // \App\Events\NamaEventLain::class => [
        //     \App\Listeners\NamaListenerLain::class,
        // ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false; // Biasanya false jika Anda mendaftarkan secara manual di atas
    }
}
