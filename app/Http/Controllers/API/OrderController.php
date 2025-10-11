<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Nekoding\Tripay\Tripay;
use Illuminate\Http\Request;
use Nekoding\Tripay\Signature;
use App\Http\Controllers\Controller;
use Nekoding\Tripay\Networks\HttpClient;

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
                'message' => 'Produk tidak tesedia'
            ], 200);
        }
        if($product->status == 'sold'){
            return response()->json([
                'success' => false,
                'message' => 'Produk sudah tidak tersedia / sold out'
            ], 200);
        }

        $invoice = "BINV".time().$user->id.rand(10,99);
        $data = [
    'method'         => 'QRIS',
    'merchant_ref'   => $invoice,
    'amount'         => $product->price,
    'customer_name'  => $user->name ?? 'Customer '.$request->telegram_id,
    'customer_email' => $user->email ?? 'user'.$request->telegram_id.'@bstore.id',
    'customer_phone' => $user->telegram_id,
    'order_items'    => [
        [
            'sku'         => $product->slug,
            'name'        => $product->name,
            'price'       => $product->price,
            'quantity'    => 1,
        ]
    ],
    'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
    'signature'    => Signature::generate($invoice.$product->price)
];
$tripay = new Tripay(new HttpClient(env('TRIPAY_API_KEY')));
$response = $tripay->createTransaction($data, Tripay::CLOSE_TRANSACTION)->getResponse();
if($response['success'])
{

    $order = new Order();
    $order->user_id = $user->id;
    $order->product_id = $product->id;
    $order->amount = $product->price;
    $order->quantity = 1;
    $order->fee = $response['data']['total_fee'];
    $order->total = $response['data']['amount'];
    $order->status = 'PENDING';
    $order->invoice = $invoice;
    $order->payment_id = $response['data']['reference'];
    $order->payment_method ='QRIS';
    $order->save();
    $res['success'] = true;
}else{
    $res['success'] = false;
}
 
$res['data'] = $response['data'];
$res['product'] = $product;
$res['user'] = $user;

return response()->json($res,200,[],JSON_PRETTY_PRINT);

     
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
