<?php


require_once "vendor/autoload.php";

use App\queue\factories\QueueClientFactory;
use App\queue\SqsClient;
use Brighte\Sqs\SqsMessage;

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

//$strategy = new StrategyA(new FailedMessage($msg, 6, 4));


$sqsClient->reject($msg);

exit("stop");

