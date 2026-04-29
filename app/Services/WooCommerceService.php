<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class WooCommerceService
{
    protected Client $client;
    protected string $key;
    protected string $secret;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.woocommerce.url'),
            'timeout'  => 10.0,
        ]);

        $this->key    = config('services.woocommerce.key');
        $this->secret = config('services.woocommerce.secret');
    }

    public function getProducts(string $search = null): array
    {
        try {
            $params = [
                'consumer_key'    => $this->key,
                'consumer_secret' => $this->secret,
                'per_page'        => 5,
            ];

            if ($search) {
                $params['search'] = $search;
            }

            $response = $this->client->get('/wp-json/wc/v3/products', [
                'query' => $params,
            ]);

            return json_decode($response->getBody(), true) ?? [];

        } catch (ConnectException $e) {
            Log::error('[WooCommerce] Sin conexión (getProducts): ' . $e->getMessage());
            return ['woo_error' => 'connection'];

        } catch (RequestException $e) {
            Log::error('[WooCommerce] Error HTTP (getProducts): ' . $e->getMessage());

            if ($e->hasResponse()) {
                Log::error('[WooCommerce] Body: ' . $e->getResponse()->getBody());
            }

            return ['woo_error' => 'http'];
        }
    }

    public function getVariations(int $productId): array
    {
        
        try {
            $response = $this->client->get("/wp-json/wc/v3/products/{$productId}/variations", [
                'query' => [
                    'consumer_key'    => $this->key,
                    'consumer_secret' => $this->secret,
                ],
            ]);

            return json_decode($response->getBody(), true) ?? [];

        } catch (ConnectException $e) {
            Log::error('[WooCommerce] Sin conexión (getVariations): ' . $e->getMessage());
            return ['woo_error' => 'connection'];

        } catch (RequestException $e) {
            Log::error('[WooCommerce] Error HTTP (getVariations): ' . $e->getMessage());
            return ['woo_error' => 'http'];
        }
    }

    //Verifica si un resultado de WooCommerce es un error.
     
    public function isError(array $result): bool
    {
        return isset($result['woo_error']);
    }

    /**
     * Devuelve el tipo de error: 'connection' | 'http' | null
     */
    public function getErrorType(array $result): ?string
    {
        return $result['woo_error'] ?? null;
    }

    public function getTags(): array
{
    try {
        $response = $this->client->get('/wp-json/wc/v3/products/tags', [
            'query' => [
                'consumer_key'    => $this->key,
                'consumer_secret' => $this->secret,
                'per_page'        => 100,
            ],
        ]);

        return json_decode($response->getBody(), true) ?? [];

    } catch (ConnectException $e) {
        Log::error('[WooCommerce] Sin conexión (getTags): ' . $e->getMessage());
        return [];
    } catch (RequestException $e) {
        Log::error('[WooCommerce] Error HTTP (getTags): ' . $e->getMessage());
        return [];
    }
}
}