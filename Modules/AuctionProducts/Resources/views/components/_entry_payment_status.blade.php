@if($payment->status == 0)
<h6><span class="badge_4">{{ __('auctionproduct.pending') }}</span></h6>
@elseif ($payment->status == 1)
<h6><span class="badge_1">{{ __('auctionproduct.paid') }}</span></h6>
@else
<h6><span class="badge_4">{{ __('auctionproduct.declined') }}</span></h6>
@endif
