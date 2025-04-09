<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{    
    /**
     * Simpan review dari customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'rating'     => 'required|integer|min:1|max:5',
            'review'     => 'nullable|string',
            'product_id' => 'required|exists:products,id',
            'order_id'   => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Cek apakah sudah pernah mereview produk dalam order ini
        $check_review = Review::where('order_id', $request->order_id)
                              ->where('product_id', $request->product_id)
                              ->first();

        if ($check_review) {
            return response()->json([
                'success' => false,
                'message' => 'Review sudah pernah diberikan untuk produk ini di pesanan tersebut.',
                'data'    => $check_review,
            ], 409);
        }

        // Simpan review
        $review = Review::create([
            'rating'      => $request->rating,
            'review'      => $request->review,
            'product_id'  => $request->product_id,
            'order_id'    => $request->order_id,
            'customer_id' => auth()->guard('api_customer')->user()->id
        ]);

        if ($review) {
            return new ReviewResource(true, 'Review berhasil disimpan!', $review);
        }

        return new ReviewResource(false, 'Review gagal disimpan!', null);
    }
}
