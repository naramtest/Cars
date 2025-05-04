<?php

namespace App\Services\Payments\Providers\Stripe;

use Cache;
use Stripe\Price;
use Stripe\Product;

class StripeCatalogService
{
    public function getOrCreateProduct(string $key, string $name): string
    {
        $cacheKey = "stripe_product_$key";
        return Cache::remember($cacheKey, now()->addDays(30), function () use (
            $name
        ) {
            return Product::create(["name" => $name])->id;
        });
    }

    public function getOrCreatePrice(
        string $productId,
        int $amount,
        string $currency
    ): string {
        $cacheKey = "stripe_price_{$productId}_{$amount}_$currency";
        return Cache::remember($cacheKey, now()->addDays(30), function () use (
            $productId,
            $amount,
            $currency
        ) {
            return Price::create([
                "product" => $productId,
                "unit_amount" => $amount,
                "currency" => strtolower($currency),
            ])->id;
        });
    }
}
