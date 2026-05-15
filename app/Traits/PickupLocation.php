<?php


namespace App\Traits;


use App\Models\User;

trait PickupLocation
{

    public static function pickupPoint($seller_id)
    {
        $location = \Modules\Shipping\Entities\PickupLocation::where('created_by', $seller_id)
            ->where('status', 1)
            ->where('is_default', 1)
            ->first();

        if (!$location) {
            $location = \Modules\Shipping\Entities\PickupLocation::where('created_by', $seller_id)
                ->where('status', 1)
                ->first();
        }

        if (!$location) {
            $location = \Modules\Shipping\Entities\PickupLocation::where('status', 1)
                ->where('is_set', 1)
                ->first();
        }

        if (!$location) {
            $location = \Modules\Shipping\Entities\PickupLocation::where('status', 1)
                ->where('is_default', 1)
                ->first();
        }

        if (!$location) {
            $location = \Modules\Shipping\Entities\PickupLocation::where('status', 1)->first();
        }

        return $location ? $location->id : null;

    }

    public static function pickupPointAddress($seller_id)
    {
        $location = \Modules\Shipping\Entities\PickupLocation::where('created_by', $seller_id)
            ->where('status', 1)
            ->where('is_default', 1)
            ->first();

        if (!$location) {
            $location = \Modules\Shipping\Entities\PickupLocation::where('created_by', $seller_id)
                ->where('status', 1)
                ->first();
        }

        if (!$location) {
            $location = \Modules\Shipping\Entities\PickupLocation::where('status', 1)
                ->where('is_set', 1)
                ->first();
        }

        if (!$location) {
            $location = \Modules\Shipping\Entities\PickupLocation::where('status', 1)
                ->where('is_default', 1)
                ->first();
        }

        if (!$location) {
            $location = \Modules\Shipping\Entities\PickupLocation::where('status', 1)->first();
        }

        return $location;

    }


}
