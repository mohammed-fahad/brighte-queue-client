<?php


namespace BrighteCapital\QueueClient\queue\factories;

use Aws\Sdk;
use Aws\Sqs\SqsClient as AwsSqsClient;
use BrighteCapital\QueueClient\queue\sqs\SqsContext;
use Enqueue\Dsn\Dsn;
use Enqueue\Sqs\SqsClient;
use Interop\Queue\Context;

class SqsConnectionFactory extends \Enqueue\Sqs\SqsConnectionFactory
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var SqsClient
     */
    private $client;

    /*exact copy of parent*/
    public function __construct($config = 'sqs:')
    {
        if ($config instanceof AwsSqsClient) {
            $this->client = new SqsClient($config);
            $this->config = ['lazy' => false] + $this->defaultConfig();

            return;
        }

        if (empty($config)) {
            $config = [];
        } elseif (is_string($config)) {
            $config = $this->parseDsn($config);
        } elseif (is_array($config)) {
            if (array_key_exists('dsn', $config)) {
                $config = array_replace_recursive($config, $this->parseDsn($config['dsn']));

                unset($config['dsn']);
            }
        } else {
            throw new \LogicException(sprintf('The config must be either an array of options, a DSN string, null or instance of %s', AwsSqsClient::class));
        }

        $this->config = array_replace($this->defaultConfig(), $config);
    }

    /**
     * @return \Enqueue\Sqs\SqsContext
     */
    public function createContext(): Context
    {
        return new SqsContext($this->establishConnection(), $this->config);
    }

    /*exact copy of parent because its private method*/
    private function establishConnection(): SqsClient
    {
        if ($this->client) {
            return $this->client;
        }

        $config = [
            'version' => $this->config['version'],
            'retries' => $this->config['retries'],
            'region' => $this->config['region'],
        ];

        if (isset($this->config['endpoint'])) {
            $config['endpoint'] = $this->config['endpoint'];
        }

        if ($this->config['key'] && $this->config['secret']) {
            $config['credentials'] = [
                'key' => $this->config['key'],
                'secret' => $this->config['secret'],
            ];

            if ($this->config['token']) {
                $config['credentials']['token'] = $this->config['token'];
            }
        }

        $establishConnection = function () use ($config) {
            return (new Sdk(['Sqs' => $config]))->createMultiRegionSqs();
        };

        $this->client = $this->config['lazy'] ?
            new SqsClient($establishConnection) :
            new SqsClient($establishConnection());

        return $this->client;
    }

    /*exact copy of parent because its private method*/
    private function parseDsn(string $dsn): array
    {
        $dsn = Dsn::parseFirst($dsn);

        if ('sqs' !== $dsn->getSchemeProtocol()) {
            throw new \LogicException(
                sprintf(
                    'The given scheme protocol "%s" is not supported. It must be "sqs"',
                    $dsn->getSchemeProtocol()
                )
            );
        }

        return array_filter(
            array_replace(
                $dsn->getQuery(), [
                    'key' => $dsn->getString('key'),
                    'secret' => $dsn->getString('secret'),
                    'token' => $dsn->getString('token'),
                    'region' => $dsn->getString('region'),
                    'retries' => $dsn->getDecimal('retries'),
                    'version' => $dsn->getString('version'),
                    'lazy' => $dsn->getBool('lazy'),
                    'endpoint' => $dsn->getString('endpoint'),
                    'queue_owner_aws_account_id' => $dsn->getString('queue_owner_aws_account_id'),
                ]
            ), function ($value) {
            return null !== $value;
        }
        );
    }

    /*exact copy of parent because its private method*/
    private function defaultConfig(): array
    {
        return [
            'key' => null,
            'secret' => null,
            'token' => null,
            'region' => null,
            'retries' => 3,
            'version' => '2012-11-05',
            'lazy' => true,
            'endpoint' => null,
            'queue_owner_aws_account_id' => null,
        ];
    }
}
