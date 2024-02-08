<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
// we open a connection and a channel, and declare the queue from
//  which we're going to consume.
// this matches up with the queue that 'send' publishes to
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('Php queue', false, false, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";
// define a PHP callable that will receive the messages sent by the server.
$callback = function ($msg) {
    echo ' [x] Received ', $msg->body, "\n";
};

$channel->basic_consume('Php queue', '', false, true, false, false, $callback);

// try {
//     // $channel->consume();
// } catch (\Throwable $exception) {
//     echo $exception->getMessage();
// }

while ($channel->is_consuming()) {
    $channel->wait();
}
$channel->close();
$connection->close();
