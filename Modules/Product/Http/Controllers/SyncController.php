<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Product\Entities\Brand;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\CategoryProduct;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductSku;
use Modules\Product\Entities\UnitType;

class SyncController extends Controller
{
    /**
     * Sync products from an external source (e.g., SparkyPos) into this app.
     *
     * Expected payload shape (defaults shown; a custom "map" can rebind keys):
     * {
     *   "products": [
     *     {"name":"Prod A","sku":"A-001","price":123.45,"stock":5,"brand":"Acme","category":"Gadgets","unit":"pcs"},
     *     ...
     *   ],
     *   "map": {"name":"pos_name","sku":"pos_sku","price":"sale_price","stock":"qty","brand":"brand_name","category":"category_name","unit":"unit_name"},
     *   "options": {"create_missing_relations": true}
     * }
     */
    public function syncProducts(Request $request): JsonResponse
    {
        $data = $request->validate([
            'products' => 'required|array',
            'products.*' => 'array',
            'map' => 'sometimes|array',
            'options' => 'sometimes|array',
        ]);

        $map = array_merge([
            'name' => 'name',
            'sku' => 'sku',
            'price' => 'price',
            'stock' => 'stock',
            'brand' => 'brand',
            'category' => 'category',
            'unit' => 'unit',
        ], $data['map'] ?? []);

        $createMissing = (bool) data_get($data, 'options.create_missing_relations', true);

        $summary = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($data['products'] as $index => $row) {
            try {
                $payload = $this->extractPayload($row, $map);
                if (!$payload['sku'] || !$payload['name']) {
                    throw new \InvalidArgumentException('Missing required name or sku');
                }

                DB::beginTransaction();

                $brandId = $this->resolveBrandId($payload['brand'], $createMissing);
                $unitTypeId = $this->resolveUnitTypeId($payload['unit'], $createMissing);
                $categoryId = $this->resolveCategoryId($payload['category'], $createMissing);

                $existingSku = ProductSku::with('product')->where('sku', $payload['sku'])->first();
                if ($existingSku) {
                    // Update existing product + sku
                    $product = $existingSku->product;
                    if ($product) {
                        $product->product_name = $payload['name'];
                        if ($brandId) $product->brand_id = $brandId;
                        if ($unitTypeId) $product->unit_type_id = $unitTypeId;
                        $product->is_physical = 1;
                        $product->status = 1;
                        $product->is_approved = 1;
                        $product->save();
                        if ($categoryId) {
                            CategoryProduct::updateOrCreate([
                                'category_id' => $categoryId,
                                'product_id' => $product->id,
                            ], []);
                        }
                    }
                    $existingSku->selling_price = $payload['price'] ?? $existingSku->selling_price;
                    if (!is_null($payload['stock'])) {
                        $existingSku->product_stock = max(0, (int) $payload['stock']);
                    }
                    $existingSku->status = 1;
                    $existingSku->save();

                    $summary['updated']++;
                } else {
                    // Create new product + sku
                    $product = new Product();
                    $product->product_name = $payload['name'];
                    $product->product_type = 1; // single product
                    $product->unit_type_id = $unitTypeId;
                    $product->brand_id = $brandId;
                    $product->shipping_type = 1; // free shipping default
                    $product->shipping_cost = 0;
                    $product->discount_type = 0;
                    $product->discount = 0;
                    $product->tax_type = null;
                    $product->tax = 0;
                    $product->minimum_order_qty = 1;
                    $product->is_physical = 1;
                    $product->is_approved = 1;
                    $product->status = 1;
                    $product->stock_manage = 1; // track stock by default
                    $product->save();

                    if ($categoryId) {
                        CategoryProduct::updateOrCreate([
                            'category_id' => $categoryId,
                            'product_id' => $product->id,
                        ], []);
                    }

                    $sku = new ProductSku();
                    $sku->product_id = $product->id;
                    $sku->sku = (string) $payload['sku'];
                    $sku->selling_price = (float) ($payload['price'] ?? 0);
                    $sku->product_stock = max(0, (int) ($payload['stock'] ?? 0));
                    $sku->status = 1;
                    $sku->save();

                    $summary['created']++;
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $summary['failed']++;
                $summary['errors'][] = [
                    'index' => $index,
                    'message' => $e->getMessage(),
                ];
                Log::error('[Product Sync] Row failed', [
                    'row' => $row,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => 'ok',
            'summary' => $summary,
        ]);
    }

    private function extractPayload(array $row, array $map): array
    {
        $getter = static function(array $arr, $key) {
            // support dot notation in mapping keys
            return data_get($arr, $key);
        };
        return [
            'name' => trim((string) $getter($row, $map['name'] ?? 'name')),
            'sku' => trim((string) $getter($row, $map['sku'] ?? 'sku')),
            'price' => is_null($getter($row, $map['price'] ?? 'price')) ? null : (float) $getter($row, $map['price'] ?? 'price'),
            'stock' => is_null($getter($row, $map['stock'] ?? 'stock')) ? null : (int) $getter($row, $map['stock'] ?? 'stock'),
            'brand' => $getter($row, $map['brand'] ?? 'brand'),
            'category' => $getter($row, $map['category'] ?? 'category'),
            'unit' => $getter($row, $map['unit'] ?? 'unit'),
        ];
    }

    private function resolveBrandId($brandName, bool $create): ?int
    {
        if (!$brandName) return null;
        $name = trim((string) $brandName);
        $existing = Brand::where('name', $name)->first();
        if ($existing) return $existing->id;
        if (!$create) return null;
        $brand = new Brand();
        $brand->name = $name;
        $brand->status = 1;
        $brand->save();
        return $brand->id;
    }

    private function resolveUnitTypeId($unitName, bool $create): ?int
    {
        if (!$unitName) return null;
        $name = trim((string) $unitName);
        $existing = UnitType::where('name', $name)->first();
        if ($existing) return $existing->id;
        if (!$create) return null;
        $unit = new UnitType();
        $unit->name = $name;
        $unit->status = 1;
        $unit->save();
        return $unit->id;
    }

    private function resolveCategoryId($categoryName, bool $create): ?int
    {
        if (!$categoryName) return null;
        $name = trim((string) $categoryName);
        $existing = Category::where('name', $name)->first();
        if ($existing) return $existing->id;
        if (!$create) return null;
        $cat = new Category();
        $cat->name = $name;
        $cat->status = 1;
        $cat->save();
        return $cat->id;
    }
}

