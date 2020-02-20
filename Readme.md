#Brighte Queue Package
## Sample code
#### Config
<pre>
$config = [
    'key' => '',
    'secret' => '',
    'region' => 'ap-southeast-2',
    'queue' => 'fahad-queue.fifo',
    'provider' => 'sqs',
    'retryStrategy' => [
      'storedMessageRetryDelay' => 300
    ],
    'storage' => [
        'host' => '',
        'user' => '',
        'password' => '',
        'dbname' => '',
        'provider' => 'MySql', // object/MySql
    ]
];
</pre>