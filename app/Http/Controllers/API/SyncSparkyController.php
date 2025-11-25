<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MediaManager;
use App\Repositories\MediaManagerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
use Modules\AuctionProducts\Entities\Auction;
use Modules\Shipping\Entities\ProductShipping;

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


            // Handle delete action
            if ($action === 'delete' && !empty($product['id'])) {
                $local = Product::where('external_product_id', $product['id'])->first();
                if ($local instanceof Product) {
                    // cascade delete skus/variations via DB constraints or manual cleanup
                    foreach ($local->skus as $sku) { $sku->delete(); }
                    foreach ($local->variations as $var) { $var->delete(); }
                    $local->delete();
                }
                return ['success' => true, 'action' => 'deleted'];
            }

            if (!empty($productCategories) && is_array($productCategories)) {
                $ids = [];
                foreach ($productCategories as $category) {
                    $externalId = $category['id'] ?? null;
                    $parentLocalId = null;
                    if (!empty($category['parent_id'])) {
                        $parentLocalId = Category::where('external_category_id', $category['parent_id'])->value('id');
                    }

                    $categoryModel = Category::updateOrCreate(
                        ['external_category_id' => $externalId],
                        [
                            'name' => html_entity_decode($category['name']),
                            'slug' => $category['product_key'] ? $category['product_key'] : Str::slug($category['name']),
                            'parent_id' => $parentLocalId,
                            'depth_level' => $parentLocalId ? 2 : 1,
                            'icon' => $category['preview_url'] ?? null,
                            'searchable' => 1,
                            'status' => 1,
                            'total_sale' => $category['total_items'] ?? 0,
                            'avg_rating' => 0,
                            'commission_rate' => 0
                        ]
                    );

                    $ids[] = $categoryModel->id;
                }

                // Avoid mass-deleting categories; only upsert from payload
            }

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
                }
            }


            if (!empty($productAttribute) && is_array($productAttribute)) {
                $attributeValueIds = [];
                $colors = [];
                foreach ($productAttribute as $attribute) {
                    $externalValueId = $attribute['id'] ?? null;
                    $externalSetId = $attribute['attribute_set_id'] ?? null;

                    $localAttributeId = Attribute::where('external_attribute_set_id', $externalSetId)->value('id');

                    $attributeValue = AttributeValue::updateOrCreate(
                        ['external_attribute_id' => $externalValueId],
                        [
                            'value' => $externalSetId == 1 ? ($attribute['color'] ?? $attribute['title'] ?? null) : ($attribute['title'] ?? null),
                            'attribute_id' => $localAttributeId
                        ]
                    );

                    $attributeValueIds[] = $attributeValue->id;

                    if ($externalSetId == 1) {
                        $colors[] = [
                            'name' => $attribute['title'] ?? '',
                            'attribute_value_id' => $attributeValue->id
                        ];
                    }
                }

                if (!empty($colors)) {
                    foreach ($colors as $color) {
                        Color::updateOrCreate(
                            ['attribute_value_id' => $color['attribute_value_id']],
                            ['name' => $color['name']]
                        );
                    }
                }

                // Avoid mass-deleting attribute values; only upsert from payload
            }
            // Do not purge existing product variations/SKUs globally here.
            // We’ll handle optional cleanup per-product during variant upsert below.


            if (!empty($product) && ($product['id'] ?? null)) {
                Log::info('shop.sync.product.payload', ['product' => $product]);
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

                // Update Product
                $newProduct = Product::updateOrCreate(
                    ['external_product_id' => $product['id']],
                    [
                        'product_name' => $product['name'],
                        'product_type' => $variant_product ? 2 : 1,
                        'variant_sku_prefix' => $product['variant_sku_prefix'] ?? $product['sku'],
                        'barcode_type' => $product['barcode_type'],
                        // Prefer full description; fallback to shortdescription
                        'description' => $product['description'] ?? ($product['shortdescription'] ?? null),
                        'unit_type_id' => 7,
                        'discount_type' => 1,
                        'minimum_order_qty' => 1,
                        'condition' => $product['condition'] ?? 'new',
                        'is_physical' => 1,
                        'is_approved' => 1,
                        'status' => $product['status'] == 'available' ? 1 : 0 ,
                        'video_provider' => 'youtube'
                    ]
                );

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
                    }
                }

                if (!empty($mediaIds)) {
                    $newProduct->media_ids = implode(',', $mediaIds);
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
                    if (isset($product['stock']) && is_numeric($product['stock'])) { $incomingQty = (int) $product['stock']; }
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
                    if (isset($product['weight'])) $newProductSku->weight = (float) $product['weight'];
                    if (isset($product['length'])) $newProductSku->length = (float) $product['length'];
                    if (isset($product['breadth'])) $newProductSku->breadth = (float) $product['breadth'];
                    if (isset($product['height'])) $newProductSku->height = (float) $product['height'];
                    if (isset($product['additional_shipping'])) $newProductSku->additional_shipping = (float) $product['additional_shipping'];
                    $newProductSku->status = ($product['status'] == 'available') ? 1 : 0;
                    $newProductSku->save();
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
                        $attrValsMap[$av['id'] ?? null] = [
                            'title' => $av['title'] ?? ($av['value'] ?? null),
                            'value' => $av['value'] ?? ($av['title'] ?? null),
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
                                // Resolve or create AttributeValue by value/title
                                $low = mb_strtolower($valStr);
                                $valId = AttributeValue::where('attribute_id', $localAttrId)
                                    ->where(function($q) use ($low){ $q->whereRaw('LOWER(value)=?',[$low])->orWhereRaw('LOWER(title)=?',[$low]); })
                                    ->value('id');
                                if (!$valId) {
                                    $val = new AttributeValue();
                                    $val->attribute_id = $localAttrId;
                                    $val->value = $valStr;
                                    // Store color code if attribute name suggests color
                                    if (mb_strtolower($attName) === 'color') { $val->color = $valStr; }
                                    $val->save();
                                    $valId = $val->id;
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
                                    $appendSku = $itemValue->attribute_id == 1 ? ($itemValue->color->name ?? $itemValue->value ?? $itemValue->title) : ($itemValue->value ?? $itemValue->title);
                                } else {
                                    // Fallback using name/title map from product_attribute
                                    $mapped = $attrValsMap[$item['attribute_id'] ?? null] ?? null;
                                    $appendSku = $mapped['value'] ?? $mapped['title'] ?? null;
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
                        $newProductSku->purchase_price = $variant['sale_price'];
                        $newProductSku->selling_price = $variant['sale_price'];
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
                        if (isset($variant['additional_shipping'])) $newProductSku->additional_shipping = (float) $variant['additional_shipping'];
                        $newProductSku->save();
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
                            // Resolve local attribute id (by external id or by set title)
                            $localAttrId = Attribute::where('external_attribute_set_id', $item['attribute_set_id'])->value('id');
                            if (!$localAttrId) {
                                $setMeta = $attrSetsMap[$item['attribute_set_id'] ?? null] ?? null;
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
                                $valMeta = $attrValsMap[$item['attribute_id'] ?? null] ?? null;
                                $needle = $valMeta['value'] ?? $valMeta['title'] ?? null;
                                if ($needle) {
                                    $low = mb_strtolower($needle);
                                    $localAttrValueId = AttributeValue::where('attribute_id', $localAttrId)
                                        ->where(function($q) use ($low) {
                                            $q->whereRaw('LOWER(value) = ?', [$low])->orWhereRaw('LOWER(title) = ?', [$low]);
                                        })->value('id');
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
                            // As a robust fallback, try exact match on AttributeValue.value/title within all values when set id couldn't be resolved
                            if (!$localAttrValueId && !empty($needle)) {
                                $low = mb_strtolower($needle);
                                $localAttrValueId = AttributeValue::where(function($q) use ($low) {
                                    $q->whereRaw('LOWER(value) = ?', [$low])->orWhereRaw('LOWER(title) = ?', [$low]);
                                })->value('id');
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

                // Sync physical/digital mapping and product-level shipping
                if (!empty($product['type'])) {
                    $newProduct->is_physical = strtolower($product['type']) === 'tangible' ? 1 : 0;
                }
                if (isset($product['shipping_type'])) $newProduct->shipping_type = (int) $product['shipping_type'];
                if (isset($product['shipping_cost'])) $newProduct->shipping_cost = (float) $product['shipping_cost'];
                // Ensure name is up to date as well
                if (!empty($product['name'])) $newProduct->product_name = $product['name'];
                $newProduct->save();

                // Upsert seller product and auction info when vendor_id provided
                if (!empty($product['vendor_id'])) {
                    $sellerId = SellerAccount::where('vendor_id', $product['vendor_id'])->value('user_id');
                    if ($sellerId) {
                        $sellerProduct = SellerProduct::firstOrCreate([
                            'product_id' => $newProduct->id,
                            'user_id' => $sellerId,
                        ], [
                            'product_name' => $newProduct->product_name,
                            'status' => 1,
                            'is_approved' => 1,
                            'stock_manage' => $newProduct->stock_manage ?? 1,
                            'tax' => $newProduct->tax ?? 0,
                            'tax_type' => $newProduct->tax_type ?? '0',
                            'discount' => 0,
                            'discount_type' => 1,
                            'slug' => \Illuminate\Support\Str::slug($newProduct->product_name),
                        ]);

                        // Map auction fields if provided
                        $hasAuction = isset($product['start_auction']) || isset($product['end_auction']) || isset($product['auction_bid_start']);
                        if ($hasAuction) {
                            $auction = Auction::firstOrNew([
                                'seller_product_id' => $sellerProduct->id,
                            ]);
                            $auction->user_id = $sellerId;
                            $auction->auction_title = $newProduct->product_name;
                            $auction->quantity = 1;
                            if (isset($product['auction_bid_start'])) $auction->starting_bidding_price = (float) $product['auction_bid_start'];
                            if (isset($product['start_auction'])) $auction->auction_start_date = substr($product['start_auction'],0,10);
                            if (isset($product['end_auction'])) $auction->auction_end_date = substr($product['end_auction'],0,10);
                            // optional extended fields if POS provides
                            if (isset($product['reserve_price'])) $auction->reserve_price = (float) $product['reserve_price'];
                            if (isset($product['increment_price'])) $auction->increment_price = (float) $product['increment_price'];
                            if (isset($product['entry_amount'])) $auction->entry_amount = (float) $product['entry_amount'];
                            $auction->status = 1;
                            $auction->save();
                        }
                    }
                }
            }

        } catch (\Throwable $th) {
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
