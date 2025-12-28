<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductSku;
use Modules\Product\Entities\ProductVariations;
use Modules\Product\Entities\Attribute;
use Modules\Product\Entities\AttributeValue;
use Modules\Seller\Entities\SellerProduct;
use Modules\AuctionProducts\Entities\Auction;

class ProductSyncService
{
    public function syncProductById(int $productId): void
    {
        $baseUrl = rtrim(config('sync.base_url', ''), '/');

        if (!$baseUrl) {
            Log::debug('Sync skipped: sync.base_url not configured');
            return;
        }

        $product = Product::with(['skus', 'categories', 'gallary_images'])->find($productId);
        if (!$product) {
            return;
        }

        $sku = optional($product->skus->first())->sku;
        $trackSku = optional($product->skus->first())->track_sku;

//        dd( $product->seller->SellerAccount?->vendor_id);
        // Decide unit group name for POS (only "In Store" or "Online") based on product type
        $type = $product->is_physical ? 'tangible' : 'digital';
        $posUnitGroupName = $type === 'digital' ? 'Online' : 'In Store';

        $payload = [
            'vendor_id' => $product->seller->SellerAccount?->vendor_id, // manage by vendor
            'sku' => $sku,
            'external_product_id'=>$productId,
            'name' => $product->product_name,
            'barcode' => $trackSku, // no dedicated barcode field; use track_sku if present
            'barcode_type' => $product->barcode_type,
            'description' => $product->description,
            'category_id' => optional($product->categories->first())->name,
            // send name instead of id; POS will resolve to its UnitGroup
            'unit_group' => $posUnitGroupName,
            'status' => $product->status == 1 ? 'available' : 'unavailable',
            'stock_management' => ((string)$product->stock_manage === '1' || $product->stock_manage === 1 || $product->stock_manage === true) ? 'enabled' : 'disabled',
            'tax_type' => $product->tax_type ?: null,
            'tax_group_id' => $product->gst_group_id ?? null,
            'product_type' => 'product',
            'type' => $type,
            // Shipping (product-level)
            'shipping_type' => $product->shipping_type,
            'shipping_cost' => (float) ($product->shipping_cost ?? 0),
        ];

        // Build variants if the product has variations
        $skus = ProductSku::where('product_id', $productId)->with('product_variations')->get();

        $totalStock = (int) $skus->sum('product_stock');
        $hasVariants = $skus->count() > 1 || $product->product_type == 2;
        \Log::info('shop.out.stock.computed', [
            'product_id' => $productId,
            'has_variants' => $hasVariants,
            'quantity' => $totalStock,
            'sku_count' => $skus->count(),
        ]);

        if ($hasVariants) {
            $payload['product_type'] = 'variable';
            $payload['quantity'] = $totalStock; // POS aggregates stock on parent

            // Collect distinct attribute sets used by this product
            $attributeIds = ProductVariations::whereIn('product_sku_id', $skus->pluck('id')->all())
                ->pluck('attribute_id')->unique()->sort()->values();

            $attributes = Attribute::whereIn('id', $attributeIds)->get()->keyBy('id');

            // Define attribute set order and slugs
            $order = [];
            foreach ($attributeIds as $aid) {
                $set = $attributes->get($aid);
                if (!$set) continue;
                $slug = \Illuminate\Support\Str::slug($set->name, '_');
                $order[] = [ 'id' => $set->id, 'title' => $set->name, 'slug' => $slug ];
            }

            // variant_attributes expected by POS ProductService
            $payload['variant_attributes'] = array_map(function($row) {
                return [
                    'set_id' => 0, // will be resolved/created in POS by title
                    'name' => $row['slug'],
                    'selected' => true,
                    'set_title' => $row['title'],
                ];
            }, $order);

            // Enrich payload with attribute sets and values (for mapping external ids on POS)
            $payload['product_attribute_set'] = array_map(function($row) use ($attributes) {
                $set = $attributes->get($row['id']);
                return [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'display_layout' => method_exists($set,'getAttribute') ? ($set->display_type ?? 'text') : 'text',
                    'status' => 'published',
                ];
            }, $order);

            $valueItems = [];
            foreach ($skus as $skuRow) {
                foreach ($skuRow->product_variations as $pv) {
                    $value = AttributeValue::find($pv->attribute_value_id);
                    if (!$value) continue;
                    $valueItems[$value->id] = [
                        'id' => $value->id,
                        'attribute_set_id' => $pv->attribute_id,
                        'title' => $value->value ?? $value->title,
                        'color' => $value->color->name ?? null,
                    ];
                }
            }
            $payload['product_attribute'] = array_values($valueItems);

            // Build variants list with sale_price and attribute values per slug
            $variants = [];
            foreach ($skus as $skuRow) {
                $variant = [
                    'variation_id' => 0,
                    'sale_price' => (float) $skuRow->selling_price,
                    'stock' => (int) $skuRow->product_stock,
                    // Optional per-variant shipping dimensions
                    'weight' => (float) ($skuRow->weight ?? 0),
                    'length' => (float) ($skuRow->length ?? 0),
                    'breadth' => (float) ($skuRow->breadth ?? 0),
                    'height' => (float) ($skuRow->height ?? 0),
                    'additional_shipping' => (float) ($skuRow->additional_shipping ?? 0),
                ];
                $pv = $skuRow->product_variations; // list rows with attribute_id and attribute_value_id
                foreach ($order as $row) {
                    $valTitle = null;
                    $pvRow = $pv->firstWhere('attribute_id', $row['id']);
                    if ($pvRow) {
                        $val = AttributeValue::find($pvRow->attribute_value_id);
                        if ($val) {
                            $valTitle = $val->attribute_id == 1 && $val->color ? ($val->color->name ?? $val->title ?? $val->value) : ($val->title ?? $val->value);
                        }
                    }
                    $variant[$row['slug']] = $valTitle ?? '';
                }
                $variants[] = $variant;
            }
            $payload['variants'] = $variants;

            // Provide parent-level price fields for POS unit quantity defaults
            // Use the minimum sale price across variants for a sensible base
            $variantPrices = array_map(function($v){ return (float)($v['sale_price'] ?? 0); }, $variants);
            $payload['sale_price_edit'] = !empty($variantPrices) ? min($variantPrices) : 0;
            // Use minimum purchase price across skus for wholesale baseline
            $wholesaleCandidates = $product->skus->pluck('purchase_price')->filter()->map(fn($v) => (float)$v)->all();
            $payload['wholesale_price_edit'] = !empty($wholesaleCandidates) ? min($wholesaleCandidates) : (float) ($product->skus->first()->purchase_price ?? 0);
        } else {
            $payload['sale_price_edit'] = optional($product->skus->first())->selling_price ?? 0;
            $payload['wholesale_price_edit'] = optional($product->skus->first())->purchase_price ?? 0;
            $payload['quantity'] = $totalStock;
            // Single SKU shipping dimensions
            if ($product->skus->first()) {
                $firstSku = $product->skus->first();
                $payload['weight'] = (float) ($firstSku->weight ?? 0);
                $payload['length'] = (float) ($firstSku->length ?? 0);
                $payload['breadth'] = (float) ($firstSku->breadth ?? 0);
                $payload['height'] = (float) ($firstSku->height ?? 0);
                $payload['additional_shipping'] = (float) ($firstSku->additional_shipping ?? 0);
            }
        }

        // Auction data (if module active and record exists)
        try {
            $sellerProductId = SellerProduct::where('product_id', $productId)->value('id');
            if ($sellerProductId) {
                $auction = Auction::where('seller_product_id', $sellerProductId)->first();
                if ($auction) {
                    $payload['start_auction'] = optional($auction->auction_start_date)->format('Y-m-d');
                    $payload['end_auction'] = optional($auction->auction_end_date)->format('Y-m-d');
                    $payload['auction_bid_start'] = (float) ($auction->starting_bidding_price ?? 0);
                    $payload['reserve_price'] = (float) ($auction->reserve_price ?? 0);
                    $payload['increment_price'] = (float) ($auction->increment_price ?? 0);
                    $payload['entry_amount'] = (float) ($auction->entry_amount ?? 0);
                }
            }
        } catch (\Throwable $e) {
            // Ignore if module or relations are not present
        }

        // Include product galleries as absolute URLs so POS can fetch them
        try {
            $galleryUrls = [];
            foreach ((array) $product->gallary_images as $gi) {
                if (!empty($gi->images_source)) {
                    $galleryUrls[] = asset(asset_path($gi->images_source));
                }
            }
            if (!empty($galleryUrls)) {
                $payload['galleries'] = array_map(function ($u) {
                    return ['url' => $u];
                }, $galleryUrls);
                // indicate POS to replace existing galleries with provided list
                $payload['replace'] = true;
            }
        } catch (\Throwable $e) {
            Log::warning('POS sync galleries build failed: '.$e->getMessage());
        }

        $url = $baseUrl . '/api/sync/products';
        try {
            Http::timeout(10)->acceptJson()->withHeaders([
                'X-Sync-Token'=> config('sync.token', env('SYNC_TOKEN','123456')),
            ])->post($url, $payload)->throw();
        } catch (\Throwable $e) {
            Log::error('POS sync failed', ['error' => $e->getMessage()]);
        }
    }
}
