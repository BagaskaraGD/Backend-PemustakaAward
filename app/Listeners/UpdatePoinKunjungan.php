<?php

namespace App\Listeners;

use App\Events\KunjunganDicatat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdatePoinKunjungan
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
    public function handle(KunjunganDicatat $event): void
    {
        //
    }
}
