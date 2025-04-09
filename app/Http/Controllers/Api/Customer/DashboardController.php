<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Invoice;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Display dashboard statistics for the authenticated customer.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // get authenticated customer
        $customer = auth()->guard('api_customer')->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        // count invoices by status for the current customer
        $statuses = ['pending', 'success', 'expired', 'failed'];
        $counts = [];

        foreach ($statuses as $status) {
            $counts[$status] = Invoice::where('status', $status)
                ->where('customer_id', $customer->id)
                ->count();
        }

        // return response
        return response()->json([
            'success' => true,
            'message' => 'Statistik Data',
            'data' => [
                'count' => $counts,
            ],
        ], 200);
    }
}
