<div class="modal fade" id="bidHistory" tabindex="-1" aria-labelledby="bidHistoryLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="bidHistoryLabel"> {{ __('Bid History') }}</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%">{{__('auctionproduct.sl')}}</th>
                    <th>{{ __('auctionproduct.user') }}</th>
                    <th class="text-center">{{ __("auctionproduct.bid_amount") }}</th>
                </tr>

                <tbody>
                    @foreach($bids as $key => $bid)
                    <tr>
                        <td style="width: 5%">{{ $key + 1 }}</td>
                        <td>{{ !empty($bid->user) ? $bid->user->first_name.' '.$bid->user->last_name:'' }}</td>
                        <td class="text-center" style="width: 10%">
                            {{ single_price($bid->bid_amount) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </thead>
        </table>
      </div>
    </div>
  </div>
</div>
