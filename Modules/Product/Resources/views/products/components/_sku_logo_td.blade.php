
<div class="product_thumb_div">
    @if($skus->variant_image != null)
    <img src="{{ showImage($skus->variant_image) }}" alt="{{ optional($skus->product)->product_name }}">
    @elseif (optional($skus->product)->thumbnail_image_source != null)
        <img src="{{ showImage($skus->product->thumbnail_image_source) }}" alt="{{ $skus->product->product_name }}">
    @else
        <img src="{{ showImage('backend/img/default.png') }}" alt="{{ optional($skus->product)->product_name }}">
    @endif

</div>
