<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('custom_queue', false, false, false, false);

$data = json_encode($_POST);

$msg = new AMQPMessage($data, array('delivery_mode' => 2));
$channel->basic_publish($msg, '', 'custom_queue');

header('Location: form.php?sent=true');
