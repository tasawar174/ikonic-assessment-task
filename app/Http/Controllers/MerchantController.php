<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MerchantController extends Controller
{
    public function __construct(MerchantService $merchantService)
    {
        //
    }

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $orders = Order::whereBetween('created_at', [$fromDate, $toDate])->get();
        $orderCount = $orders->count();
        $commissionOwed = $orders->filter(function ($order) {
            return $order->affiliate_id !== null && $order->payout_status == 'unpaid';
        })->sum('commission_owed');
        $revenue = $orders->sum('subtotal');
        $data = [
            'count' => $orderCount,
            'commissions_owed' => round($commissionOwed, 2),
            'revenue' => $revenue,
        ];
        return response()->json($data);
    }
}
