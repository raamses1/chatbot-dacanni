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
        // MODO PRUEBA — quitar cuando dacanni.com se restaure
if (config('app.env') === 'local') {
    return $this->mockProducts($search);
}
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
        // MODO PRUEBA — quitar cuando dacanni.com se restaure
if (config('app.env') === 'local') {
    return $this->mockVariations($productId);
}
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
    private function mockProducts(?string $search): array
{
    $products = [
        ['id' => 1, 'name' => 'Vestido Discordia', 'price' => '950', 'type' => 'simple', 'stock_status' => 'instock', 'stock_quantity' => 3],
        ['id' => 2, 'name' => 'Vestido Luna',      'price' => '850', 'type' => 'simple', 'stock_status' => 'instock', 'stock_quantity' => 1],
        ['id' => 3, 'name' => 'Blusa Alebrije',    'price' => '620', 'type' => 'simple', 'stock_status' => 'instock', 'stock_quantity' => 5],
        ['id' => 4, 'name' => 'Blusa Primavera',   'price' => '580', 'type' => 'simple', 'stock_status' => 'outofstock', 'stock_quantity' => 0],
        ['id' => 5, 'name' => 'Top Luna',          'price' => '480', 'type' => 'simple', 'stock_status' => 'instock', 'stock_quantity' => 8],
        ['id' => 6, 'name' => 'Corset Magnolia',   'price' => '1100','type' => 'simple', 'stock_status' => 'instock', 'stock_quantity' => 2],
        ['id' => 7, 'name' => 'Guayabera Oaxaca',  'price' => '750', 'type' => 'simple', 'stock_status' => 'instock', 'stock_quantity' => 4],
        ['id' => 8, 'name' => 'Pantalon Copal',    'price' => '890', 'type' => 'simple', 'stock_status' => 'outofstock', 'stock_quantity' => 0],
    ];

    if (!$search) {
        return $products;
    }

    // Filtrar por búsqueda
    return array_values(array_filter($products, function ($p) use ($search) {
        return str_contains(strtolower($p['name']), strtolower($search));
    }));
}
private function mockVariations(int $productId): array
{
    return [
        ['id' => 101, 'stock_status' => 'instock',    'stock_quantity' => 2],
        ['id' => 102, 'stock_status' => 'instock',    'stock_quantity' => 1],
        ['id' => 103, 'stock_status' => 'outofstock', 'stock_quantity' => 0],
    ];
}

}