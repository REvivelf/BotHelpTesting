<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Exception\AMQPNoDataException;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Exception\AMQPBasicCancelException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use App\Models\EventWorker;


$isObserver = $_SERVER['argv'][1] === 'observer';

function processMessageObserver(AMQPMessage $message): void
{
    $script = "/var/www/bothelp/bin/scripts/QueueWorker.php " . $message->body;
    //Запуск воркера
    exec("php $script > /dev/null &");

    $message->ack();
}

function processMessage(AMQPMessage $message): void
{
    EventWorker::processMessage($message->body);

    $message->ack();

    $timeout = 5;

    while(count($message->getChannel()->callbacks)) {
        try {
            $message->getChannel()->wait(null, false, $timeout);
        } catch (AMQPTimeoutException $exception) {
            $message->getChannel()->queue_delete(queue: $message->getRoutingKey(), if_empty: true);
        } catch (AMQPNoDataException $exception) {
            continue;
        } catch (AMQPProtocolChannelException $exception) {
            exit();
        }
    }
}

function shutdown($channel, $connection)
{
    $channel->close();
    $connection->close();
}

$queueName = $isObserver ? 'worker_management_queue' : $_SERVER['argv'][1];
$consumerTag = $isObserver ? 'SuperVisor' : $_SERVER['argv'][1] . '_worker';
$connection = new AMQPStreamConnection('host.docker.internal','5672','root2','root2pass');
$channel = $connection->channel();

register_shutdown_function('shutdown', $channel, $connection);

$channel->queue_declare($queueName, true, true, false, !$isObserver);
$channel->exchange_declare($queueName, AMQPExchangeType::DIRECT, true, true, !$isObserver);
$channel->queue_bind($queueName, $queueName);
$channel->basic_consume(
    $queueName,
    $consumerTag,
    false,
    false,
    false,
    false,
    $isObserver ? 'processMessageObserver' : 'processMessage'
);
try {
    $channel->consume();
} catch (AMQPBasicCancelException $exception) {
    exit();
}