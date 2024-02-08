<?php
// include the library and use the necessary classes
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create connection
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    // Create a channel and declare a queue
    $channel->queue_declare('latest_queue', false, false, false, false);

    // Capture form data and encode as JSON
    $data = json_encode([
        'from' => $_POST['from_name'],
        'from_email' => $_POST['from_email'],
        'to_email' => $_POST['to_email'],
        'subject' => $_POST['subject'],
        'message' => $_POST['message'],
    ]);

    $msg = new AMQPMessage($data);
    $channel->basic_publish($msg, '', 'latest_queue');

    echo " [x] Email data sent\n";

    // Close channel and connection
    $channel->close();
    $connection->close();
}
