<?php

namespace App\Http\Controllers;

use App\Models\StoreProduct;
use App\Support\Settings;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function __invoke(Settings $settings): Response
    {
        $products = StoreProduct::showcase()->get();
        abort_if($products->isEmpty(), 404);

        $sales = $settings->bool('gmodstore_show_sales');

        return Inertia::render('Store/Index', [
            'products' => $products->map->toPublicArray($sales),
            'totals' => [
                'products' => $products->count(),
                'reviews' => $products->sum('reviews_count'),
                'rating' => $products->where('reviews_count', '>', 0)->isNotEmpty()
                    ? round($products->sum(fn ($p) => $p->rating * $p->reviews_count) / max(1, $products->sum('reviews_count')), 1)
                    : null,
                'sales' => $sales ? $products->sum('sales_count') : null,
            ],
        ]);
    }
}
