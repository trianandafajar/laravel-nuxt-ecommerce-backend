<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Invoice;
use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the invoices for the authenticated customer.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $customer = auth()->guard('api_customer')->user();

        $invoices = Invoice::latest()
            ->when($request->q, function ($query) use ($request) {
                return $query->where('invoice', 'like', '%' . $request->q . '%');
            })
            ->where('customer_id', $customer->id)
            ->paginate(5);

        return new InvoiceResource(true, 'List Data Invoices : ' . $customer->name, $invoices);
    }

    /**
     * Display the specified invoice details.
     *
     * @param  string  $snap_token
     * @return \Illuminate\Http\Response
     */
    public function show($snap_token)
    {
        $customer = auth()->guard('api_customer')->user();

        $invoice = Invoice::with(['orders.product', 'customer', 'city', 'province'])
            ->where('customer_id', $customer->id)
            ->where('snap_token', $snap_token)
            ->first();

        if ($invoice) {
            return new InvoiceResource(true, 'Detail Data Invoice : ' . $invoice->snap_token, $invoice);
        }

        return new InvoiceResource(false, 'Detail Data Invoice Tidak Ditemukan!', null);
    }
}
