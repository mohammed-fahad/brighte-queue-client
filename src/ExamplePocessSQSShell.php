<?php

require_once "vendor/autoload.php";

use BrighteCapital\QueueClient\queue\BrighteQueueClient;
use Enqueue\Sqs\SqsMessage;

$config = [
    'key' => 'Key here',
    'secret' => 'secret here',
    'region' => 'region',
    'queue' => 'queue name here',
    'isFifo' => true,
    "provider" => "sqs"
];

$queueClient = new BrighteQueueClient($config);
$msg = new SqsMessage("this is the body");

$msg->setMessageGroupId(9);
$msg->setMessageDeduplicationId(9);

$queueClient->send($msg);

echo "done";
