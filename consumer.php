<?php
// Include the library and use the necessary classes
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

// Create a connection
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare the same queue as the producer
$channel->queue_declare('new_queue', false, false, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

// Callback function to process received messages
$callback = function ($msg) {
    echo ' [x] Received ', $msg->body, "\n";

    // Decode the message body
    $data = json_decode($msg->body, true);

    // Email sending logic
    $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
        ->setUsername('dorcasifeoluwa07@gmail.com')
        ->setPassword('ceis ltli bjux jrce');

    // Create the Mailer using your created Transport
    $mailer = new Swift_Mailer($transport);

    // Create a message
    $message = (new Swift_Message($data['subject']))
        ->setFrom([$data['from_email'] => $data['from']])
        ->setTo([$data['to_email']])
        ->setBody($data['message']);

    // Send the message
    try {
        $result = $mailer->send($message);
        echo " [x] Email sent\n";
    } catch (Exception $e) {
        echo " [!] Error sending email: " . $e->getMessage() . "\n";
    }
};

// Consume messages from the queue
$channel->basic_consume('new_queue', '', false, true, false, false, $callback);

// Keep listening for messages until [CTRL+C]
while (count($channel->callbacks)) {
    $channel->wait();
}

// Close channel and connection
$channel->close();
$connection->close();
