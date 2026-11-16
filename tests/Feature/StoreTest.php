<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\StoreProduct;
use App\Services\GmodStore\ProductSync;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private const ID = 'b83b1440-ace4-44a0-b3f1-c85158d770e7';

    private function product(array $overrides = []): array
    {
        return array_replace_recursive([
            'id' => self::ID, 'teamId' => 'c0ffee00-0000-4000-8000-000000000001', 'slug' => 'vanta-banking', 'name' => 'Vanta Banking',
            'shortDescription' => 'Banking for DarkRP.', 'views' => 1234, 'active' => true,
            'price' => ['raw' => 1999, 'original' => ['amount' => '2499', 'currency' => 'USD'], 'purchase' => ['amount' => '1999', 'currency' => 'USD']],
            'images' => ['background' => 'https://img/bg.png', 'listing' => 'https://img/listing.png'],
        ], $overrides);
    }

    private function fakeApi(array $productOverrides = []): void
    {
        Http::fake([
            'api.pivity.com/v3/products/'.self::ID.'/reviews*' => Http::sequence()
                ->push(['data' => [['rating' => 5], ['rating' => 4]], 'cursors' => ['prev' => null, 'next' => 'page2'], 'connections' => [], 'meta' => ['perPage' => 100]])
                ->push(['data' => [['rating' => 3]], 'cursors' => ['prev' => 'x', 'next' => null], 'connections' => [], 'meta' => ['perPage' => 100]]),
            'api.pivity.com/v3/products/'.self::ID.'/purchases*' => Http::response(['data' => [['revoked' => false], ['revoked' => true], ['revoked' => false]], 'cursors' => ['prev' => null, 'next' => null], 'connections' => [], 'meta' => ['perPage' => 100]]),
            'api.pivity.com/v3/products/'.self::ID.'*' => Http::response(['data' => $this->product($productOverrides)]),
            'api.pivity.com/*' => Http::response(['message' => 'Not found'], 404),
        ]);
    }

    private function withKey(): void
    {
        app(Settings::class)->set(['gmodstore_api_key' => Crypt::encryptString('pat_test_key')]);
    }

    public function test_admin_adds_a_product_from_its_url_with_live_stats(): void
    {
        $this->withKey();
        $this->fakeApi();

        $this->actingAs($this->admin())
            ->post('/admin/store', ['product' => 'https://www.gmodstore.com/market/view/'.self::ID])
            ->assertSessionHas('success');

        $p = StoreProduct::sole();
        $this->assertSame('Vanta Banking', $p->name);
        $this->assertSame(1999, $p->price);
        $this->assertSame(2499, $p->original_price);
        $this->assertSame(3, $p->reviews_count);
        $this->assertEqualsWithDelta(4.0, $p->rating, 0.01);
        $this->assertSame(2, $p->sales_count, 'revoked purchases are not counted');
        $this->assertSame('https://www.gmodstore.com/market/view/vanta-banking', $p->storeUrl());

        Http::assertSent(fn (Request $r) => $r->hasHeader('Authorization', 'Bearer pat_test_key') && $r->hasHeader('X-Tenant', 'gmodstore.com'));
    }

    public function test_unknown_product_shows_a_clear_error(): void
    {
        $this->withKey();
        Http::fake(['api.pivity.com/*' => Http::response([], 404)]);

        $this->actingAs($this->admin())
            ->post('/admin/store', ['product' => self::ID])
            ->assertSessionHasErrors(['product' => 'GmodStore has no product with that ID.']);
        $this->assertSame(0, StoreProduct::count());
    }

    public function test_input_without_an_id_is_rejected_before_calling_the_api(): void
    {
        $this->withKey();
        Http::fake();

        $this->actingAs($this->admin())->post('/admin/store', ['product' => 'vanta banking'])->assertSessionHasErrors('product');
        Http::assertNothingSent();
    }

    public function test_sync_records_errors_without_losing_the_product(): void
    {
        $this->withKey();
        $p = StoreProduct::create(['gmodstore_id' => self::ID, 'name' => 'Old name', 'price' => 500]);
        Http::fake(['api.pivity.com/*' => Http::response([], 401)]);

        $this->assertFalse(app(ProductSync::class)->sync($p));
        $this->assertSame('GmodStore rejected the API key.', $p->fresh()->sync_error);
        $this->assertSame('Old name', $p->fresh()->name);
    }

    public function test_store_page_lists_only_visible_products(): void
    {
        StoreProduct::create(['gmodstore_id' => self::ID, 'name' => 'Shown', 'price' => 1000, 'rating' => 4.5, 'reviews_count' => 10, 'sales_count' => 50]);
        StoreProduct::create(['gmodstore_id' => '00000000-0000-4000-8000-000000000002', 'name' => 'Hidden', 'is_visible' => false]);
        StoreProduct::create(['gmodstore_id' => '00000000-0000-4000-8000-000000000003', 'name' => 'Delisted', 'is_listed' => false]);

        $this->get('/store')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Store/Index')
            ->has('products', 1)
            ->where('products.0.name', 'Shown')
            ->where('products.0.sales', 50)
            ->where('totals.reviews', 10));

        $this->get('/')->assertInertia(fn ($page) => $page->has('store', 1)->where('nav.store', true));
    }

    public function test_store_page_is_hidden_until_there_are_products(): void
    {
        $this->get('/store')->assertNotFound();
        $this->get('/')->assertInertia(fn ($page) => $page->where('nav.store', false)->has('store', 0));
    }

    public function test_sales_can_be_hidden(): void
    {
        $this->settings(['gmodstore_show_sales' => false]);
        StoreProduct::create(['gmodstore_id' => self::ID, 'name' => 'Shown', 'sales_count' => 50]);

        $this->get('/store')->assertInertia(fn ($page) => $page->where('products.0.sales', null)->where('totals.sales', null));
    }

    public function test_api_key_is_encrypted_write_only(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/settings/gmodstore', ['gmodstore_api_key' => 'pat_secret', 'gmodstore_show_sales' => true])->assertSessionHas('success');
        $stored = Setting::firstWhere('key', 'gmodstore_api_key')->value;
        $this->assertNotSame('pat_secret', $stored);
        $this->assertSame('pat_secret', app(Settings::class)->secret('gmodstore_api_key'));

        // Never sent back to the browser…
        $this->get('/admin/settings/gmodstore')->assertInertia(fn ($page) => $page
            ->where('fields.0.key', 'gmodstore_api_key')->where('fields.0.value', null)->where('fields.0.is_set', true));
        $this->assertStringNotContainsString('pat_secret', $this->get('/admin/settings/gmodstore')->getContent());

        // …a blank save keeps it, and it can be cleared explicitly.
        $this->post('/admin/settings/gmodstore', ['gmodstore_api_key' => '']);
        $this->assertSame('pat_secret', app(Settings::class)->secret('gmodstore_api_key'));
        $this->post('/admin/settings/gmodstore', ['clear_gmodstore_api_key' => true]);
        $this->assertNull(app(Settings::class)->secret('gmodstore_api_key'));
    }

    public function test_sync_command_imports_the_team_catalogue(): void
    {
        $this->withKey();
        $this->settings(['gmodstore_team_id' => 'c0ffee00-0000-4000-8000-000000000001']);
        Http::fake([
            'api.pivity.com/v3/teams/*' => Http::response(['data' => [$this->product()], 'cursors' => ['prev' => null, 'next' => null], 'connections' => [], 'meta' => ['perPage' => 100]]),
            'api.pivity.com/v3/products/'.self::ID.'/*' => Http::response(['data' => [], 'cursors' => ['prev' => null, 'next' => null], 'connections' => [], 'meta' => ['perPage' => 100]]),
            'api.pivity.com/v3/products/*' => Http::response(['data' => $this->product()]),
        ]);

        $this->artisan('gmodstore:sync')->assertSuccessful();
        $this->assertSame('Vanta Banking', StoreProduct::sole()->name);
    }
}
