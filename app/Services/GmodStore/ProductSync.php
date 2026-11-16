<?php

namespace App\Services\GmodStore;

use App\Models\StoreProduct;
use App\Support\Settings;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/** Refreshes StoreProduct rows from the GmodStore API. */
class ProductSync
{
    public function __construct(private readonly GmodStoreClient $client, private readonly Settings $settings) {}

    /** Add (or refresh) one product by its GmodStore ID. Throws when GmodStore can't return it. */
    public function add(string $gmodstoreId): StoreProduct
    {
        $product = StoreProduct::firstOrNew(['gmodstore_id' => Str::lower($gmodstoreId)]);
        $this->fill($product, $this->client->product($gmodstoreId));
        $product->save();
        $this->refreshStats($product);

        return $product;
    }

    /** Pull every product from the configured team. Returns how many were added. */
    public function importTeam(): int
    {
        $teamId = trim((string) $this->settings->get('gmodstore_team_id'));
        if ($teamId === '') {
            return 0;
        }

        $added = 0;
        foreach ($this->client->teamProducts($teamId) as $data) {
            $product = StoreProduct::firstOrNew(['gmodstore_id' => Str::lower($data['id'])]);
            $added += $product->exists ? 0 : 1;
            $this->fill($product, $data);
            $product->save();
        }

        return $added;
    }

    /** Refresh every product. Returns [ok, failed]. */
    public function syncAll(): array
    {
        $ok = $failed = 0;

        foreach (StoreProduct::all() as $product) {
            $this->sync($product) ? $ok++ : $failed++;
        }

        return [$ok, $failed];
    }

    public function sync(StoreProduct $product): bool
    {
        try {
            $this->fill($product, $this->client->product($product->gmodstore_id));
            $product->save();
            $this->refreshStats($product);

            return true;
        } catch (Throwable $e) {
            Log::warning('GmodStore sync failed', ['product' => $product->gmodstore_id, 'error' => $e->getMessage()]);
            $product->forceFill(['sync_error' => Str::limit($this->describe($e), 290)])->save();

            return false;
        }
    }

    private function refreshStats(StoreProduct $product): void
    {
        if ($reviews = $this->client->reviewSummary($product->gmodstore_id)) {
            [$product->rating, $product->reviews_count] = $reviews;
        }

        if ($this->settings->bool('gmodstore_show_sales') && ($sales = $this->client->salesCount($product->gmodstore_id))) {
            [$product->sales_count, $product->sales_capped] = $sales;
        }

        $product->save();
    }

    private function fill(StoreProduct $product, array $data): void
    {
        $purchase = $data['price']['purchase']['amount'] ?? null;
        $original = $data['price']['original']['amount'] ?? null;

        $product->fill([
            'slug' => $data['slug'] ?? null,
            'name' => $data['name'] ?? 'GmodStore product',
            'tagline' => Str::limit(trim((string) ($data['shortDescription'] ?? '')), 295) ?: null,
            'image_url' => $data['images']['listing'] ?? $data['images']['page'] ?? null,
            'background_url' => $data['images']['background'] ?? $data['images']['bigspot'] ?? null,
            'price' => $purchase !== null ? (int) $purchase : null,
            'original_price' => $original !== null ? (int) $original : null,
            'currency' => $data['price']['purchase']['currency'] ?? 'USD',
            'views' => (int) ($data['views'] ?? 0),
            'is_listed' => (bool) ($data['active'] ?? true),
            'synced_at' => now(),
            'sync_error' => null,
        ]);
    }

    private function describe(Throwable $e): string
    {
        $status = $e instanceof RequestException ? $e->response->status() : null;

        return match ($status) {
            401 => 'GmodStore rejected the API key.',
            403 => 'The API key is missing the products:read scope.',
            404 => 'GmodStore has no product with that ID.',
            429 => 'Rate limited by GmodStore — will retry next hour.',
            default => $status ? "GmodStore returned HTTP {$status}." : 'Couldn\'t reach GmodStore.',
        };
    }
}
