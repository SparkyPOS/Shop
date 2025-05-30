<!-- shortby  -->
<div class="dropdown CRM_dropdown">
    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ __('common.select') }}
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2" >
        <a href="{{ route('auctionproducts.destroy.bid', $bids->id) }}" class="dropdown-item product_detail" data-id="{{$bids->id}}">{{__('common.delete')}}</a>

        @if($bids->bid_amount >=  $auction->reserve_price )
        <form action="{{ route('auctionproducts.email.bidder.award') }}" method="POST">
            @csrf
            <input type="hidden" name="bid_id" value="{{$bids->id}}">
            <input type="hidden" name="auction_id" value="{{$bids->auction_id}}">
            <button type="submit" class="dropdown-item product_detail" data-id="{{$bids->id}}">{{__('auctionproduct.award_bidder')}}</button>
        </form>
        @else
        <button class="dropdown-item alert-award" data-toggle='modal' data-target='#bidAwardAlert' data-bid-id='{{$bids->id}}' data-auction-id='{{$bids->auction_id}}'>{{__('auctionproduct.award_bidder')}}</button>
        @endif
    </div>
</div>
<!-- shortby  -->
