<?php

namespace Modules\AuctionProducts\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Seller\Entities\SellerProduct;

class Auction extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function seller_product(){
        return $this->belongsTo(SellerProduct::class, 'seller_product_id');
    }
    public function seller(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function auction_bid(){
        return $this->hasMany(AuctionBid::class,'auction_id');
    }
}
