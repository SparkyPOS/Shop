<?php

namespace Modules\AuctionProducts\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuctionBid extends Model
{
    use HasFactory;

    protected $fillable = ['auction_id', 'bid_amount', 'user_id','is_send','confirm_order','cancel_order'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    
     public function auction()
    {
        return $this->belongsTo(Auction::class,'auction_id');
    }
}
