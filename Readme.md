#Brighte Queue Package
## Sample code
#### Config
<pre>
$config = [
    'key' => 'AKIAUQNGXHESCI4THQTD',
    'secret' => 'gqbJdJZVt611sj4+qZDJZSIlCHAK511icZNFpn+Q',
    'region' => 'ap-southeast-2',
    'queue' => 'fahad-queue.fifo',
    'provider' => 'sqs',
    'retryStrategy' => [
      'storedMessageRetryDelay' => 300
    ],
    'database' => [
        'host' => '172.18.0.6',
        'user' => 'root',
        'password' => 'lksdoiwe09',
        'dbname' => 'brighte_prod',
        'provider' => 'MySql', // object / string ()
    ]
];
</pre>