<?php

namespace Modules\AuctionProducts\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuctionEntryAmountGatewayInfo extends Model
{
    use HasFactory;

    protected $table = 'auctions_entry_amount_gateway_infos';
    protected $guarded = [];


}
