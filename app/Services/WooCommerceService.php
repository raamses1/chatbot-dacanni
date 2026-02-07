<?php

namespace App\Services;

use GuzzleHttp\Client;

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
    }
}
