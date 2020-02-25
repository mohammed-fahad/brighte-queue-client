<?php

require_once 'Setup.php';

$jobManager = new \BrighteCapital\QueueClient\Example\JobManager();
$client->processMessage($jobManager);
