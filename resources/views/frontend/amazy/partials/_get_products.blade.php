@foreach($products as $key => $product)
    @include('frontend.amazy.partials._home_product_card', [
        'product' => $product,
        'cardClass' => 'product_widget5 mb_30 style5',
        'metaClass' => 'product__meta text-center',
        'showStoreVendor' => true,
    ])
@endforeach
