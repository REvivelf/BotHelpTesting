<?php

namespace App\Models;

class EventGenerator
{
    public static function generateEvents(int $accountCount = 10, int $eventsCount = 10): \Generator
    {
        for ($i=0;$i<$eventsCount;$i++) {
            $idAcc = random_int(1,$accountCount);
            $id = $i+1;
            yield [
                'id' => $id,
                'idAccount' => $idAcc,
                'message' => "Event num {$id} for account {$idAcc}",
            ];
        }
    }
}