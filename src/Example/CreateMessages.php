<?php

Require_once 'Setup.php';

/** @var \Enqueue\Sqs\SqsMessage $messagePassed */
$messagePassed = $client->createMessage('Passing Message', ['type' => 'success']);
$messagePassed->setMessageGroupId('test1.1');
$messagePassed->setMessageDeduplicationId('test1.2');
$client->send($messagePassed);

/** @var \Enqueue\Sqs\SqsMessage $messageFailed */
$messageFailed = $client->createMessage('Failed Message', ['type' => 'failed']);
$messageFailed->setMessageGroupId('test2.1');
$messageFailed->setMessageDeduplicationId('test2.2');
$client->send($messageFailed);