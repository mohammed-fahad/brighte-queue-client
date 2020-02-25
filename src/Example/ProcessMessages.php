<?php

Require_once 'Setup.php';

$jobManager = new \BrighteCapital\QueueClient\Example\JobManager();
$client->processMessage($jobManager);