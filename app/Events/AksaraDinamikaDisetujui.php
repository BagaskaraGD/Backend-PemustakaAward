<?php

// Contoh untuk app/Events/AksaraDinamikaDisetujui.php
namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AksaraDinamikaDisetujui
{
    use Dispatchable, SerializesModels;
    public $nim;
    // Anda bisa menambahkan parameter lain jika diperlukan, misal $idAksaraDinamika
    public function __construct($nim)
    {
        $this->nim = $nim;
    }
}
