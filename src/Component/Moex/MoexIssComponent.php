<?php

declare(strict_types=1);

namespace Moex\Skill\Component\Moex;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Moex\Skill\Exception\InfrastructureException;
use Moex\Skill\Exception\InfrastructureExceptionInterface;
use Psr\Log\LoggerInterface;

class MoexIssComponent implements MoexIssComponentInterface
{
    private ClientInterface $httpClient;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $baseUrl,
    ) {
        $this->httpClient = new \GuzzleHttp\Client([
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getContent(string $url, array $urlData, array $query): ?string
    {
        $url = sprintf($url, ...$urlData);
        $fullUrl = $this->baseUrl . '/' . $url;
        $query['iss.json'] = 'extended';
        $query['iss.meta'] = 'off';

        try {
            $response = $this->httpClient->request('GET', $fullUrl . '.json', [
                'query' => $query,
            ]);
            $content = $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            $this->logger->error("Failed to get data from MOEX ISS: " . $e->getMessage(), [
                'url' => $fullUrl,
                'code' => $e->getCode(),
            ]);
            throw new InfrastructureException(
                message: sprintf(
                    'Failed to get data from MOEX ISS (url: %s): %s',
                    $fullUrl,
                    $e->getMessage()
                ),
                previous: $e
            );
        }

        if (empty($content)) {
            $this->logger->error("Empty response from MOEX ISS", [
                'url' => $fullUrl,
            ]);
            throw new InfrastructureException(sprintf(
                'Empty response from MOEX ISS (url: %s)',
                $fullUrl
            ));
        }

        $this->logger->debug("Data fetched from MOEX ISS", [
            'url' => $fullUrl,
            'length' => strlen($content),
        ]);

        return $content;
    }
}
