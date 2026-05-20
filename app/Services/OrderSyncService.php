<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderSyncService
{
    public function syncOrderById(int $orderId): void
    {
        $baseUrl = rtrim(config('sync.base_url', ''), '/');

        if (!$baseUrl) {
            Log::debug('Order sync skipped: sync.base_url not configured', ['order_id' => $orderId]);
            return;
        }

        $order = Order::with([
            'customer',
            'customer.SellerAccount',
            'method',
            'order_payment.method',
            'address.getBillingCountry',
            'address.getBillingState',
            'address.getBillingCity',
            'address.getShippingCountry',
            'address.getShippingState',
            'address.getShippingCity',
            'shipping_address.getCountry',
            'shipping_address.getState',
            'shipping_address.getCity',
            'billing_address.getCountry',
            'billing_address.getState',
            'billing_address.getCity',
            'guest_info.getBillingCountry',
            'guest_info.getBillingState',
            'guest_info.getBillingCity',
            'guest_info.getShippingCountry',
            'guest_info.getShippingState',
            'guest_info.getShippingCity',
            'packages.seller.SellerAccount',
            'packages.products.seller_product_sku.product.product',
            'packages.products.seller_product_sku.sku',
            'packages.products.giftCard',
        ])->find($orderId);

        if (!$order) {
            return;
        }

        $payload = $this->buildPayload($order);

        try {
            Http::timeout(15)->acceptJson()->withHeaders([
                'X-Sync-Token'=> config('sync.token', env('SYNC_TOKEN','123456')),
            ])->post($baseUrl . '/api/sync/orders', $payload)->throw();

            Log::info('POS order sync success', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
        } catch (\Throwable $e) {
            Log::error('POS order sync failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function buildPayload(Order $order): array
    {
        $payment = $order->order_payment;
        $method = $order->method ?: optional($payment)->method;
        $methodSlug = optional($method)->slug;
        $methodName = optional($method)->name;
        $paymentIdentifier = $this->mapPaymentIdentifier($methodSlug);

        return [
            'external_order_id' => (string) $order->id,
            'order_number' => (string) $order->order_number,
            'created_at' => optional($order->created_at)->toDateTimeString(),
            'updated_at' => optional($order->updated_at)->toDateTimeString(),
            'is_paid' => (int) $order->is_paid === 1,
            'payment_status' => (int) $order->is_paid === 1 ? 'paid' : 'unpaid',
            'process_status' => $this->mapProcessStatus($order),
            'delivery_status' => $this->mapDeliveryStatus($order),
            'note' => $order->note,
            'totals' => [
                'sub_total' => (float) $order->sub_total,
                'discount_total' => (float) $order->discount_total,
                'shipping_total' => (float) $order->shipping_total,
                'tax_amount' => (float) $order->tax_amount,
                'grand_total' => (float) $order->grand_total,
            ],
            'payment' => [
                'id' => optional($payment)->id,
                'method_id' => $order->payment_type,
                'method_slug' => $methodSlug,
                'method_name' => $methodName,
                'identifier' => $paymentIdentifier,
                'amount' => (float) (optional($payment)->amount ?: $order->grand_total),
                'txn_id' => optional($payment)->txn_id,
                'status' => optional($payment)->status,
            ],
            'customer' => $this->mapCustomer($order),
            'billing_address' => $this->mapAddress($order, 'billing'),
            'shipping_address' => $this->mapAddress($order, 'shipping'),
            'packages' => $this->mapPackages($order),
            'products' => $this->mapProducts($order),
        ];
    }

    private function mapCustomer(Order $order): array
    {
        $customer = $order->customer;
        $billing = $this->mapAddress($order, 'billing');
        $shipping = $this->mapAddress($order, 'shipping');
        $name = trim((string) (optional($customer)->first_name . ' ' . optional($customer)->last_name));

        if ($name === '') {
            $name = $billing['name'] ?: ($shipping['name'] ?: 'Guest');
        }

        $parts = $this->splitName($name);

        return [
            'external_customer_id' => $order->customer_id ? (string) $order->customer_id : null,
            'buyer_pos_user_id' => optional($customer)->pos_user_id ?: optional($customer)->app_user_id,
            'buyer_vendor_code' => optional(optional($customer)->SellerAccount)->vendor_id,
            'name' => $name,
            'first_name' => $parts[0],
            'last_name' => $parts[1],
            'email' => $order->customer_email ?: (optional($customer)->email ?: ($billing['email'] ?: $shipping['email'])),
            'phone' => $order->customer_phone ?: (optional($customer)->phone ?: ($billing['phone'] ?: $shipping['phone'])),
        ];
    }

    private function mapAddress(Order $order, string $type): array
    {
        if ($order->address) {
            return [
                'name' => $order->address->{$type . '_name'},
                'email' => $order->address->{$type . '_email'},
                'phone' => $order->address->{$type . '_phone'},
                'address_1' => $order->address->{$type . '_address'},
                'address_2' => null,
                'country' => $this->locationName($order->address->{'get' . ucfirst($type) . 'Country'}),
                'state' => $this->locationName($order->address->{'get' . ucfirst($type) . 'State'}),
                'city' => $this->locationName($order->address->{'get' . ucfirst($type) . 'City'}),
                'postal_code' => $order->address->{$type . '_postcode'},
                'company' => null,
            ];
        }

        if (!$order->customer_id && $order->guest_info) {
            return [
                'name' => $order->guest_info->{$type . '_name'},
                'email' => $order->guest_info->{$type . '_email'},
                'phone' => $order->guest_info->{$type . '_phone'},
                'address_1' => $order->guest_info->{$type . '_address'},
                'address_2' => null,
                'country' => $this->locationName($order->guest_info->{'get' . ucfirst($type) . 'Country'}),
                'state' => $this->locationName($order->guest_info->{'get' . ucfirst($type) . 'State'}),
                'city' => $this->locationName($order->guest_info->{'get' . ucfirst($type) . 'City'}),
                'postal_code' => $order->guest_info->{$type . '_post_code'},
                'company' => null,
            ];
        }

        $address = $type === 'billing' ? $order->billing_address : $order->shipping_address;

        return [
            'name' => optional($address)->name,
            'email' => optional($address)->email,
            'phone' => optional($address)->phone,
            'address_1' => optional($address)->address,
            'address_2' => null,
            'country' => $this->locationName(optional($address)->getCountry),
            'state' => $this->locationName(optional($address)->getState),
            'city' => $this->locationName(optional($address)->getCity),
            'postal_code' => optional($address)->postal_code,
            'company' => null,
        ];
    }

    private function mapPackages(Order $order): array
    {
        return $order->packages->map(function ($package) {
            return [
                'id' => $package->id,
                'package_code' => $package->package_code,
                'seller_id' => $package->seller_id,
                'vendor_code' => optional(optional($package->seller)->SellerAccount)->vendor_id,
                'shipping_cost' => (float) $package->shipping_cost,
                'tax_amount' => (float) $package->tax_amount,
                'delivery_status' => $package->delivery_status,
                'is_paid' => (int) $package->is_paid === 1,
            ];
        })->values()->all();
    }

    private function mapProducts(Order $order): array
    {
        $products = [];

        foreach ($order->packages as $package) {
            foreach ($package->products as $detail) {
                $sellerSku = $detail->seller_product_sku;
                $sellerProduct = optional($sellerSku)->product;
                $mainProduct = optional($sellerProduct)->product;
                $sku = optional($sellerSku)->sku;
                $qty = (float) $detail->qty;
                $totalWithoutTax = (float) $detail->total_price;
                $taxValue = (float) $detail->tax_amount;

                $products[] = [
                    'id' => $detail->id,
                    'package_id' => $package->id,
                    'type' => $detail->type,
                    'name' => $this->productName($detail, $sellerProduct, $mainProduct),
                    'sku' => optional($sku)->sku,
                    'external_product_id' => optional($mainProduct)->id,
                    'pos_product_id' => optional($mainProduct)->external_product_id,
                    'shop_product_id' => optional($mainProduct)->id,
                    'shop_seller_product_id' => optional($sellerProduct)->id,
                    'shop_seller_product_sku_id' => optional($sellerSku)->id,
                    'seller_id' => $package->seller_id,
                    'vendor_code' => optional(optional($package->seller)->SellerAccount)->vendor_id,
                    'quantity' => $qty,
                    'unit_price' => (float) $detail->price,
                    'total_price_without_tax' => $totalWithoutTax,
                    'tax_value' => $taxValue,
                    'total_price_with_tax' => $totalWithoutTax + $taxValue,
                    'discount' => 0,
                ];
            }
        }

        return $products;
    }

    private function productName($detail, $sellerProduct, $mainProduct): string
    {
        if ($detail->type !== 'product') {
            return (string) (optional($detail->giftCard)->name ?: 'Gift Card');
        }

        return (string) (optional($sellerProduct)->product_name ?: (optional($mainProduct)->product_name ?: 'Online Product'));
    }

    private function mapPaymentIdentifier(?string $slug): string
    {
        if ($slug === 'cash-on-delivery') {
            return 'cash-payment';
        }

        if ($slug === 'wallet') {
            return 'account-payment';
        }

        if ($slug === 'bank-payment') {
            return 'check-payment';
        }

        if (in_array($slug, ['gift-card', 'gift_card'], true)) {
            return 'giftcard-payment';
        }

        return 'card-payment';
    }

    private function mapProcessStatus(Order $order): string
    {
        if ((int) $order->is_cancelled === 1) {
            return 'failed';
        }

        if ((int) $order->is_completed === 1) {
            return 'ready';
        }

        if ((int) $order->is_confirmed === 1) {
            return 'ongoing';
        }

        return 'pending';
    }

    private function mapDeliveryStatus(Order $order): string
    {
        $statuses = $order->packages->pluck('delivery_status')->map(fn($status) => (int) $status);

        if ($statuses->isNotEmpty() && $statuses->min() >= 5) {
            return 'delivered';
        }

        if ($statuses->contains(fn($status) => $status >= 2)) {
            return 'ongoing';
        }

        return 'pending';
    }

    private function locationName($location): ?string
    {
        return optional($location)->name ?: optional($location)->title;
    }

    private function splitName(?string $name): array
    {
        $name = trim((string) $name);

        if ($name === '') {
            return ['Guest', null];
        }

        $parts = preg_split('/\s+/', $name);
        $first = array_shift($parts);

        return [$first, count($parts) ? implode(' ', $parts) : null];
    }
}
