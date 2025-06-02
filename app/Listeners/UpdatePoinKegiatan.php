<?php

namespace App\Listeners;

use App\Events\KehadiranKegiatanDicatat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdatePoinKegiatan
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(KehadiranKegiatanDicatat $event): void
    {
        //
    }
}
