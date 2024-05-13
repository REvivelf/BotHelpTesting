<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Models\EventGenerator;
use Symfony\Component\HttpClient\HttpClient;
#[AsCommand(name: 'app:send-events')]
class SendEventsCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $script = "/var/www/bothelp/bin/scripts/QueueWorker.php observer";
        //Запуск мониторинга новых очередей
        exec("php $script > /dev/null &");

        $client = HttpClient::create();
        $t0 = microtime(true);

        foreach (EventGenerator::generateEvents(1000, 10000) as $event) {
            $client->request(
                'POST',
                'http://host.docker.internal:6691/event/register',
                ['json' => $event]
            );
        }
        $t1 = microtime(true);
        var_dump($t1 - $t0);
        return Command::SUCCESS;
    }
}