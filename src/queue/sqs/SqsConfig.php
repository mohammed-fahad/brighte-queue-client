<?php

namespace App\queue\sqs;

class SqsConfig
{
    /** @var string $key aws key */
    protected $key;

    /** @var string $secret aws secret */
    protected $secret;

    /** @var string $region aws region */
    protected $region;

    /** @var string $queue queue name */
    protected $queue;

    /** @var bool $fifo is fifo queue */
    protected $fifo = false;

    public function __construct(array $config)
    {
        $this->key = $config['key'] ?? null;
        $this->secret = $config['secret'] ?? null;
        $this->region = $config['region'] ?? null;
        $this->queue = $config['queue'] ?? null;
        $this->fifo = isset($config['isFifo']) ? (bool)$config['isFifo'] : false;
    }


    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'secret' => $this->secret,
            'region' => $this->region,
            'queue' => $this->queue,
            'isFifo' => $this->fifo,
        ];
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * @return string
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * @return bool
     */
    public function isFifo(): bool
    {
        return $this->fifo;
    }

}
