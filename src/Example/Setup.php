<?php

require_once "vendor/autoload.php";
require_once "Config.php";

use BrighteCapital\QueueClient\Example\MySql;
use BrighteCapital\QueueClient\Queue\QueueClient;
use Doctrine\DBAL\DriverManager;

$connectionParams = [
    'dbname' => $config['storage']['dbname'],
    'user' => $config['storage']['user'],
    'password' => $config['storage']['password'],
    'host' => $config['storage']['host'],
    'driver' => 'pdo_mysql'
];
$storage = new MySql(DriverManager::getConnection($connectionParams));

if (!$storage->messageTableExist()) {
    $storage->createMessageTable();
}

$notification = new \BrighteCapital\QueueClient\Notifications\Channels\SlackNotificationChannel($config['notification']['slack']['url']);
$client = new QueueClient($config, null, $notification, $storage);
/** @var \BrighteCapital\QueueClient\Queue\QueueClient $client */
