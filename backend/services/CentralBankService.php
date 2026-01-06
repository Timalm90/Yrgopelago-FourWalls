<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CentralBankService
{
    private Client $client;
    private string $hotelUser;
    private string $apiKey;

    public function __construct()
    {
        $this->hotelUser = $_ENV['HOTEL_USER'] ?? '';
        $this->apiKey    = $_ENV['API_KEY'] ?? '';

        if (!$this->hotelUser || !$this->apiKey) {
            throw new RuntimeException("CentralBank credentials not set in .env");
        }

        $this->client = new Client([
            'base_uri' => 'https://www.yrgopelag.se/centralbank/',
            'timeout'  => 5.0,
        ]);
    }

    /**
     * Validate transfer code
     * Returns ['success' => bool, 'error' => null|string]
     * error can be: 'expired_code', 'invalid_code', 'other'
     */
    public function validateTransfer(string $transferCode, float $totalCost): array
    {
        try {
            $response = $this->client->post('transferCode', [
                'json' => [
                    'transferCode' => $transferCode,
                    'totalCost'    => $totalCost,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            if (!isset($data['status'])) {
                return ['success' => false, 'error' => 'other'];
            }

            if ($data['status'] === 'success') {
                return ['success' => true, 'error' => null];
            }

            if (isset($data['error'])) {
                if ($data['error'] === 'expired') {
                    return ['success' => false, 'error' => 'expired_code'];
                } elseif ($data['error'] === 'invalid') {
                    return ['success' => false, 'error' => 'invalid_code'];
                }
            }

            return ['success' => false, 'error' => 'other'];
        } catch (RequestException $e) {
            return ['success' => false, 'error' => 'other'];
        }
    }

    /**
     * Consume transfer code and credit hotel
     */
    public function deposit(string $transferCode): bool
    {
        try {
            $response = $this->client->post('deposit', [
                'json' => [
                    'user'         => $this->hotelUser,
                    'api_key'      => $this->apiKey,
                    'transferCode' => $transferCode,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            return isset($data['status']) && $data['status'] === 'success';
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * Send receipt to CentralBank
     */
    public function sendReceipt(
        string $guestName,
        string $arrival,
        string $departure,
        array $featuresUsed,
        int $stars = 5
    ): bool {
        try {
            $response = $this->client->post('receipt', [
                'json' => [
                    'user'           => $this->hotelUser,
                    'api_key'        => $this->apiKey,
                    'guest_name'     => $guestName,
                    'arrival_date'   => $arrival,
                    'departure_date' => $departure,
                    'features_used'  => $featuresUsed,
                    'star_rating'    => $stars,
                ],
            ]);

            $data = json_decode((string)$response->getBody(), true);
            return isset($data['status']) && $data['status'] === 'success';
        } catch (RequestException $e) {
            return false;
        }
    }
}
