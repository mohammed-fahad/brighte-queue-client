<?php

require_once "vendor/autoload.php";

use BrighteCapital\QueueClient\queue\BrighteQueueClient;
use BrighteCapital\QueueClient\strategies\StorageRetryStrategy;
use Enqueue\Sqs\SqsMessage;

$config = [
    'key' => 'AKIAUQNGXHESCI4THQTD',
    'secret' => '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',
    'region' => 'ap-southeast-2',
    'queue' => 'fahad-queue.fifo',
//    'isFifo' => true,
    "provider" => "sqs",
    'database' => [
        'host' => '172.18.0.6',
        'user' => 'root',
        'password' => 'lksdoiwe09',
        'dbname' => 'brighte_prod',
        'provider' => 'MySql', // object / string ()
    ]
];

$queueClient = new BrighteQueueClient($config);
$msg = new SqsMessage("this is the body");
$msg->setMessageGroupId(9);
$msg->setMessageDeduplicationId(9);
$message = $queueClient->receive(20);
$queueClient->reject($message, new \BrighteCapital\QueueClient\strategies\Retry(15, 0, StorageRetryStrategy::class));
var_dump($message); die(0);
die(0);

echo "done";
