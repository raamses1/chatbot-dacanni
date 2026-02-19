<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class WooCommerceService
{
    protected Client $client;
    protected string $key;
    protected string $secret;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => env('WOO_URL'),
            'timeout'  => 10.0,
        ]);

        $this->key = env('WOO_KEY');
        $this->secret = env('WOO_SECRET');
    }

    public function getProducts(string $search = null)
    {
        try {

            $params = [
                'consumer_key' => $this->key,
                'consumer_secret' => $this->secret,
                'per_page' => 5,
            ];

            if ($search) {
                $params['search'] = $search;
            }

            $response = $this->client->get('/wp-json/wc/v3/products', [
                'query' => $params
            ]);

            return json_decode($response->getBody(), true);

        } catch (RequestException $e) {

            \Log::error('WooCommerce API error: ' . $e->getMessage());

            if ($e->hasResponse()) {
                \Log::error($e->getResponse()->getBody());
            }

            return null;
        }
    }

    public function getVariations(int $productId)
    {
        try {

            $response = $this->client->get("/wp-json/wc/v3/products/{$productId}/variations", [
                'query' => [
                    'consumer_key' => $this->key,
                    'consumer_secret' => $this->secret,
                ]
            ]);

            return json_decode($response->getBody(), true);

        } catch (RequestException $e) {

            \Log::error('WooCommerce Variations error: ' . $e->getMessage());

            return null;
        }
    }
}
