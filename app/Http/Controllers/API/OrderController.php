<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function getOrderHistory(Request $request)
    {
        $telegram_id = $request->telegram_id;
        if (!$telegram_id) {
            return response()->json([
                'success' => false,
                'message' => 'Missing telegram_id'
            ], 400);
        }

        $user = User::where('telegram_id', $telegram_id)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $orders = Order::where('user_id', $user->id)->with('product')->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ], 200);
    }

    public function createOrder(Request $request)
    {
        $user = User::where('telegram_id', $request->telegram_id)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        \Xendit\Configuration::setXenditKey(env('XENDIT_API_KEY'));
        $apiInstance = new \Xendit\Invoice\InvoiceApi();
        $invoice =  'INV' . time() . '' . $user->telegram_id;
        $create_invoice_request = new \Xendit\Invoice\CreateInvoiceRequest([
            'external_id' =>$invoice,
            'description' => 'Order product ',
            'amount' => $product->price,
            'invoice_duration' => 172800,
            'currency' => 'IDR',
            'reminder_time' => 1,
        ]);
        $for_user_id = "6065d8a4da970440a3bc747c";

        try {
            $result = $apiInstance->createInvoice($create_invoice_request);
            $order = new Order();
            $order->user_id = $user->id;
            $order->product_id = $product->id;
            $order->amount = $product->price;
            $order->quantity = 1;
            $order->fee = 0;
            $order->total = $product->price;
            $order->status = 'PENDING';
            $order->invoice = $invoice;
            $order->payment_id = $result['id'];
            $order->payment_method = $result['payment_method'];
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => $result,
                'product' => $product,
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Xendit\XenditSdkException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),

            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function cancelOrder(Request $request)
    {
        $invoice_id = $request->invoice_id;
        if (!$invoice_id) {
            return response()->json([
                'success' => false,
                'message' => 'Missing invoice_id'
            ], 400);
        }

        \Xendit\Configuration::setXenditKey(env('XENDIT_API_KEY'));
        $apiInstance = new \Xendit\Invoice\InvoiceApi();

        try {
            $order = Order::where('invoice', $invoice_id)->where('status','!=','paid')->first();
            if ($order) {
                $order->status = 'CANCELLED';
                $order->save();
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404, [], JSON_PRETTY_PRINT);
            }
            $result = $apiInstance->expireInvoice($order->payment_id);


            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled successfully',
                'data' => $result
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Xendit\XenditSdkException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function getOrderByInvoice(Request $request)
    {
        $invoice_id = $request->invoice_id;
        if (!$invoice_id) {
            return response()->json([
                'success' => false,
                'message' => 'Missing invoice_id'
            ], 400);
        }

        $order = Order::where('invoice', $invoice_id)->with('product')->first();
        if ($order) {
            return response()->json([
                'success' => true,
                'data' => $order
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
    }

    
}
