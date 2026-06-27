<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MediaManager;
use App\Repositories\MediaManagerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Product\Entities\Category;
use Illuminate\Support\Str;
use Modules\Product\Entities\Attribute;
use Modules\Product\Entities\AttributeValue;
use Modules\Product\Entities\CategoryProduct;
use Modules\Product\Entities\Color;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductGalaryImage;
use Modules\Product\Entities\ProductSku;
use Modules\Product\Entities\ProductVariations;
use Modules\MultiVendor\Entities\SellerAccount;
use Modules\Seller\Entities\SellerProduct;
use Modules\Seller\Entities\SellerProductSKU;
use Modules\AuctionProducts\Entities\Auction;
use Modules\Shipping\Entities\ProductShipping;
use App\Models\UsedMedia;

class SyncSparkyController extends Controller
{
    public function __construct()
    {

    }
    /**
     * Resolve a category identifier that may be an external id, local id, full name or partial name/slug.
     */
    private function resolveCategoryId($input): ?int
    {
        if (is_array($input)) {
            $input = $input['id'] ?? $input['name'] ?? $input['slug'] ?? null;
        }
        if ($input === null || $input === '') {
            return null;
        }

        // Numeric: try external id first, then local id
        if (is_numeric($input)) {
            $num = (int) $input;
            $byExternal = Category::where('external_category_id', $num)->value('id');
            if ($byExternal) return $byExternal;
            $byLocal = Category::where('id', $num)->value('id');
            if ($byLocal) return $byLocal;
        }

        $needle = $this->normalizeCategoryString((string) $input);
        $categories = Category::query()->get(['id','name','slug']);

        // Exact name match (case-insensitive, normalized)
        $exact = $categories->first(function($c) use ($needle) {
            return $this->normalizeCategoryString((string) $c->name) === $needle
                || $this->normalizeCategoryString((string) $c->slug) === $needle;
        });
        if ($exact) return $exact->id;

        // Contains match on name or slug
        $contains = $categories->first(function($c) use ($needle) {
            return mb_stripos($this->normalizeCategoryString((string)$c->name), $needle) !== false
                || mb_stripos($this->normalizeCategoryString((string)$c->slug), $needle) !== false;
        });
        if ($contains) return $contains->id;

        return null;
    }

    private function normalizeCategoryString(string $value): string
    {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = preg_replace('/\s+/u', ' ', $decoded ?? '');
        return mb_strtolower(trim($decoded));
    }

    private function extractShippingPayload(array $product): array
    {
        $shipping = isset($product['shipping']) && is_array($product['shipping']) ? $product['shipping'] : [];

        $cost = $product['shipping_cost'] ?? $product['shippingc'] ?? $shipping['shipping_cost'] ?? $shipping['shippingc'] ?? null;
        $type = $product['shipping_type'] ?? $shipping['shipping_type'] ?? null;
        $pickup = $product['shipping_pickup'] ?? $shipping['shipping_pickup'] ?? null;
        $location = $product['shipping_location'] ?? $shipping['shipping_location'] ?? null;
        $processing = $product['processing_time'] ?? $product['shippingpt'] ?? $shipping['processing_time'] ?? $shipping['shippingpt'] ?? null;

        $weight = $product['weight'] ?? $product['shippingweight'] ?? $shipping['weight'] ?? $shipping['shippingweight'] ?? null;
        $length = $product['length'] ?? $product['shippinglength'] ?? $shipping['length'] ?? $shipping['shippinglength'] ?? null;
        $height = $product['height'] ?? $product['shippingheight'] ?? $shipping['height'] ?? $shipping['shippingheight'] ?? null;
        $width = $product['breadth'] ?? $product['shippingwidth'] ?? $shipping['breadth'] ?? $shipping['shippingwidth'] ?? null;

        return [
            'shipping_cost' => is_numeric($cost) ? (float) $cost : null,
            'shipping_type' => is_numeric($type) ? (int) $type : null,
            'shipping_pickup' => is_string($pickup) ? strtolower(trim($pickup)) : null,
            'shipping_location' => is_string($location) ? trim($location) : null,
            'processing_time' => is_string($processing) ? trim($processing) : null,
            'weight' => is_numeric($weight) ? (float) $weight : null,
            'length' => is_numeric($length) ? (float) $length : null,
            'height' => is_numeric($height) ? (float) $height : null,
            'breadth' => is_numeric($width) ? (float) $width : null,
        ];
    }

    private function resolveShopShippingType(array $shippingPayload): int
    {
        if ($shippingPayload['shipping_type'] !== null) {
            return (int) $shippingPayload['shipping_type'];
        }

        if (in_array($shippingPayload['shipping_pickup'], ['yes', 'y', 'true', '1', 'on', 'enabled'], true)) {
            return 1;
        }

        return (($shippingPayload['shipping_cost'] ?? 0) > 0) ? 2 : 1;
    }

    /**
     * Find the canonical category matching external_id/slug/name and merge duplicates into it.
     */
    private function findCanonicalCategoryAndMergeDuplicates(int $externalId, string $slug, string $name): ?Category
    {
        $slug = trim((string) $slug);
        $normalizedName = $this->normalizeCategoryString((string) $name);

        $candidates = Category::query()
            ->where(function ($query) use ($externalId, $slug) {
                $query->where('external_category_id', $externalId);
                if ($slug !== '') {
                    $query->orWhere('slug', $slug);
                }
            })
            ->get(['id', 'name', 'slug', 'external_category_id']);

        if ($normalizedName !== '') {
            $nameMatches = Category::query()
                ->get(['id', 'name', 'slug', 'external_category_id'])
                ->filter(function ($row) use ($normalizedName) {
                    return $this->normalizeCategoryString((string) $row->name) === $normalizedName;
                });
            $candidates = $candidates->merge($nameMatches)->unique('id')->values();
        }

        if ($candidates->isEmpty()) {
            return null;
        }

        $primary = $candidates->firstWhere('external_category_id', $externalId)
            ?: ($slug !== '' ? $candidates->firstWhere('slug', $slug) : null)
            ?: $candidates->sortBy('id')->first();

        if (!$primary instanceof Category) {
            return null;
        }

        $duplicates = $candidates->filter(function ($row) use ($primary) {
            return (int) $row->id !== (int) $primary->id;
        })->values();

        if ($duplicates->isNotEmpty()) {
            foreach ($duplicates as $duplicate) {
                $this->repointCategoryReferencesAndDelete((int) $duplicate->id, (int) $primary->id);
            }
        }

        return Category::find($primary->id);
    }

    /**
     * Move all FK references from $fromCategoryId to $toCategoryId, then delete the duplicate category.
     */
    private function repointCategoryReferencesAndDelete(int $fromCategoryId, int $toCategoryId): void
    {
        if ($fromCategoryId <= 0 || $toCategoryId <= 0 || $fromCategoryId === $toCategoryId) {
            return;
        }

        // Ensure hierarchy references are moved even when DB FK is not declared.
        Category::where('parent_id', $fromCategoryId)->update(['parent_id' => $toCategoryId]);

        $database = DB::getDatabaseName();
        $foreignKeys = DB::select(
            'SELECT TABLE_NAME, COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND REFERENCED_TABLE_NAME = ?
               AND REFERENCED_COLUMN_NAME = ?',
            [$database, 'categories', 'id']
        );

        foreach ($foreignKeys as $fk) {
            $table = (string) ($fk->TABLE_NAME ?? '');
            $column = (string) ($fk->COLUMN_NAME ?? '');
            if ($table === '' || $column === '') {
                continue;
            }
            if ($table === 'categories' && $column === 'id') {
                continue;
            }
            DB::table($table)->where($column, $fromCategoryId)->update([$column => $toCategoryId]);
        }

        Category::where('id', $fromCategoryId)->delete();
    }

    public function sync(Request $request)
    {
        try {
            Log::debug(json_encode(['headers'=>$request->headers->all(),'has_files'=>$request->hasFile(null),'keys'=>array_keys($request->all())]));
            // Mark this request as inbound to avoid re-propagation loops
            app()->instance('sync::inbound', true);
            // Support multipart: accept a JSON payload field named "payload"
            $payload = null;
            if ($request->has('payload') && is_string($request->input('payload'))) {
                $decoded = json_decode($request->input('payload'), true);
                if (is_array($decoded)) { $payload = $decoded; }
            }
            $productCategories = $payload['product_category'] ?? $request->post('product_category');
            $productAttributeSet = $payload['product_attribute_set'] ?? $request->post('product_attribute_set');
            $productAttribute = $payload['product_attribute'] ?? $request->post('product_attribute');
            $product = $payload['product'] ?? $request->post('product');
            $action = $payload['action'] ?? $request->post('action');
            $categorySyncScope = $payload['product_category_sync_scope'] ?? $request->post('product_category_sync_scope');


            // Handle delete action
            if ($action === 'delete') {
                $externalId = $product['id'] ?? null;
                $externalSku = trim((string) ($product['sku'] ?? ''));

                $local = null;
                if (!empty($externalId)) {
                    $local = Product::where('external_product_id', $externalId)->first();
                }

                // Fallback for legacy rows where external_product_id may be missing.
                if (!($local instanceof Product) && $externalSku !== '') {
                    $productIdBySku = ProductSku::where('sku', $externalSku)->value('product_id');
                    if (!empty($productIdBySku)) {
                        $local = Product::find($productIdBySku);
                    }
                }

                if ($local instanceof Product) {
                    $sellerProductIds = SellerProduct::where('product_id', $local->id)->pluck('id');
                    if ($sellerProductIds->isNotEmpty()) {
                        SellerProductSKU::whereIn('product_id', $sellerProductIds)->delete();
                        Auction::whereIn('seller_product_id', $sellerProductIds)->delete();
                        SellerProduct::whereIn('id', $sellerProductIds)->delete();
                    }

                    // cascade delete skus/variations via DB constraints or manual cleanup
                    foreach ($local->skus as $sku) { $sku->delete(); }
                    foreach ($local->variations as $var) { $var->delete(); }
                    $local_auction = Auction::where('seller_product_id', $local->id)->first();
                    if($local_auction instanceof Auction) {
                        $local_auction->delete();
                    }
                    $local->delete();
                }

                return ['success' => true, 'action' => 'deleted', 'found' => (bool) ($local instanceof Product)];
            }

            if (!empty($productCategories) && is_array($productCategories)) {
                $incomingExternalIds = [];
                $externalToParentExternal = [];

                // Pass 1: upsert categories without parent dependency.
                foreach ($productCategories as $category) {
                    $externalId = isset($category['id']) ? (int) $category['id'] : null;
                    if (!$externalId) {
                        continue;
                    }

                    $incomingExternalIds[] = $externalId;
                    $externalToParentExternal[$externalId] = !empty($category['parent_id']) ? (int) $category['parent_id'] : null;
                    $name = html_entity_decode((string) ($category['name'] ?? ''));
                    $slug = Str::slug((string) (!empty($category['product_key']) ? $category['product_key'] : $name));
                    if ($slug === '') {
                        $slug = 'category-' . $externalId;
                    }

                    $categoryPayload = [
                        'name' => $name,
                        'slug' => $slug,
                        'parent_id' => 0,
                        'depth_level' => 1,
                        'icon' => $category['preview_url'] ?? null,
                        'searchable' => 1,
                        'status' => 1,
                        'total_sale' => $category['total_items'] ?? 0,
                        'avg_rating' => 0,
                        'commission_rate' => 0,
                        'external_category_id' => $externalId,
                    ];

                    // Match by external id, slug, and name, and merge duplicates into one canonical row.
                    $existingCategory = $this->findCanonicalCategoryAndMergeDuplicates($externalId, $slug, $name);

                    if ($existingCategory) {
                        $existingCategory->fill($categoryPayload);
                        $existingCategory->save();
                    } else {
                        Category::create($categoryPayload);
                    }
                }

                // Pass 2: resolve parent relation after all categories exist locally.
                if (!empty($externalToParentExternal)) {
                    $localIdsByExternal = Category::whereIn('external_category_id', array_keys($externalToParentExternal))
                        ->pluck('id', 'external_category_id')
                        ->toArray();

                    $parentExternalIds = array_values(array_filter($externalToParentExternal));
                    $parentLocalIdsByExternal = [];
                    if (!empty($parentExternalIds)) {
                        $parentLocalIdsByExternal = Category::whereIn('external_category_id', $parentExternalIds)
                            ->pluck('id', 'external_category_id')
                            ->toArray();
                    }

                    foreach ($externalToParentExternal as $externalId => $parentExternalId) {
                        $localId = $localIdsByExternal[$externalId] ?? null;
                        if (!$localId) {
                            continue;
                        }

                        $parentLocalId = 0;
                        if ($parentExternalId) {
                            $parentLocalId = (int) ($parentLocalIdsByExternal[$parentExternalId] ?? 0);
                        }

                        Category::where('id', $localId)->update([
                            'parent_id' => $parentLocalId,
                            'depth_level' => $parentLocalId > 0 ? 2 : 1,
                            'status' => 1,
                        ]);
                    }
                }

                // When syncing a full category snapshot, disable stale external categories not present anymore.
                if (($categorySyncScope === 'full' || $categorySyncScope === null) && !empty($incomingExternalIds)) {
                    Category::whereNotNull('external_category_id')
                        ->whereNotIn('external_category_id', $incomingExternalIds)
                        ->update(['status' => 0]);
                }
            }

            $attributeSetMap = [];
            if (!empty($productAttributeSet) && is_array($productAttributeSet)) {
                $attributeSetIds = [];
                foreach ($productAttributeSet as $attributeSet) {
                    $externalSetId = $attributeSet['id'] ?? null;

                    $attribute = Attribute::updateOrCreate(
                        ['external_attribute_set_id' => $externalSetId],
                        [
                            'name' => $attributeSet['title'],
                            'display_type' => $attributeSet['display_layout'],
                            'description' => '',
                            'status' => $attributeSet['status'] == 'published' ? 1 : 0,
                            'created_by' => 1,
                            'updated_by' => 1
                        ]
                    );

                    $attributeSetIds[] = $attribute->id;
                    if ($externalSetId !== null) {
                        $attributeSetMap[(string) $externalSetId] = $attribute;
                    }
                }
            }


            if (!empty($productAttribute) && is_array($productAttribute)) {
                $attributeValueIds = [];
                foreach ($productAttribute as $attribute) {
                    $externalValueId = $attribute['id'] ?? null;
                    $externalSetId = $attribute['attribute_set_id'] ?? null;

                    $localAttributeId = Attribute::where('external_attribute_set_id', $externalSetId)->value('id');
                    $localAttribute = null;
                    if (isset($attributeSetMap[(string) $externalSetId])) {
                        $localAttribute = $attributeSetMap[(string) $externalSetId];
                    } elseif ($localAttributeId) {
                        $localAttribute = Attribute::find($localAttributeId);
                    }

                    $displayType = strtolower((string) ($localAttribute->display_type ?? ''));
                    $attributeName = strtolower((string) ($localAttribute->name ?? ''));
                    $isColorAttribute = str_contains($displayType, 'color') || str_contains($attributeName, 'color');

                    $incomingTitle = isset($attribute['title']) ? trim((string) $attribute['title']) : null;
                    $incomingColor = isset($attribute['color']) ? trim((string) $attribute['color']) : null;
                    $incomingValue = isset($attribute['value']) ? trim((string) $attribute['value']) : null;

                    $attributeValueText = $incomingTitle;
                    if ($isColorAttribute) {
                        // For color attributes, Shop uses attribute_values.value as swatch code.
                        $attributeValueText = $incomingColor ?: ($incomingValue ?: $incomingTitle);
                    } else {
                        $titleIsNumeric = !empty($incomingTitle) && preg_match('/^\d+$/', $incomingTitle);
                        $valueIsReadable = !empty($incomingValue) && !preg_match('/^\d+$/', $incomingValue);
                        // Prefer readable label from "value" when "title" looks id-like.
                        if ($titleIsNumeric && $valueIsReadable) {
                            $attributeValueText = $incomingValue;
                        } elseif (empty($attributeValueText)) {
                            $attributeValueText = $incomingValue ?: $incomingColor;
                        }
                    }

                    $existingValue = null;
                    if (!empty($externalValueId)) {
                        $existingValue = AttributeValue::where('external_attribute_id', $externalValueId)->first();
                    }
                    // Keep readable labels for non-color attributes: don't replace text labels with numeric ids/codes.
                    if (!$isColorAttribute && $existingValue instanceof AttributeValue) {
                        $currentValueText = trim((string) ($existingValue->value ?? ''));
                        if (
                            preg_match('/^\d+$/', (string) $attributeValueText)
                            && $currentValueText !== ''
                            && !preg_match('/^\d+$/', $currentValueText)
                        ) {
                            $attributeValueText = $currentValueText;
                        }
                    }

                    $attributeValue = AttributeValue::updateOrCreate(
                        ['external_attribute_id' => $externalValueId],
                        [
                            'value' => $attributeValueText,
                            'attribute_id' => $localAttributeId
                        ]
                    );

                    $attributeValueIds[] = $attributeValue->id;

                    if ($isColorAttribute) {
                        $colorName = $incomingTitle;
                        if (
                            (empty($colorName) || preg_match('/^[a-z0-9]{2,6}$/i', (string) $colorName))
                            && !empty($incomingValue)
                            && !preg_match('/^#?[0-9a-f]{3,8}$/i', (string) $incomingValue)
                        ) {
                            $colorName = $incomingValue;
                        }
                        Color::updateOrCreate(
                            ['attribute_value_id' => $attributeValue->id],
                            ['name' => $colorName ?: ($incomingColor ?: $attributeValueText)]
                        );
                    } else {
                        // Guard against legacy wrong mapping (non-color attribute accidentally linked as color).
                        Color::where('attribute_value_id', $attributeValue->id)->delete();
                    }
                }

                // Avoid mass-deleting attribute values; only upsert from payload
            }
            // Do not purge existing product variations/SKUs globally here.
            // We’ll handle optional cleanup per-product during variant upsert below.


            if (!empty($product) && ($product['id'] ?? null)) {
                Log::info('shop.sync.product.payload', ['product' => $product]);

                $vendorCode = trim((string) ($prorduct['vendor_id'] ?? ''));
                $sellerId = $vendorCode !== ''
                    ? SellerAccount::where('vendor_id', $vendorCode)->value('user_id')
                    : null;
                if(!$sellerId) {
                    throw new \RuntimeException(
                        sprintf('Unable to sync product %s: Shop seller mapping was not fould for POS vendor %s.', $product['id'], $vendorCode)
                    );
                }
                // Pull variations; fallback to top-level 'variants' if product.variations is missing
                $variations = $product['variations'] ?? [];
                if (empty($variations)) {
                    $variations = $request->input('variants', []);
                }
                $productAttributeSets = $product['attribute_sets'] ?? [];
                if (empty($productAttributeSets)) {
                    $productAttributeSets = $request->input('product_attribute_set', []);
                }
                $galleries = $product["galleries"];
                $variant_product = !empty($variations);
                Log::info('shop.sync.product.variant.detect', [
                    'product_id' => $product['id'] ?? null,
                    'product_variations_count' => is_array($product['variations'] ?? null) ? count($product['variations']) : 0,
                    'fallback_variants_count' => is_array($request->input('variants')) ? count($request->input('variants')) : 0,
                    'variant_product' => $variant_product,
                ]);

                // Decide stock management mapping (default: enabled)
                $manageStock = 1;
                if (isset($product['stock_management'])) {
                    $val = strtolower((string) $product['stock_management']);
                    $manageStock = in_array($val, ['enabled','1','true','yes']) ? 1 : 0;
                } elseif (isset($product['stock_manage'])) {
                    $manageStock = (int) $product['stock_manage'] ? 1 : 0;
                }
                $shippingPayload = $this->extractShippingPayload($product);

                // Update Product
                $newProduct = Product::updateOrCreate(
                    ['external_product_id' => $product['id']],
                    [
                        'product_name' => $product['name'],
                        'product_type' => $variant_product ? 2 : 1,
                        'tax_type' => $product['tax_type'] ?? 'inclusive',
                        'tax' => isset($product['tax_value']) ? (float) $product['tax_value'] : 0,
                        'variant_sku_prefix' => $product['variant_sku_prefix'] ?? $product['sku'],
                        'barcode_type' => $product['barcode_type'],
                        // Prefer non-empty full description; fallback to shortdescription
                        'description' => filled($product['description'] ?? null)
                            ? $product['description']
                            : ($product['shortdescription'] ?? null),
                        'unit_type_id' => 7,
                        'discount_type' => 1,
                        'minimum_order_qty' => 1,
                        'condition' => $product['condition'] ?? 'New',
                        'selling_price' => isset($product['selling_price']) ? (float) $product['selling_price'] : 0,
                        'is_physical' => 1,
                        'is_approved' => 1,
                        'status' => $product['status'] == 'available' ? 1 : 0 ,
                        'stock_manage' => $manageStock,
                        'video_provider' => 'youtube'
                    ]
                );

                // Collect ProductSku rows to later upsert SellerProductSKU after ensuring seller product
                $pendingSellerSkuRows = [];

                // Update Product Category: resolve using external id, id, name, or slug (lenient)
                $categoryInput = $product['category_id'] ?? ($product['category'] ?? ($product['category_name'] ?? null));
                $localCategoryId = $this->resolveCategoryId($categoryInput);
                if ($localCategoryId) {
                    CategoryProduct::updateOrCreate(
                        ['product_id' => $newProduct->id],
                        [
                            'category_id' => $localCategoryId
                        ]
                    );
                }

                $mediaRepository = null;
                $mediaIds = [];
                $primaryMediaId = null;
                // Update Product Images
                // If replace=true, clear existing galleries. Otherwise, append.
                $replace = (bool) ($request->boolean('replace') ?? ($product['replace'] ?? false));
                if ($replace) {
                    ProductGalaryImage::where('product_id', $newProduct->id)->delete();
                }
                // 1) Handle multipart uploaded images first (galleries[] or images[])
                $galleryFiles = [];
                if ($request->hasFile('galleries')) { $galleryFiles = array_merge($galleryFiles, (array) $request->file('galleries')); }
                if ($request->hasFile('images')) { $galleryFiles = array_merge($galleryFiles, (array) $request->file('images')); }
                if (!empty($galleryFiles)) {
                    if (!$mediaRepository) { $mediaRepository = app(MediaManagerRepository::class); }
                    foreach ($galleryFiles as $file) {
                        if (!$file) continue;
                        $resp = $mediaRepository->saveUploadedFile($file, 1);
                        if (!empty($resp['success']) && !empty($resp['media_id'])) {
                            $media = MediaManager::find($resp['media_id']);
                            if ($media) {
                                $gal = new ProductGalaryImage();
                                $gal->product_id = $newProduct->id;
                                $gal->images_source = $media->file_name;
                                $gal->media_id = $media->id;
                                $gal->save();
                                $mediaIds[] = $media->id;
                                if ($primaryMediaId === null) { $primaryMediaId = $media->id; }
                            }
                        }
                    }
                }
                // 2) Handle URL galleries in payload (backward compatible)
                foreach ((array) $galleries as $gallery) {
                    $url = is_array($gallery) ? ($gallery['url'] ?? null) : (is_string($gallery) ? $gallery : null);
                    if (!$url) continue;
                    $media = MediaManager::where('external_link', $url)->first();
                    if (!$media) {
                        if (!$mediaRepository) { $mediaRepository = app(MediaManagerRepository::class); }
                        $response = $mediaRepository->downloadAndSaveImage($url);
                        if (!empty($response['success'])) {
                            $media = MediaManager::find($response['media_id']);
                        }
                    }
                    if ($media) {
                        $gal = new ProductGalaryImage();
                        $gal->product_id = $newProduct->id;
                        $gal->images_source = $media->file_name;
                        $gal->media_id = $media->id;
                        $gal->save();
                        $mediaIds[] = $media->id;
                        if ($primaryMediaId === null) { $primaryMediaId = $media->id; }
                    }
                }

                if (!empty($mediaIds)) {
                    $newProduct->media_ids = implode(',', $mediaIds);
                    // also ensure a thumbnail image is set for list views
                    if ($replace || empty($newProduct->thumbnail_image_source)) {
                        if ($primaryMediaId) {
                            $primaryMedia = MediaManager::find($primaryMediaId);
                            if ($primaryMedia) {
                                $newProduct->thumbnail_image_source = $primaryMedia->file_name;
                            }
                        }
                    }
                    $newProduct->save();
                }
                if (!$variant_product) {
                    // Update Product Sku
                    $newProductSku = ProductSku::where('product_id', $newProduct->id)->first();
                    if (!$newProductSku instanceof ProductSku) {
                        $newProductSku = new ProductSku();
                    }
                    $newProductSku->product_id = $newProduct->id;
                    $newProductSku->sku = $product['sku'];
                    $newProductSku->track_sku = $product['sku'];
                    $newProductSku->purchase_price = $product['purchase_price'];
                    $newProductSku->selling_price = $product['selling_price'];
                    $incomingQty = null;
                    if (isset($product['accurate_tracking']) && is_numeric($product['accurate_tracking'])) { $incomingQty = (int) $product['accurate_tracking']; }
                    elseif (isset($product['quantity']) && is_numeric($product['quantity'])) { $incomingQty = (int) $product['quantity']; }
                    if ($incomingQty !== null) {
                        \Log::info('shop.sync.stock.single_applied', [
                            'product_id' => $newProduct->id,
                            'sku' => $product['sku'] ?? null,
                            'quantity' => $incomingQty,
                        ]);
                        $newProductSku->product_stock = $incomingQty;
                    }
                    // Optional shipping dimensions from payload
                    if ($shippingPayload['weight'] !== null) $newProductSku->weight = $shippingPayload['weight'];
                    if ($shippingPayload['length'] !== null) $newProductSku->length = $shippingPayload['length'];
                    if ($shippingPayload['breadth'] !== null) $newProductSku->breadth = $shippingPayload['breadth'];
                    if ($shippingPayload['height'] !== null) $newProductSku->height = $shippingPayload['height'];
                    // SparkyPOS shipping cost should be reflected in Shop SKU additional shipping.
                    if (array_key_exists('additional_shipping', $product) && $product['additional_shipping'] !== null) {
                        $newProductSku->additional_shipping = (float) $product['additional_shipping'];
                    } elseif ($shippingPayload['shipping_cost'] !== null) {
                        $newProductSku->additional_shipping = $shippingPayload['shipping_cost'];
                    }
                    $newProductSku->status = ($product['status'] == 'available') ? 1 : 0;
                    $newProductSku->save();

                    // queue seller sku upsert for simple product
                    $pendingSellerSkuRows[] = [
                        'product_sku_id' => $newProductSku->id,
                        'product_stock' => $incomingQty !== null ? $incomingQty : ((int) ($newProductSku->product_stock ?? 0)),
                        'selling_price' => (float) ($newProductSku->selling_price ?? 0),
                        'status' => (int) ($newProductSku->status ?? 1),
                    ];
                } else {
                    $replace = (bool) ($request->boolean('replace') ?? ($product['replace'] ?? false));
                    $skuIds = [];
                    $pVariationIds = [];
                    // Prepare lookup maps for name-based fallback
                    $attrSetsMap = [];
                    foreach ((array) $request->input('product_attribute_set', []) as $as) {
                        $attrSetsMap[$as['id'] ?? null] = [
                            'title' => $as['title'] ?? null,
                            'slug' => \Illuminate\Support\Str::slug((string)($as['title'] ?? ''), '_'),
                        ];
                    }
                    $attrValsMap = [];
                    foreach ((array) $request->input('product_attribute', []) as $av) {
                        $title = isset($av['title']) ? trim((string) $av['title']) : null;
                        $value = isset($av['value']) ? trim((string) $av['value']) : null;
                        $attrValsMap[$av['id'] ?? null] = [
                            'title' => !empty($title) ? $title : (!empty($value) ? $value : null),
                            'value' => !empty($value) ? $value : (!empty($title) ? $title : null),
                            'slug'  => $av['slug'] ?? \Illuminate\Support\Str::slug((string)($av['title'] ?? ($av['value'] ?? '')), '_'),
                            'color' => $av['color'] ?? null,
                            'attribute_set_id' => $av['attribute_set_id'] ?? null,
                        ];
                    }

                    // save product variation and product sku
                    Log::info('shop.sync.variants.start', [
                        'external_product_id' => $product['id'] ?? null,
                        'count' => is_array($variations) ? count($variations) : 0,
                    ]);

                    $variantAttributes = (array) $request->input('variant_attributes', []);
                    $namedKeysMode = false;
                    // If variations do not carry 'items', use named keys from 'variant_attributes' & top-level 'variants'
                    if (!empty($variantAttributes) && (!isset($variations[0]['items']) || empty($variations[0]['items']))) {
                        $variations = (array) $request->input('variants', []);
                        $namedKeysMode = true;
                        Log::info('shop.sync.variants.namedkeys.detected', ['variants_count'=>count($variations),'variant_attributes'=>$variantAttributes]);
                    }

                    $receivedAnyVariantStock = false;
                    foreach ($variations as $ind => $variant) {
                        $basePrefix = $newProduct->variant_sku_prefix ?: \Illuminate\Support\Str::slug((string)$newProduct->product_name, '_');
                        if (empty($basePrefix)) { $basePrefix = 'VAR'; }
                        $sku = str_replace(' ', '-', $basePrefix);
                        $localPairs = [];
                        if ($namedKeysMode) {
                            // Build pairs from variant_attributes names and values from current variant row
                            foreach ($variantAttributes as $va) {
                                if (empty($va['name'])) continue;
                                $attName = (string) $va['name'];
                                // read the value from the variant keyed by attribute name (case-insensitive)
                                $valRaw = null;
                                foreach ($variant as $k=>$v) {
                                    if (mb_strtolower($k) === mb_strtolower($attName)) { $valRaw = $v; break; }
                                }
                                if ($valRaw === null) continue;
                                $valStr = trim((string)$valRaw);
                                $appendSku = $valStr;
                                if ($appendSku) { $sku .= '-' . str_replace(' ', '', (string)$appendSku); }

                                // Resolve or create Attribute by name
                                $localAttrId = Attribute::whereRaw('LOWER(name) = ?', [mb_strtolower($attName)])->value('id');
                                if (!$localAttrId) {
                                    $attr = new Attribute();
                                    $attr->name = $attName;
                                    $attr->display_type = 'text';
                                    $attr->status = 1;
                                    $attr->save();
                                    $localAttrId = $attr->id;
                                    Log::info('shop.sync.variants.attrset.created.byname', ['id'=>$localAttrId,'title'=>$attName]);
                                }
                                // Resolve or create AttributeValue by value
                                $low = mb_strtolower($valStr);
                                $valId = AttributeValue::where('attribute_id', $localAttrId)
                                    ->whereRaw('LOWER(value)=?',[$low])
                                    ->value('id');
                                if (!$valId) {
                                    $val = new AttributeValue();
                                    $val->attribute_id = $localAttrId;
                                    $val->value = $valStr;
                                    $val->save();
                                    $valId = $val->id;
                                    if (str_contains(mb_strtolower($attName), 'color')) {
                                        Color::updateOrCreate(
                                            ['attribute_value_id' => $valId],
                                            ['name' => $valStr]
                                        );
                                    }
                                    Log::info('shop.sync.variants.attrval.created.byname', ['id'=>$valId,'value'=>$valStr,'attribute'=>$attName]);
                                }
                                $localPairs[] = [$localAttrId, $valId];
                            }
                        } else {
                            // Original items based mapping
                            foreach ($variant['items'] as $item) {
                                $itemValue = \Modules\Product\Entities\AttributeValue::where('external_attribute_id', $item['attribute_id'])->first();
                                $appendSku = null;
                                if ($itemValue) {
                                    $appendSku = $itemValue->color
                                        ? ($itemValue->color->name ?? $itemValue->value)
                                        : $itemValue->value;
                                } else {
                                    // Fallback using name/title map from product_attribute
                                    $mapped = $attrValsMap[$item['attribute_id'] ?? null] ?? null;
                                    $appendSku = $item['title'] ?? ($mapped['title'] ?? ($mapped['value'] ?? null));
                                }
                                if ($appendSku) {
                                    $sku .= '-' . str_replace(' ', '', (string) $appendSku);
                                }
                            }
                        }

                        // Track if variant-level stock provided
                        if (isset($variant['stock'])) { $receivedAnyVariantStock = true; }

                        // Update Product Sku (idempotent by SKU per product)
                        $newProductSku = ProductSku::where('product_id', $newProduct->id)
                            ->where('sku', $sku)->first();
                        if (!$newProductSku instanceof ProductSku) {
                            $newProductSku = new ProductSku();
                        }
                        $newProductSku->product_id = $newProduct->id;
                        $newProductSku->sku = $sku;
                        $newProductSku->track_sku = $sku;
                        // Map prices: POS sends per-variant sale_price; purchase_price is product-level
                        $basePurchase = null;
                        if (isset($product['purchase_price']) && is_numeric($product['purchase_price'])) {
                            $basePurchase = (float) $product['purchase_price'];
                        } elseif (isset($product['wholesale_price_edit']) && is_numeric($product['wholesale_price_edit'])) {
                            $basePurchase = (float) $product['wholesale_price_edit'];
                        }
                        $newProductSku->purchase_price = $basePurchase !== null ? $basePurchase : (float) ($product['selling_price'] ?? $variant['sale_price'] ?? 0);
                        $newProductSku->selling_price = (float) $variant['sale_price'];
                        $newProductSku->weight = 0;
                        $newProductSku->length = 0;
                        $newProductSku->breadth = 0;
                        $newProductSku->height = 0;
                        $newProductSku->status = ($product['status'] == 'available') ? 1 : 0;
                        // Optional per-variant shipping fields
                        if (isset($variant['stock'])) {
                            $newProductSku->product_stock = (int) $variant['stock'];
                            \Log::info('shop.sync.stock.variant_row_applied', [
                                'product_id' => $newProduct->id,
                                'sku' => $sku,
                                'quantity' => (int) $variant['stock'],
                            ]);
                        }
                        if (isset($variant['weight'])) $newProductSku->weight = (float) $variant['weight'];
                        if (isset($variant['length'])) $newProductSku->length = (float) $variant['length'];
                        if (isset($variant['breadth'])) $newProductSku->breadth = (float) $variant['breadth'];
                        if (isset($variant['height'])) $newProductSku->height = (float) $variant['height'];
                        if (array_key_exists('additional_shipping', $variant) && $variant['additional_shipping'] !== null) {
                            $newProductSku->additional_shipping = (float) $variant['additional_shipping'];
                        } elseif (array_key_exists('additional_shipping', $product) && $product['additional_shipping'] !== null) {
                            $newProductSku->additional_shipping = (float) $product['additional_shipping'];
                        } elseif ($shippingPayload['shipping_cost'] !== null) {
                            $newProductSku->additional_shipping = $shippingPayload['shipping_cost'];
                        }
                        $newProductSku->save();

                        // queue seller sku upsert for variant
                        $pendingSellerSkuRows[] = [
                            'product_sku_id' => $newProductSku->id,
                            'product_stock' => isset($variant['stock']) && is_numeric($variant['stock']) ? (int) $variant['stock'] : ((int) ($newProductSku->product_stock ?? 0)),
                            'selling_price' => (float) ($newProductSku->selling_price ?? 0),
                            'status' => (int) ($newProductSku->status ?? 1),
                        ];
                        // Variant image via multipart: variant_images_by_sku[<SKU>]
                        if ($request->hasFile('variant_images_by_sku')) {
                            $map = (array) $request->file('variant_images_by_sku');
                            if (isset($map[$sku]) && $map[$sku]) {
                                try {
                                    if (!$mediaRepository) { $mediaRepository = app(MediaManagerRepository::class); }
                                    $resp = $mediaRepository->saveUploadedFile($map[$sku], 1);
                                    if (!empty($resp['success']) && !empty($resp['media_id'])) {
                                        $media = MediaManager::find($resp['media_id']);
                                        if ($media) {
                                            $newProductSku->variant_image = $media->file_name;
                                            $newProductSku->save();
                                        }
                                    }
                                } catch (\Throwable $e) { Log::warning('shop.sync.variant_image.save_failed', ['sku'=>$sku,'error'=>$e->getMessage()]); }
                            }
                        }
                        Log::info('shop.sync.variants.sku.upsert', [
                            'product_id' => $newProduct->id,
                            'sku_id' => $newProductSku->id,
                            'sku' => $newProductSku->sku,
                            'price' => $newProductSku->selling_price,
                        ]);
                        $skuIds[] = $newProductSku->id;

                        if ($namedKeysMode) {
                            foreach ($localPairs as [$localAttrId, $localAttrValueId]) {
                                $productVariation = ProductVariations::where('product_id', $newProduct->id)
                                    ->where('product_sku_id',  $newProductSku->id)
                                    ->where('attribute_id', $localAttrId)
                                    ->where('attribute_value_id', $localAttrValueId)
                                    ->first();
                                if (!$productVariation) {
                                    $productVariation = new ProductVariations();
                                    $productVariation->product_sku_id = $newProductSku->id;
                                    $productVariation->product_id = $newProduct->id;
                                    $productVariation->attribute_id = $localAttrId;
                                    $productVariation->attribute_value_id = $localAttrValueId;
                                    $productVariation->save();
                                    Log::info('shop.sync.variants.pvar.created', [
                                        'id' => $productVariation->id,
                                        'product_id' => $newProduct->id,
                                        'sku_id' => $newProductSku->id,
                                        'attr_id' => $localAttrId,
                                        'attr_val_id' => $localAttrValueId,
                                    ]);
                                }
                                $pVariationIds[] = $productVariation->id;
                            }
                        } else {
                        foreach ($variant['items'] as $item) {
                            $needle = null;
                            // Resolve local attribute id (by external id or by set title)
                            $localAttrId = Attribute::where('external_attribute_set_id', $item['attribute_set_id'])->value('id');
                            $valMeta = $attrValsMap[$item['attribute_id'] ?? null] ?? null;
                            if (!$localAttrId && !empty($valMeta['attribute_set_id'])) {
                                $localAttrId = Attribute::where('external_attribute_set_id', $valMeta['attribute_set_id'])->value('id');
                            }
                            if (!$localAttrId) {
                                $setMeta = $attrSetsMap[$item['attribute_set_id'] ?? null] ?? null;
                                if (!$setMeta && !empty($valMeta['attribute_set_id'])) {
                                    $setMeta = $attrSetsMap[$valMeta['attribute_set_id']] ?? null;
                                }
                                if ($setMeta) {
                                    $localAttrId = Attribute::whereRaw('LOWER(name) = ?', [mb_strtolower($setMeta['title'])])->value('id')
                                        ?: Attribute::whereRaw('LOWER(name) = ?', [mb_strtolower($setMeta['slug'])])->value('id');
                                    // Create attribute set if still missing
                                    if (!$localAttrId && !empty($setMeta['title'])) {
                                        $attr = new Attribute();
                                        $attr->name = $setMeta['title'];
                                        $attr->display_type = 'text';
                                        $attr->status = 1;
                                        $attr->external_attribute_set_id = $item['attribute_set_id'] ?? null;
                                        $attr->save();
                                        $localAttrId = $attr->id;
                                        Log::info('shop.sync.variants.attrset.created', ['id'=>$localAttrId,'title'=>$setMeta['title']]);
                                    }
                                }
                            }
                            // Resolve local attribute value id (by external id or by title/value)
                            $localAttrValueId = AttributeValue::where('external_attribute_id', $item['attribute_id'])->value('id');
                            if (!$localAttrValueId && $localAttrId) {
                                $setName = (string) optional(Attribute::find($localAttrId))->name;
                                $isColorAttribute = str_contains(mb_strtolower($setName), 'color');
                                $needle = null;
                                if ($isColorAttribute) {
                                    $needle = $item['color'] ?? ($valMeta['color'] ?? ($item['title'] ?? ($valMeta['title'] ?? null)));
                                } else {
                                    $needle = $item['title'] ?? ($valMeta['title'] ?? ($valMeta['value'] ?? null));
                                }
                                if ($needle) {
                                    $low = mb_strtolower($needle);
                                    $localAttrValueId = AttributeValue::where('attribute_id', $localAttrId)
                                        ->whereRaw('LOWER(value) = ?', [$low])
                                        ->value('id');
                                }
                            }
                            // Create attribute value if still missing and we have a name
                            if (!$localAttrValueId && $localAttrId && !empty($needle)) {
                                $val = new AttributeValue();
                                $val->attribute_id = $localAttrId;
                                $val->value = $needle;
                                $val->external_attribute_id = $item['attribute_id'] ?? null;
                                $val->save();
                                $localAttrValueId = $val->id;
                                Log::info('shop.sync.variants.attrval.created', ['id'=>$localAttrValueId,'value'=>$needle]);
                            }
                            // As a robust fallback, try exact match on AttributeValue.value within all values when set id couldn't be resolved
                            if (!$localAttrValueId && !empty($needle)) {
                                $low = mb_strtolower($needle);
                                $localAttrValueId = AttributeValue::whereRaw('LOWER(value) = ?', [$low])->value('id');
                                // If we got a value match but no set id, try to pick the corresponding attribute id
                                if ($localAttrValueId && !$localAttrId) {
                                    $localAttrId = AttributeValue::where('id', $localAttrValueId)->value('attribute_id');
                                }
                            }
                            if (!$localAttrId || !$localAttrValueId) {
                                Log::warning('shop.sync.variants.attr.resolve.failed', [
                                    'product_id' => $newProduct->id,
                                    'set_external_id' => $item['attribute_set_id'] ?? null,
                                    'val_external_id' => $item['attribute_id'] ?? null,
                                    'local_set_id' => $localAttrId,
                                    'local_val_id' => $localAttrValueId,
                                ]);
                                continue;
                            }

                            // Normalize attribute value labels using variation item payload to avoid id-like labels in UI.
                            $linkedAttr = Attribute::find($localAttrId);
                            $linkedValue = AttributeValue::find($localAttrValueId);
                            if ($linkedValue instanceof AttributeValue) {
                                $linkedAttrName = strtolower((string) ($linkedAttr->name ?? ''));
                                $linkedIsColor = str_contains($linkedAttrName, 'color');
                                $preferredTitle = trim((string) ($item['title'] ?? ($valMeta['title'] ?? '')));
                                $preferredValue = trim((string) ($valMeta['value'] ?? ''));
                                $currentValueText = trim((string) ($linkedValue->value ?? ''));

                                if ($linkedIsColor) {
                                    $colorName = $preferredTitle;
                                    if (
                                        (empty($colorName) || preg_match('/^[a-z0-9]{2,6}$/i', (string) $colorName))
                                        && !empty($preferredValue)
                                        && !preg_match('/^#?[0-9a-f]{3,8}$/i', (string) $preferredValue)
                                    ) {
                                        $colorName = $preferredValue;
                                    }
                                    if ($colorName !== '') {
                                        Color::updateOrCreate(
                                            ['attribute_value_id' => $linkedValue->id],
                                            ['name' => $colorName]
                                        );
                                    }
                                } else {
                                    if (
                                        $preferredTitle !== ''
                                        && !preg_match('/^\d+$/', $preferredTitle)
                                        && ($currentValueText === '' || preg_match('/^\d+$/', $currentValueText))
                                    ) {
                                        $linkedValue->value = $preferredTitle;
                                        $linkedValue->save();
                                    }
                                }
                            }

                            $productVariation = ProductVariations::where('product_id', $newProduct->id)
                            ->where('product_sku_id',  $newProductSku->id)
                            ->where('attribute_id', $localAttrId)
                            ->where('attribute_value_id', $localAttrValueId)
                            ->first();
                            if (!$productVariation) {
                                $productVariation = new ProductVariations();
                                $productVariation->product_sku_id = $newProductSku->id;
                                $productVariation->product_id = $newProduct->id;
                                $productVariation->attribute_id = $localAttrId;
                                $productVariation->attribute_value_id = $localAttrValueId;
                                $productVariation->save();
                                Log::info('shop.sync.variants.pvar.created', [
                                    'id' => $productVariation->id,
                                    'product_id' => $newProduct->id,
                                    'sku_id' => $newProductSku->id,
                                    'attr_id' => $localAttrId,
                                    'attr_val_id' => $localAttrValueId,
                                ]);
                            }
                            $pVariationIds[] = $productVariation->id;
                        }
                        }
                    }

                    // If no variant stock provided, but top-level quantity/stock provided, set it to the first SKU
                    if (!$receivedAnyVariantStock) {
                        $topQty = null;
                        if (isset($product['quantity']) && is_numeric($product['quantity'])) { $topQty = (int) $product['quantity']; }
                        elseif (isset($product['stock']) && is_numeric($product['stock'])) { $topQty = (int) $product['stock']; }
                        if ($topQty !== null) {
                            $firstSku = ProductSku::where('product_id', $newProduct->id)->orderBy('id')->first();
                            if ($firstSku) {
                                $firstSku->product_stock = $topQty;
                                $firstSku->save();
                                \Log::info('shop.sync.variants.top_stock_applied', ['product_id'=>$newProduct->id,'sku_id'=>$firstSku->id,'stock'=>$topQty]);
                                // also queue seller sku for this top-level stock assignment
                                $pendingSellerSkuRows[] = [
                                    'product_sku_id' => $firstSku->id,
                                    'product_stock' => (int) $topQty,
                                    'selling_price' => (float) ($firstSku->selling_price ?? 0),
                                    'status' => (int) ($firstSku->status ?? 1),
                                ];
                            }
                        }
                    }

                    if ($replace) {
                        if (!empty($skuIds)) {
                            ProductSku::where('product_id', $newProduct->id)->whereNotIn('id', $skuIds)->delete();
                            Log::info('shop.sync.variants.cleanup.sku', ['product_id'=>$newProduct->id, 'kept'=> $skuIds]);
                        }
                        if (!empty($pVariationIds)) {
                            ProductVariations::where('product_id', $newProduct->id)->whereNotIn('id', $pVariationIds)->delete();
                            Log::info('shop.sync.variants.cleanup.pvars', ['product_id'=>$newProduct->id, 'kept'=> $pVariationIds]);
                        }
                    } else {
                        Log::info('shop.sync.variants.cleanup.skipped', ['product_id' => $newProduct->id]);
                    }
                    // Ensure product marked as variant when variations provided
                    $newProduct->product_type = 2; // 2 = variant product
                    $newProduct->save();
                    Log::info('shop.sync.variants.done', ['product_id'=>$newProduct->id]);
                }

                // Enforce POS->Shop rule:
                // - products synced from SparkyPOS are always physical on Shop
                $newProduct->is_physical = 1;
                $newProduct->shipping_type = $this->resolveShopShippingType($shippingPayload);
                if ($shippingPayload['shipping_cost'] !== null) $newProduct->shipping_cost = $shippingPayload['shipping_cost'];
                if ($shippingPayload['shipping_pickup'] !== null && Schema::hasColumn('products', 'shipping_pickup')) {
                    $newProduct->shipping_pickup = $shippingPayload['shipping_pickup'];
                }
                if (!empty($shippingPayload['shipping_location']) && Schema::hasColumn('products', 'shipping_location')) {
                    $newProduct->shipping_location = $shippingPayload['shipping_location'];
                }
                if (($shippingPayload['processing_time'] ?? '') !== '') {
                    $newProduct->processing_time = $shippingPayload['processing_time'];
                }
                // Ensure name is up to date as well
                if (!empty($product['name'])) $newProduct->product_name = $product['name'];
                $newProduct->save();

                // Upsert seller product, map SKUs to seller, and auction info when vendor_id provided
                if (!empty($product['vendor_id'])) {
                    if ($sellerId) {
                        $sellerProduct = SellerProduct::updateOrCreate([
                            'product_id' => $newProduct->id,
                            'user_id' => $sellerId,
                        ], [
                            'product_name' => $newProduct->product_name,
                            'status' => 1,
                            'is_approved' => 1,
                            'stock_manage' => $manageStock,
                            'tax_type' => $newProduct->tax_type ?? 'inclusive',
                            'tax' => $newProduct->tax ?? 0,
                            'discount' => 0,
                            'discount_type' => 1,
                            'slug' => \Illuminate\Support\Str::slug($newProduct->product_name),
                        ]);

                        // ensure seller thumbnail is set so product shows on listings
                        if ($primaryMediaId) {
                            $primaryMedia = MediaManager::find($primaryMediaId);
                            if ($primaryMedia) {
                                if ($replace || empty($sellerProduct->thum_img)) {
                                    $sellerProduct->thum_img = $primaryMedia->file_name;
                                    $sellerProduct->save();
                                }
                                // map UsedMedia for thumb_image so other components can resolve
                                UsedMedia::updateOrCreate([
                                    'usable_id' => $sellerProduct->id,
                                    'usable_type' => get_class($sellerProduct),
                                    'used_for' => 'thumb_image',
                                ], [
                                    'media_id' => $primaryMedia->id,
                                ]);
                            }
                        }

                        // Upsert SellerProductSKU rows for all collected SKUs
                        $keptSellerSkuIds = [];
                        foreach ($pendingSellerSkuRows as $row) {
                            $sellerSku = SellerProductSKU::updateOrCreate([
                                'product_id' => $sellerProduct->id,
                                'product_sku_id' => $row['product_sku_id'],
                                'user_id' => $sellerId,
                            ], [
                                'product_stock' => (int) ($row['product_stock'] ?? 0),
                                'selling_price' => (float) ($row['selling_price'] ?? 0),
                                'status' => (int) ($row['status'] ?? 1),
                            ]);
                            $keptSellerSkuIds[] = $sellerSku->id;
                        }

                        // Cleanup removed SellerProductSKU when replace is requested
                        $replace = (bool) ($request->boolean('replace') ?? ($product['replace'] ?? false));
                        if ($replace && !empty($keptSellerSkuIds)) {
                            SellerProductSKU::where('product_id', $sellerProduct->id)
                                ->whereNotIn('id', $keptSellerSkuIds)
                                ->delete();
                        }

                        // Map auction fields: only create/list when both dates are provided
                        $hasStart = !empty($product['start_auction']);
                        $hasEnd = !empty($product['end_auction']);
                        $hasAuctionDates = $hasStart && $hasEnd;
                        if ($hasAuctionDates) {
                            $auction = Auction::firstOrNew([
                                'seller_product_id' => $sellerProduct->id,
                            ]);
                            $auction->user_id = $sellerId;
                            $auction->auction_title = $newProduct->product_name;
                            $auction->quantity = 1;
                            if (isset($product['auction_bid_start'])) $auction->starting_bidding_price = (float) $product['auction_bid_start'];
                            $auction->auction_start_date = substr((string) $product['start_auction'], 0, 10);
                            $auction->auction_end_date = substr((string) $product['end_auction'], 0, 10);
                            // optional extended fields if POS provides
                            $reservePrice = isset($product['reserve_price']) ? (float) $product['reserve_price'] : 0;
                            if ($reservePrice <= 0) {
                                $reservePrice = (float) ($product['selling_price'] ?? $product['sale_price_edit'] ?? 0);
                            }
                            $auction->reserve_price = $reservePrice;
                            if (isset($product['increment_price'])) $auction->increment_price = (float) $product['increment_price'];
                            // Shop auctions follow a marketplace bid flow with no entry fee.
                            $auction->entry_amount = 0;
                            $auction->status = 1; // list auction
                            $auction->save();
                        } else {
                            // If dates are missing/null, ensure auction is not listed
                            $existingAuction = Auction::where('seller_product_id', $sellerProduct->id)->first();
                            if ($existingAuction) {
                                $existingAuction->status = 0; // unlist
                                if (!$hasStart) { $existingAuction->auction_start_date = null; }
                                if (!$hasEnd) { $existingAuction->auction_end_date = null; }
                                $existingAuction->save();
                            }
                        }
                    }
                }
            }

        } catch (\Throwable $th) {
            Log::error('shop.sync.failed', [
                'action' => $action ?? null,
                'external_product_id' => $product['id'] ?? null,
                'vendor_id' => $product['vendor_id'] ?? null,
                'error' => $th->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $th->getMessage()
            ];
        }

        return [
            'success' => true
        ];
    }
}
