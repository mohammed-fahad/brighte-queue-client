<?php

require_once "vendor/autoload.php";
require_once "Config.php";

use BrighteCapital\QueueClient\Example\MySql;
use BrighteCapital\QueueClient\Queue\QueueClient;
use Doctrine\DBAL\DriverManager;

$connectionParams = [
    'dbname' => $config['dbname'],
    'user' => $config['user'],
    'password' => $config['password'],
    'host' => $config['host'],
    'driver' => 'pdo_mysql'
];
$storage = new MySql(DriverManager::getConnection($connectionParams));

if (!$storage->messageTableExist()) {
    $storage->createMessageTable();
}

$client = new QueueClient($config, null, null, $storage);
/** @var \BrighteCapital\QueueClient\Queue\QueueClient $client */
