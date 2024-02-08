<?php
// Include the library and use the necessary classes
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;

// Establish a connection to the server
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare a queue
$channel->queue_declare('email_queue', false, false, false, false);

echo ' * Waiting for messages. To exit press CTRL+C', "\n";

// Define a callback function to process received messages
$callback = function ($msg) {
    echo " * Message received", "\n";

    // Decode the JSON data from the message body
    $data = json_decode($msg->body, true);

    // Extract email details from the data
    $from = $data['from'] ?? '';
    $from_email = $data['from_email'] ?? '';
    $to_email = $data['to_email'] ?? '';
    $subject = $data['subject'] ?? '';
    $messageContent = $data['message'] ?? '';

    // Set up the transporter for Swift Mailer using SMTP
    $transporter = (new Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl'))
        ->setUsername('dorcasifeoluwa07@gmail.com')
        ->setPassword('Ifeluwa07');

    // Create the Mailer using the created Transporter
    $mailer = new Swift_Mailer($transporter);

    // Create the message
    $message = (new Swift_Message($subject))
        ->setFrom([$from_email => $from])
        ->setTo([$to_email])
        ->setBody($messageContent);

    // Send the email
    try {
        $result = $mailer->send($message);
        echo " * Message was sent successfully\n";
    } catch (Exception $e) {
        echo " * Error sending email: " . $e->getMessage() . "\n";
    }

    // Acknowledge the message
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

// Set up the channel to consume messages
$channel->basic_qos(null, 1, null);
$channel->basic_consume('email_queue', '', false, false, false, false, $callback);

// Start a loop to wait for incoming messages
while (count($channel->callbacks)) {
    $channel->wait();
}

// Close the channel and the connection
$channel->close();
$connection->close();
