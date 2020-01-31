<?php


require_once "vendor/autoload.php";

use BrighteCapital\QueueClient\queue\factories\QueueClientFactory;
use Enqueue\Sqs\SqsMessage;

$config = [
    'key' => 'Key here',
    'secret' => 'secret here',
    'region' => 'region',
    'queue' => 'queue name here',
    'isFifo' => true,
    "provider" => "sqs"
];


$sqsClient = QueueClientFactory::create($config);

$msg = new SqsMessage("this is a test");

$msg->setMessageGroupId(9);
$msg->setMessageDeduplicationId(9);

$sqsClient->send($msg);

echo "done";
