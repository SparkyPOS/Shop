<?php

namespace Modules\AuctionProducts\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\PaymentGateway\Entities\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\AuctionProducts\Entities\AuctionEntryAmountGatewayInfo;

class AuctionEntryAmountPayment extends Model
{
    use HasFactory;

    protected $table = 'auctions_entry_amount_payments';

    protected $guarded = [];


    public function customer()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function auction()
    {
        return $this->belongsTo(Auction::class,'auction_id');
    }


    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class,'payment_method');
    }
    


    public function paymentInfo()
    {
        return $this->hasOne(AuctionEntryAmountGatewayInfo::class,'entry_amount_payment_id');
    }


}
