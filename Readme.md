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
    'storedMessageRetryDelay' => 300
];
</pre>

#### Examples
##### Copy config files 
<pre> cp ./src/Example/Configconfig.php.example ./src/Example/config.php</pre>
Fill Config.php

#### Example files in 
<pre>
.src/Example/
