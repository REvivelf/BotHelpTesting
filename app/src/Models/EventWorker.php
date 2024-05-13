<?php

namespace App\Models;

class EventWorker
{
    public static function processMessage($message): void
    {
        sleep(1);
    }
}