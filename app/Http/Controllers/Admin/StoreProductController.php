<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreProduct;
use App\Services\GmodStore\GmodStoreClient;
use App\Services\GmodStore\ProductSync;
use App\Support\Settings;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class StoreProductController extends Controller
{
    public function index(GmodStoreClient $client, Settings $settings): Response
    {
        return Inertia::render('Admin/Store/Index', [
            'products' => StoreProduct::orderByDesc('is_featured')->orderBy('sort_order')->orderBy('name')->get()
                ->map(fn (StoreProduct $p) => [...$p->toArray(), 'url' => $p->storeUrl()]),
            'configured' => $client->configured(),
            'team' => filled($settings->get('gmodstore_team_id')),
            'show_sales' => $settings->bool('gmodstore_show_sales'),
        ]);
    }

    public function store(Request $request, ProductSync $sync, GmodStoreClient $client): RedirectResponse
    {
        $input = $request->validate(['product' => ['required', 'string', 'max:300']])['product'];

        if (! preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', $input, $m)) {
            return back()->withErrors(['product' => 'Paste the product\'s GmodStore URL or its ID (looks like b83b1440-ace4-…).']);
        }
        if (! $client->configured()) {
            return back()->withErrors(['product' => 'Add your GmodStore API key in Settings → GmodStore first.']);
        }

        try {
            $product = $sync->add($m[0]);
        } catch (RequestException $e) {
            return back()->withErrors(['product' => match ($e->response->status()) {
                401 => 'GmodStore rejected the API key.',
                403 => 'The API key is missing the products:read scope.',
                404 => 'GmodStore has no product with that ID.',
                default => 'GmodStore returned an error ('.$e->response->status().'). Try again shortly.',
            }]);
        } catch (Throwable) {
            return back()->withErrors(['product' => 'Couldn\'t reach GmodStore. Try again shortly.']);
        }

        return back()->with('success', "{$product->name} added.");
    }

    public function update(Request $request, StoreProduct $product): RedirectResponse
    {
        $product->update($request->validate([
            'is_visible' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:9999'],
        ]));

        return back()->with('success', 'Saved.');
    }

    public function sync(ProductSync $sync, GmodStoreClient $client): RedirectResponse
    {
        if (! $client->configured()) {
            return back()->with('error', 'Add your GmodStore API key in Settings → GmodStore first.');
        }

        try {
            $added = $sync->importTeam();
        } catch (Throwable) {
            return back()->with('error', 'Couldn\'t import your team\'s products — check the team ID and the key\'s scopes.');
        }

        [$ok, $failed] = $sync->syncAll();

        return $failed
            ? back()->with('error', "Refreshed {$ok}, {$failed} failed — see the notes on each product.")
            : back()->with('success', 'Refreshed '.$ok.' '.Str::plural('product', $ok).($added ? " ({$added} new from your team)" : '').'.');
    }

    public function destroy(StoreProduct $product): RedirectResponse
    {
        $product->delete();

        return back()->with('success', "{$product->name} removed from the site.");
    }
}
