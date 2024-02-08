<?php
// include the library and use the necessary classes
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
// create a connection to the server
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
//create a channel
$channel->queue_declare('Php queue', false, false, false, false);

$msg = new AMQPMessage('Hello World!');
$channel->basic_publish($msg, '', 'Php queue');

echo " [x] Sent 'Hello World!'\n";
// close the channel and the connection
$channel->close();
$connection->close();
