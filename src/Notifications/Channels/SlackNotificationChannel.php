<?php

namespace BrighteCapital\QueueClient\Notifications\Channels;

use DateTime;
use GuzzleHttp\Client;

class SlackNotificationChannel implements NotificationChannelInterface
{
    public const DEFAULT_MAX_BODY_CHARS_TO_SEND = 200;
    /** @var \GuzzleHttp\ClientInterface */
    private $client;
    /** @var string */
    private $url;
    /** @var int */
    private $maxBodyCharsToSend;

    /**
     * SlackNotificationChannel constructor.
     * @param string $url slack webHook url
     * @param \GuzzleHttp\Client $client client
     * @param int $maxBodyChars message body character limit
     */
    public function __construct(
        string $url,
        Client $client = null,
        int $maxBodyChars = self::DEFAULT_MAX_BODY_CHARS_TO_SEND
    ) {
        $this->url = $url;
        $this->client = $client ?? new Client();
        $this->maxBodyCharsToSend = $maxBodyChars;
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
        if (isset($data['body'])) {
            $length = strlen($data['body']) - $this->getMaxBodyCharsToSend();
            $data['body'] = substr($data['body'], 0, $this->getMaxBodyCharsToSend());
            if ($length > 0) {
                $data['body'] .= "\n...+" . $length . ' characters';
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

        $fieldNames = ['time', 'messageId', 'retryCount', 'class'];
        foreach ($fieldNames as $field) {
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

    /**
     * @return int
     */
    public function getMaxBodyCharsToSend(): int
    {
        return $this->maxBodyCharsToSend;
    }

    /**
     * @param int $maxBodyCharsToSend
     */
    public function setMaxBodyCharsToSend(int $maxBodyCharsToSend): void
    {
        $this->maxBodyCharsToSend = $maxBodyCharsToSend;
    }
}
