<!-- Modal::start  -->
<div class="modal fade placebid_modal" id="placebid_modal" tabindex="-1" role="dialog" aria-labelledby="placebid_modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <form id="place_bid_form">
        <div class="modal-header">
          <h5 class="modal-title" id="placebid_modalLabel">{{__('auctionproduct.place_bid')}}</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            <div class="form-group" id="bid_amount-group">
                <label for="bid_amount">Bid Amount<span class="text-danger">*</span>  </label>
                @if($auction->increment_price != 0)
                    @if($max_bid > 0)
                        <input type="text" name="bid_amount" readonly value="{{ $max_bid + $auction->increment_price }}" class="form-control" id="bid_amount" placeholder="Enter Bid Amount">
                    @else
                        <input type="text" name="bid_amount" readonly value="{{ $auction->starting_bidding_price }}" class="form-control" id="bid_amount" placeholder="Enter Bid Amount">
                    @endif
                @else
                    @if($max_bid > 0)
                        <input type="text" name="bid_amount"  value="" class="form-control" id="bid_amount" placeholder="Enter Bid Amount">
                    @else
                        <input type="text" name="bid_amount"  value="{{ $auction->starting_bidding_price }}" class="form-control" id="bid_amount" placeholder="Enter Bid Amount">
                    @endif
                @endif
            </div>
            <input type="hidden" name="auction_id" id="auction_id" value="{{$auction->id}}">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" id="bidModalBtn" class="btn btn-primary auction-bg-red" >{{__('common.save')}}</button>
        </div>

        </form>
      </div>
    </div>
  </div>
