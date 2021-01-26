<?php

namespace BrighteCapital\QueueClient\Notifications\Channels;

use DateTime;
use GuzzleHttp\Client;

class SlackNotificationChannel implements NotificationChannelInterface
{
    public const FIELDS = ['time', 'messageId', 'retryCount'];
    public const EXCLUDE = ['messageHandle'];
    public const LIMIT_SIZE = ['body' => 200, 'lastError' => 1000];

    /** @var \GuzzleHttp\ClientInterface */
    private $client;
    /** @var string */
    private $url;

    /**
     * SlackNotificationChannel constructor.
     * @param string $url slack webHook url
     * @param \GuzzleHttp\Client $client client
     * @param int $maxBodyChars message body character limit
     */
    public function __construct(
        string $url,
        Client $client = null
    ) {
        $this->url = $url;
        $this->client = $client ?? new Client();
    }

    /**
     * @param array $data data
     * @return void
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(array $data): void
    {
        $message = $this->createMessage($data);
        $response = $this->postMessage($message);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception(
                sprintf(
                    "Failed to send Slack message. status code = %s body = %s",
                    $response->getStatusCode(),
                    (string)$response->getBody()
                )
            );
        }
    }

    /**
     * @param array $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function postMessage(array $message)
    {
        return $this->client->post($this->url, $message);
    }

    /**
     * @param array $data
     * @return array
     */
    public function createMessage(array $data): array
    {
        $blocks = [];
        $fields = [];
        $data['time'] = (new DateTime())->format(DateTime::ISO8601);

        foreach (self::EXCLUDE as $field) {
            unset($data[$field]);
        }

        foreach (self::LIMIT_SIZE as $field => $charLimit) {
            if (!isset($data[$field])) {
                continue;
            }

            $length = strlen($data[$field]) - $charLimit;
            $data[$field] = substr($data[$field], 0, $charLimit);
            if ($length > 0) {
                $data[$field] .= "\n...+" . $length . ' characters';
            }
        }

        if (isset($data['retryCount'])) {
            $data['retryCount'] = str_repeat(':o:', (int)$data['retryCount']);
        }

        $title = $data['title'] ?? 'Queue Client Notification';
        unset($data['title']);
        $blocks[] = [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' =>  '*' . $title . '*',
            ],
        ];

        foreach (self::FIELDS as $field) {
            if (!isset($data[$field])) {
                continue;
            }

            $fields[] = [
                'type' => 'mrkdwn',
                'text' => '*' . $field . "*:\n" . $data[$field],
            ];
            unset($data[$field]);
        }

        if ($fields) {
            $blocks[] = [
                'type' => 'section',
                'fields' => $fields
            ];
        }

        foreach ($data as $key => $value) {
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' =>  '*' . $key . "*:\n```" . $value . '```',
                ],
            ];
        }

        $blocks[] = ['type' => 'divider'];

        return [
            'json' => [
                'blocks' => $blocks,
            ]
        ];
    }
}
