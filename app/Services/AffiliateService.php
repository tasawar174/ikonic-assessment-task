<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(protected ApiService $apiService)
    {
        //
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // Check if the email is already used by a merchant
        if (Merchant::join('users', 'users.id', '=', 'merchants.user_id')->where('users.type', User::TYPE_MERCHANT)->where('users.email', $email)->exists()) {
            throw new AffiliateCreateException('Email is already in use as a merchant.');
        }
        // Check if the email is already used by an affiliate
        if (Affiliate::join('users', 'users.id', '=', 'affiliates.user_id')->where('users.email', $email)->exists()) {
            throw new AffiliateCreateException('Email is already in use as an affiliate.');
        }

        $affiliate = new Affiliate();
        $affiliate->user_id = $merchant->user_id;
        $affiliate->merchant_id = $merchant->id;
        $affiliate->commission_rate = $commissionRate;
        $affiliate->discount_code = $this->apiService->createDiscountCode($merchant)['code'];;
        $affiliate->save();
        //Mail::send(new AffiliateCreated($affiliate));
        return $affiliate;
    }
}
