<?php

namespace App\Models;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;

class EventPublisher
{
    const MANAGEMENT_QUEUE = 'worker_management_queue';
    const MANAGEMENT_EXCHANGE = 'worker_management_queue';
    const CONNECTION_PARAMS = [
        'host' => 'host.docker.internal',
        'port' => '5672',
        'user' => 'root2',
        'password' => 'root2pass',
    ];
    private AMQPStreamConnection $connection;

    public function __construct()
    {
        try {
            $this->connection = new AMQPStreamConnection(
                self::CONNECTION_PARAMS['host'],
                self::CONNECTION_PARAMS['port'],
                self::CONNECTION_PARAMS['user'],
                self::CONNECTION_PARAMS['password']
            );
        } catch (\Throwable $tr) {
            var_dump($tr->getMessage());
            var_dump($tr->getTraceAsString());
            die();
        }

    }

    public function __destruct()
    {
        $this->connection->close();
    }

    public function publishEvent(array $data, int $idAccount): void
    {
        $queueKey = "account_$idAccount";
        $channel = $this->connection->channel();

        try {
            $channel->queue_declare($queueKey, true, true, false, true);
        } catch (AMQPProtocolChannelException $e) {
            $this->pushToManagementQueue($queueKey);
            $channel = $this->connection->channel();
            $channel->queue_declare($queueKey, false, true, false, true);
        }

        $channel->exchange_declare(
            $queueKey,
            AMQPExchangeType::DIRECT,
            false,
            true,
            true
        );
        $channel->queue_bind($queueKey, $queueKey);

        $message = new AMQPMessage(
            json_encode($data),
            [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]
        );

        $channel->basic_publish($message, $queueKey);
        $channel->close();
    }

    private function pushToManagementQueue(string $queueName): void
    {
        $channel = $this->connection->channel();
        $channel->queue_declare(
            self::MANAGEMENT_QUEUE,
            true,
            true,
            false,
            false
        );
        $channel->exchange_declare(
            self::MANAGEMENT_EXCHANGE,
            AMQPExchangeType::DIRECT,
            false,
            true,
            false
        );
        $channel->queue_bind(self::MANAGEMENT_QUEUE, self::MANAGEMENT_EXCHANGE);
        $channel->basic_publish(
            new AMQPMessage($queueName),
            self::MANAGEMENT_EXCHANGE
        );
        $channel->close();
    }
}