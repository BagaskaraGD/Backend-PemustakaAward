<?php

namespace App\Listeners;

use App\Events\PeminjamanDicatat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdatePoinPeminjaman
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
    public function handle(PeminjamanDicatat $event): void
    {
        //
    }
}
