<?php
// include the library and use the necessary classes
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Swift_SmtpTransport;
use Swift_Mailer;
use php;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('email_queue', false, false, false, false);
// retreive form data

// 
echo ' * Waiting for messages. To exit press CTRL+C', "\n";

$callback = function ($msg) {

    echo " * Message received", "\n";
    $data = json_decode($msg->body, true);

    $from = $data['from'];
    $from_email = $data['from_email'];
    $to_email = $data['to_email'];
    $subject = $data['subject'];
    $message = $data['message'];

    $transporter = (new Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl'))
        ->setUsername('dorcasifeoluwa07@gmail.com')
        ->setPassword('XXXXXXX');

    $mailer = new Swift_Mailer($transporter);

    $message = (new Swift_Message($subject))
        ->setFrom([$from_email => $from])
        ->setTo([$to_email])
        ->setBody($message);

    $mailer->send($message);

    echo " * Message was sent", "\n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('email_queue', '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    $channel->wait();
}
