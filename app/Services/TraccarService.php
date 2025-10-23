<?php

declare(strict_types=1);

namespace App\Services;

use DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

/**
 * TraccarService
 *
 * Service for interacting with Traccar GPS tracking server API.
 * Handles fetching device positions, device information, and route reports.
 *
 * @package App\Services
 */
class TraccarService
{
    /**
     * Base URL of the Traccar server
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * Authentication method ('basic' or 'token')
     *
     * @var string
     */
    protected string $authMethod;

    /**
     * Basic auth username
     *
     * @var string
     */
    protected string $username;

    /**
     * Basic auth password
     *
     * @var string
     */
    protected string $password;

    /**
     * API token for authentication
     *
     * @var string
     */
    protected string $token;

    /**
     * Request timeout in seconds
     *
     * @var int
     */
    protected int $timeout;

    /**
     * Enable debug logging
     *
     * @var bool
     */
    protected bool $debugLogging;

    /**
     * Create a new TraccarService instance.
     */
    public function __construct()
    {
        $this->baseUrl = rtrim(config('traccar.url'), '/');
        $this->authMethod = config('traccar.auth_method', 'basic');
        $this->username = config('traccar.username');
        $this->password = config('traccar.password');
        $this->token = config('traccar.token');
        $this->timeout = config('traccar.sync.timeout', 30);
        $this->debugLogging = config('traccar.features.debug_logging', false);
    }

    /**
     * Fetch device positions for a specific time range.
     *
     * Queries the Traccar /api/positions endpoint with optional time filters.
     * Returns array of position objects with deviceId, latitude, longitude, etc.
     *
     * @param  \DateTime  $from  Start time for position query
     * @param  \DateTime  $to    End time for position query
     * @param  int|null   $deviceId  Optional: filter by specific device ID
     * @return array<int, array{
     *     id: int,
     *     deviceId: int,
     *     protocol: string,
     *     serverTime: string,
     *     deviceTime: string,
     *     fixTime: string,
     *     latitude: float,
     *     longitude: float,
     *     altitude: float,
     *     speed: float,
     *     course: float,
     *     address: string|null,
     *     accuracy: float,
     *     network: array|null,
     *     attributes: array
     * }>
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function fetchDevicePositionsForTimeRange(DateTime $from, DateTime $to, ?int $deviceId = null): array
    {
        $endpoint = $this->baseUrl . config('traccar.endpoints.positions');

        // Build query parameters
        $params = [
            'from' => $from->format('c'), // ISO 8601 format
            'to' => $to->format('c'),
        ];

        if ($deviceId !== null) {
            $params['deviceId'] = $deviceId;
        }

        if ($this->debugLogging) {
            Log::info('Traccar API Request', [
                'endpoint' => $endpoint,
                'params' => $params,
            ]);
        }

        // Make authenticated request
        $response = $this->makeRequest('GET', $endpoint, $params);

        if (!$response->successful()) {
            Log::error('Traccar API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            $response->throw();
        }

        $positions = $response->json() ?? [];

        if ($this->debugLogging) {
            Log::info('Traccar API Response', [
                'positions_count' => count($positions),
            ]);
        }

        return $positions;
    }

    /**
     * Get the last known position for a specific device.
     *
     * @param  int  $deviceId  The Traccar device ID
     * @return array|null  Position data or null if not found
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getDeviceLastPosition(int $deviceId): ?array
    {
        $endpoint = $this->baseUrl . config('traccar.endpoints.positions');

        $params = [
            'deviceId' => $deviceId,
        ];

        if ($this->debugLogging) {
            Log::info('Traccar API: Get last position', [
                'device_id' => $deviceId,
            ]);
        }

        $response = $this->makeRequest('GET', $endpoint, $params);

        if (!$response->successful()) {
            Log::error('Traccar API: Failed to get last position', [
                'device_id' => $deviceId,
                'status' => $response->status(),
            ]);
            
            return null;
        }

        $positions = $response->json() ?? [];

        // Return the most recent position (Traccar returns array)
        return !empty($positions) ? $positions[0] : null;
    }

    /**
     * Get all devices from Traccar server.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     uniqueId: string,
     *     status: string,
     *     lastUpdate: string|null,
     *     positionId: int|null,
     *     groupId: int|null,
     *     phone: string|null,
     *     model: string|null,
     *     contact: string|null,
     *     category: string|null,
     *     disabled: bool,
     *     attributes: array
     * }>
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getAllDevices(): array
    {
        $endpoint = $this->baseUrl . config('traccar.endpoints.devices');

        if ($this->debugLogging) {
            Log::info('Traccar API: Get all devices');
        }

        $response = $this->makeRequest('GET', $endpoint);

        if (!$response->successful()) {
            Log::error('Traccar API: Failed to get devices', [
                'status' => $response->status(),
            ]);
            
            $response->throw();
        }

        return $response->json() ?? [];
    }

    /**
     * Fetch route report for a specific device and time range.
     *
     * More efficient than fetching individual positions for historical data.
     *
     * @param  int       $deviceId  The Traccar device ID
     * @param  \DateTime $from      Start time
     * @param  \DateTime $to        End time
     * @return array<int, array>    Array of position points in the route
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function fetchDeviceRoute(int $deviceId, DateTime $from, DateTime $to): array
    {
        $endpoint = $this->baseUrl . config('traccar.endpoints.reports_route');

        $params = [
            'deviceId' => $deviceId,
            'from' => $from->format('c'),
            'to' => $to->format('c'),
        ];

        if ($this->debugLogging) {
            Log::info('Traccar API: Fetch route report', [
                'device_id' => $deviceId,
                'from' => $params['from'],
                'to' => $params['to'],
            ]);
        }

        $response = $this->makeRequest('GET', $endpoint, $params);

        if (!$response->successful()) {
            Log::error('Traccar API: Failed to fetch route', [
                'device_id' => $deviceId,
                'status' => $response->status(),
            ]);
            
            $response->throw();
        }

        return $response->json() ?? [];
    }

    /**
     * Make an authenticated HTTP request to Traccar API.
     *
     * @param  string $method  HTTP method (GET, POST, etc.)
     * @param  string $url     Full endpoint URL
     * @param  array  $params  Query parameters or request body
     * @return \Illuminate\Http\Client\Response
     */
    protected function makeRequest(string $method, string $url, array $params = []): Response
    {
        $http = Http::timeout($this->timeout)
            ->accept('application/json');

        // Apply authentication
        if ($this->authMethod === 'token' && !empty($this->token)) {
            // Token-based authentication
            $http = $http->withToken($this->token);
        } else {
            // Basic authentication (default)
            $http = $http->withBasicAuth($this->username, $this->password);
        }

        // Make request based on method
        if (strtoupper($method) === 'GET') {
            return $http->get($url, $params);
        } elseif (strtoupper($method) === 'POST') {
            return $http->post($url, $params);
        } else {
            return $http->send($method, $url, ['query' => $params]);
        }
    }

    /**
     * Test connection to Traccar server.
     *
     * @return bool True if connection is successful
     */
    public function testConnection(): bool
    {
        try {
            $devices = $this->getAllDevices();
            
            Log::info('Traccar connection test successful', [
                'devices_count' => count($devices),
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Traccar connection test failed', [
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }
}
