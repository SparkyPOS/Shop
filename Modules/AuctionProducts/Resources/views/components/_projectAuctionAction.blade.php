<!-- shortby  -->
<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ __('common.select') }}
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
        <a href="{{ route('auctionproducts.settings', $auctions->id) }}" target="_blank" class="dropdown-item product_detail" data-id="{{$auctions->id}}">{{__('auctionproduct.settings')}}</a>
        <a href="{{ route('auctionproducts.view', [$auctions->id, $auctions->seller_product_id]) }}" target="_blank" class="dropdown-item product_detail" data-id="{{$auctions->id}}">{{__('auctionproduct.view')}}</a>
        <a href="{{ route('auctionproducts.edit', $auctions->id) }}" class="dropdown-item product_detail" data-id="{{$auctions->id}}">{{__('common.edit')}}</a>
        <a href="{{ route('auctionproducts.view.all.bids', $auctions->id) }}" class="dropdown-item product_detail" data-id="{{$auctions->id}}">{{__('auctionproduct.view_all_bids')}}</a>
        <a href="{{ route('auctionproducts.destroy') }}" class="dropdown-item product_detail delete_auction" data-id="{{$auctions->id}}">{{__('common.delete')}}</a>
    </div>
</div>
<!-- shortby  -->
