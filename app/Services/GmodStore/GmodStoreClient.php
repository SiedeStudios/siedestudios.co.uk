<?php

namespace App\Services\GmodStore;

use App\Support\Settings;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Minimal client for the Pivity (GmodStore) API v3 — https://docs.pivity.com.
 * Needs a personal access token with products:read; product-reviews:read and
 * product-purchases:read unlock ratings and sales counts.
 */
class GmodStoreClient
{
    public const BASE_URL = 'https://api.pivity.com/v3';

    public function __construct(private readonly Settings $settings) {}

    public function configured(): bool
    {
        return filled($this->settings->secret('gmodstore_api_key'));
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->withToken((string) $this->settings->secret('gmodstore_api_key'))
            ->withHeaders(['X-Tenant' => 'gmodstore.com'])
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(20);
    }

    /** @throws RequestException */
    public function product(string $id): array
    {
        return $this->http()->get('/products/'.rawurlencode($id), ['richTextFormat' => 'plaintext'])->throw()->json('data');
    }

    /** @return list<array> @throws RequestException */
    public function teamProducts(string $teamId): array
    {
        $products = [];
        $this->paginate('/teams/'.rawurlencode($teamId).'/products', 20, function (array $page) use (&$products) {
            array_push($products, ...$page);
        });

        return $products;
    }

    /**
     * Average rating and count across every review.
     *
     * @return array{0: ?float, 1: int}|null null when the token can't read reviews
     */
    public function reviewSummary(string $productId): ?array
    {
        $sum = 0;
        $count = 0;

        try {
            $this->paginate('/products/'.rawurlencode($productId).'/reviews', 30, function (array $page) use (&$sum, &$count) {
                foreach ($page as $review) {
                    $sum += (int) ($review['rating'] ?? 0);
                    $count++;
                }
            });
        } catch (RequestException $e) {
            if (in_array($e->response->status(), [401, 403], true)) {
                return null;
            }
            throw $e;
        }

        return [$count ? $sum / $count : null, $count];
    }

    /**
     * Non-revoked purchases, counted up to a cap so huge catalogues stay cheap.
     *
     * @return array{0: int, 1: bool}|null [count, hit the cap], null when the token can't read purchases
     */
    public function salesCount(string $productId, int $maxPages = 50): ?array
    {
        $count = 0;

        try {
            $complete = $this->paginate('/products/'.rawurlencode($productId).'/purchases', $maxPages, function (array $page) use (&$count) {
                foreach ($page as $purchase) {
                    $count += empty($purchase['revoked']) ? 1 : 0;
                }
            });
        } catch (RequestException $e) {
            if (in_array($e->response->status(), [401, 403], true)) {
                return null;
            }
            throw $e;
        }

        return [$count, ! $complete];
    }

    /**
     * Walk a cursor-paginated list. Returns false if it stopped at $maxPages.
     *
     * @throws RequestException
     */
    private function paginate(string $path, int $maxPages, callable $each): bool
    {
        $cursor = null;

        for ($i = 0; $i < $maxPages; $i++) {
            $response = $this->http()->get($path, array_filter(['perPage' => 100, 'cursor' => $cursor]))->throw();
            $each($response->json('data') ?? []);
            $cursor = $response->json('cursors.next');

            if (! $cursor) {
                return true;
            }
        }

        return false;
    }
}
