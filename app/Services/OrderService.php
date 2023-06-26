<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        if ($data['order_id'] != '' && $data['customer_email'] != '' && $data['customer_name'] != '') {
            return;
        }
        $user = User::where('email', $data['customer_email'])->first();
        if (!$user) {
            return;
        }
        $affiliate  =   $user->affiliate;
        if (empty($affiliate)) {
            $user = new User();
            $user->name = $data['customer_name'];
            $user->email = $data['customer_email'];
            $user->type = User::TYPE_AFFILIATE;
            $user->save();
            $merchant = new Merchant();
            $merchant->user_id = $user->id ?? '0';
            $merchant->domain = $data['domain'];
            $merchant->display_name = $data['name'];
            $merchant->save();
            $affiliate  =   $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], '');
        }
        if (Order::where('id', $data['order_id'])->exists()) {
            return;
        }
        $order = new Order();
        $order->merchant_id = $affiliate->merchant_id;
        $order->affiliate_id = $affiliate->id;
        $order->subtotal = round($data['subtotal_price'], 2);
        $order->payout_status = Order::STATUS_PAID;
        $order->discount_code = $data['discount_code'];
        $order->external_order_id = $data['order_id'];
        $order->affiliate()->associate($affiliate);
        $order->save();
    }
}
