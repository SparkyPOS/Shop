<div class="modal fade" id="bidAwardAlert" tabindex="-1" aria-labelledby="bidAwardAlertModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bidAwardAlertModalLabel">{{__('auctionproduct.award_bidder')}} </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('auctionproducts.email.bidder.award') }}" method="post">
            @csrf

                <p class="text-center">Bid amount is below reserve price of this auction. Do you want to award the bidder ?</p>

             <input type="hidden" name="bid_id" value="" id="bid_id_input">
             <input type="hidden" name="auction_id" value="" id="auction_id_input">


             <div class="w-100 text-center">
                <button type="submit" class="primary_btn_2 mt-5 text-center saveBtn">{{__('auctionproduct.award_bidder')}}</button>
             </div>

        </form>

      </div>
    </div>
  </div>
</div>
