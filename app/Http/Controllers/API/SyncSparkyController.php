<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MediaManager;
use App\Repositories\MediaManagerRepository;
use Illuminate\Http\Request;
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

class SyncSparkyController extends Controller
{
    public function __construct()
    {

    }
    public function sync(Request $request)
    {
        try {
            $productCategories = $request->post('product_category');
            $productAttributeSet = $request->post('product_attribute_set');
            $productAttribute = $request->post('product_attribute');
            $product = $request->post('product');

            if (!empty($productCategories) && is_array($productCategories)) {
                $ids = [];
            
                foreach ($productCategories as $category) {
                    // Ensure ID exists or create a new one
                    $categoryId = $category['id'] ?? null;
            
                    $categoryModel = Category::updateOrCreate(
                        ['id' => $categoryId], // Match by ID
                        [
                            'name' => html_entity_decode($category['name']),
                            'slug' => $category['product_key'] ? $category['product_key'] : Str::slug($category['name']),
                            'parent_id' => $category['parent_id'] ?? null,
                            'depth_level' => $category['parent_id'] ? 2 : 1,
                            'icon' => $category['preview_url'] ?? null,
                            'searchable' => 1,
                            'status' => 1,
                            'total_sale' => $category['total_items'] ?? 0,
                            'avg_rating' => 0,
                            'commission_rate' => 0
                        ]
                    );
            
                    $ids[] = $categoryModel->id; // Ensure the correct ID is stored
                }
            
                // Prevent accidental deletion of all categories
                if (!empty($ids)) {
                    Category::whereNotIn('id', $ids)->delete();
                }
            }

            if (!empty($productAttributeSet) && is_array($productAttributeSet)) {
                $attributeSetIds = [];
            
                foreach ($productAttributeSet as $attributeSet) {
                    // Ensure ID exists or create a new one
                    $attributeSetId = $attributeSet['id'] ?? null;
            
                    $attribute = Attribute::updateOrCreate(
                        ['id' => $attributeSetId], // Match by ID
                        [
                            'name' => $attributeSet['title'],
                            'display_type' => $attributeSet['display_layout'],
                            'description' => '',
                            'status' => $attributeSet['status'] == 'published' ? 1 : 0,
                            'created_by' => 1,
                            'updated_by' => 1
                        ]
                    );
            
                    $attributeSetIds[] = $attribute->id; // Ensure correct ID is stored
                }
            
                // Prevent accidental mass deletion
                if (!empty($attributeSetIds)) {
                    Attribute::whereNotIn('id', $attributeSetIds)->delete();
                }
            }
            
            if (!empty($productAttribute) && is_array($productAttribute)) {
                $attributeValueIds = [];
                $colors = [];
                foreach ($productAttribute as $attribute) {
                    // Ensure ID exists or create a new one
                    $attributeId = $attribute['id'] ?? null;
            
                    $attributeValue = AttributeValue::updateOrCreate(
                        ['id' => $attributeId],
                        [
                            'value' => $attribute['attribute_set_id'] == 1 ? $attribute['color'] : $attribute['title'],
                            'attribute_id' => $attribute['attribute_set_id']
                        ]
                    );
                    
                    $attributeValueIds[] = $attributeValue->id; // Store the updated/created ID

                    if ($attribute['attribute_set_id'] == 1) {
                        $colors[] = [
                            'name' => $attribute['title'], // Use array key, not object property
                            'attribute_value_id' => $attributeValue->id
                        ];
                    }
                }
            
                // Insert or update Color records
                if (!empty($colors)) {
                    foreach ($colors as $color) {
                        Color::updateOrCreate(
                            ['attribute_value_id' => $color['attribute_value_id']],
                            ['name' => $color['name']]
                        );
                    }
                }
                // Prevent accidental mass deletion
                if (!empty($attributeValueIds)) {
                    AttributeValue::whereNotIn('id', $attributeValueIds)->delete();
                    Color::whereNotIn('attribute_value_id', $attributeValueIds)->delete();
                }
            }

            if (!empty($productAttributeSet) && !empty($productAttribute)) {
                // Get the valid attribute IDs and attribute value IDs
                $validAttributeIds = Attribute::pluck('id')->toArray();
                $validAttributeValueIds = AttributeValue::pluck('id')->toArray();

                // Fetch the product_variation IDs and associated product_sku_id values before deletion
                $variationsToDelete = ProductVariations::whereNotIn('attribute_id', $validAttributeIds)
                    ->orWhereNotIn('attribute_value_id', $validAttributeValueIds)
                    ->get(['id', 'product_sku_id']); // Fetch both id and product_sku_id

                // Extract the product_sku_ids that need to be deleted
                $productSkuIdsToDelete = $variationsToDelete->pluck('product_sku_id')->unique()->toArray();

                // Extract the product_variation IDs
                $variationIdsToDelete = $variationsToDelete->pluck('id')->toArray();

                // Delete the invalid product variations
                ProductVariations::whereIn('id', $variationIdsToDelete)->delete();

                // Delete the corresponding product_sku records
                ProductSku::whereIn('id', $productSkuIdsToDelete)->delete();

                // Optional: Log or return the deleted variation IDs
                return $variationsToDelete;
            }

            if (!empty($product) && $product['id']) {
                $variations = $product['variations'];
                $productAttributeSets = $product['attribute_sets'];
                $galleries = $product["galleries"];
                $variant_product = false;
                if (!empty($variations)) {
                    $variant_product = true;
                }

                // Update Product
                $newProduct = Product::updateOrCreate(
                    ['id' => $product['id']],
                    [
                        'product_name' => $product['name'],
                        'product_type' => $variant_product ? 2 : 1,
                        'variant_sku_prefix' => $product['variant_sku_prefix'] ?? $product['sku'],
                        'barcode_type' => $product['barcode_type'],
                        'description' => $product['shortdescription'],
                        'unit_type_id' => 7,
                        'discount_type' => 1,
                        'minimum_order_qty' => 1,
                        'is_physical' => 1,
                        'is_approved' => 1,
                        'status' => $product['status'] == 'available' ? 1 : 0 ,
                        'video_provider' => 'youtube'
                    ]
                );

                // Update Product Category
                CategoryProduct::updateOrCreate(
                    ['product_id' => $newProduct->id],
                    [
                        'category_id' => $product['category_id']
                    ]
                );

                $mediaRepository = null;
                $mediaIds = [];
                // Update Product Images
                ProductGalaryImage::where('product_id', $newProduct->id)->delete();
                foreach ($galleries as $gallery) {
                    $media = MediaManager::where('external_link', $gallery['url'])->first();
                    if (!$media) {
                        if (!$mediaRepository) {
                            $mediaRepository = app(MediaManagerRepository::class);
                        }
                        $response = $mediaRepository->downloadAndSaveImage($gallery['url']);
                        if ($response['success']) {
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
                    $newProduct->media_ids = implode($mediaIds);
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
                    $newProductSku->weight = 0;
                    $newProductSku->length = 0;
                    $newProductSku->breadth = 0;
                    $newProductSku->height = 0;
                    $newProductSku->status = $product['status'];
                    $newProductSku->save();
                } else {
                    $skuIds = [];
                    $pVariationIds = [];
                    // save product variation and product sku
                    foreach ($variations as $ind => $variant) {
                        $sku = '';
                        $sku .= str_replace(' ', '-', $newProduct->variant_sku_prefix);
                        foreach ($variant['items'] as $item) {
                            $item_value = \Modules\Product\Entities\AttributeValue::find($item['attribute_id']);
                            if ($item_value->attribute_id == 1) {
                                $item = $item_value->color->name;
                            }else {
                                $item = $item_value->value;
                            }
                            $sku .= '-'.str_replace(' ', '', $item);
                        }

                        // Update Product Sku
                        $newProductSku = ProductSku::where('product_id', $newProduct->id)
                        ->where('variation_id', $variant['id'])->first();
                        if (!$newProductSku instanceof ProductSku) {
                            $newProductSku = new ProductSku();
                        }
                        $newProductSku->product_id = $newProduct->id;
                        $newProductSku->variation_id = $variant['id'];
                        $newProductSku->sku = $sku;
                        $newProductSku->track_sku = $sku;
                        $newProductSku->purchase_price = $variant['sale_price'];
                        $newProductSku->selling_price = $variant['sale_price'];
                        $newProductSku->weight = 0;
                        $newProductSku->length = 0;
                        $newProductSku->breadth = 0;
                        $newProductSku->height = 0;
                        $newProductSku->status = 1;
                        $newProductSku->save();
                        $skuIds[] = $newProductSku->id;

                        foreach ($variant['items'] as $item) {
                            $productVariation = ProductVariations::where('product_id', $newProduct->id)
                            ->where('product_sku_id',  $newProductSku->id)
                            ->where('attribute_id', $item['attribute_set_id'])
                            ->where('attribute_value_id', $item['attribute_id'])
                            ->first();
                            if (!$productVariation) {
                                $productVariation = new ProductVariations();
                                $productVariation->product_sku_id = $newProductSku->id;
                                $productVariation->product_id = $newProduct->id;
                                $productVariation->attribute_id = $item['attribute_set_id'];
                                $productVariation->attribute_value_id = $item['attribute_id'];
                                $productVariation->save();
                            }
                            $pVariationIds[] = $productVariation->id;
                        }
                    }
                    
                    if (!empty($skuIds)) {
                        ProductSku::where('product_id', $newProduct->id)->whereNotIn('id', $skuIds)->delete();
                    }
                    if (!empty($pVariationIds)) {
                        ProductVariations::where('product_id', $newProduct->id)->whereNotIn('id', $pVariationIds)->delete();
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