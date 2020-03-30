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
    'defaultMaxDelay' => 300
];
</pre>

#### Examples

##### Copy config files 
<pre> cp ./src/Example/Config.php.example ./src/Example/Config.php</pre>

Fill Config.php

##### Example files in 
<pre>
.src/Example/
</pre>