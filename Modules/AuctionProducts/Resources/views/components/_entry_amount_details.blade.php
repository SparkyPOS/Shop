<div class="modal fade admin-query" id="detailsModal">
    <div class="modal-dialog modal_800px modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('auctionproduct.details') }}</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <i class="ti-close "></i>
                </button>
            </div>
            <div class="modal-body">

                @if($payment->paymentMethod->slug == 'bank-payment')
                <div class="row">
                    @php
                        $info_json = $payment->paymentInfo;
                        $info = json_decode($info_json->payment_info);
                    @endphp
                    <div class="col">
                        <table class="table">
                            <thead>


                                    <tr>
                                        <td style="width: 25%;">Bank Name</td>
                                        <td style="width:5%">:</td>
                                        <td style="width: 70%;">{{ $info->bank_name }}</td>
                                    </tr>

                                    <tr>
                                        <td style="width: 25%;">Branch Name</td>
                                        <td style="width:5%">:</td>
                                        <td style="width: 70%;">{{ $info->branch_name }}</td>
                                    </tr>

                                    <tr>
                                        <td style="width: 25%;">Account Number</td>
                                        <td style="width:5%">:</td>
                                        <td style="width: 70%;">{{ $info->account_number }}</td>
                                    </tr>

                                    <tr>
                                        <td style="width: 25%;">Account Holder</td>
                                        <td style="width:5%">:</td>
                                        <td style="width: 70%;">{{ $info->account_holder }}</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 25%;">Amount</td>
                                        <td style="width:5%">:</td>
                                        <td style="width: 70%;">{{ single_price($payment->amount) }}</td>
                                    </tr>

                            </thead>
                        </table>
                    </div>
                    @if(!empty($info->image))
                        <div class="col">
                            <img src="{{ asset('public/'.$info->image) }}" alt="" class="img-fluid">
                        </div>
                    @endif

                </div>
                @elseif($payment->paymentMethod->slug == 'wallet')
                   <div class="row">
                    <div class="col">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td style="width: 25%;">Gateway</td>
                                    <td style="width:5%">:</td>
                                    <td style="width: 70%;">Wallet</td>
                                </tr>

                                <tr>
                                    <td style="width: 25%;">Amount</td>
                                    <td style="width:5%">:</td>
                                    <td style="width: 70%;">{{ single_price($payment->amount) }}</td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                   </div>
                
                @elseif($payment->paymentMethod->slug == 'clickpay')
                    @php
                        $info_json = $payment->paymentInfo;
                        $info = json_decode($info_json->payment_info);
                        
                    @endphp
                   <div class="row">
                    <div class="col">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td style="width: 25%;">Gateway</td>
                                    <td style="width:5%">:</td>
                                    <td style="width: 70%;">Clickpay</td>
                                </tr>

                                <tr>
                                    <td style="width: 25%;">Amount</td>
                                    <td style="width:5%">:</td>
                                    <td style="width: 70%;">{{ single_price($payment->amount) }}</td>
                                </tr>
                                
                                <tr>
                                    <td style="width: 25%;">Transection ID</td>
                                    <td style="width:5%">:</td>
                                    <td style="width: 70%;">{{ !empty($info->transection_id) ? $info->transection_id:'' }}</td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                   </div>
                @endif
                
                
                @if($payment->status == 0)
                <div class="row">
                    <div class="col">
                        <a class="primary_btn_2 mt-5 text-center" href="{{ route('auctionproducts.entryAmountStatusChange',$payment->id) }}?status=1">
                            {{ __('auctionproduct.mark_as_paid') }}
                        </a>
                    </div>
                    <div class="col">
                        <a class="primary_btn_2 mt-5 text-center" href="{{ route('auctionproducts.entryAmountStatusChange',$payment->id) }}?status=2">
                            {{ __('auctionproduct.Declined') }}
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
