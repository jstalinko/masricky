<?php

namespace App\Http\Controllers\API;

use App\Helper;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
                'message' => 'Produk tidak tesedia'
            ], 200);
        }
        if($product->status == 'sold'){
            return response()->json([
                'success' => false,
                'message' => 'Produk sudah tidak tersedia / sold out'
            ], 200);
        }

        \Xendit\Configuration::setXenditKey(env('XENDIT_API_KEY'));
        $apiInstance = new \Xendit\Invoice\InvoiceApi();
        $invoice =  'INV' . time() . '' . $user->telegram_id;
        $create_invoice_request = new \Xendit\Invoice\CreateInvoiceRequest([
            'external_id' =>$invoice,
            'description' => $product->name,
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

    public function createTopup(Request $request)
    {
        $telegram_id = $request->telegram_id;
        $nominal =(int) $request->nominal;
        if($nominal < 100000)
        {
            return response()->json([
                'success' => false,
                'message' => 'Minimal topup adalah Rp. 100.000'
            ], 400);
        }

        $user = User::where('telegram_id', $telegram_id)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        \Xendit\Configuration::setXenditKey(env('XENDIT_API_KEY'));
        $apiInstance = new \Xendit\Invoice\InvoiceApi();
        $invoice =  'SLD-' .$user->telegram_id.'-'. substr(strtoupper(time() . $user->telegram_id.$nominal) , 6);
        $create_invoice_request = new \Xendit\Invoice\CreateInvoiceRequest([
            'external_id' =>$invoice,
            'description' => 'TOPUP SALDO Rp. ' . number_format($nominal,0,',','.'). ' User: ' . $user->name,
            'amount' => $nominal,
            'invoice_duration' => 172800,
            'currency' => 'IDR',
            'reminder_time' => 1,
        ]);

        try {

                

           
            $result = $apiInstance->createInvoice($create_invoice_request);
             $topupModel = new \App\Models\Topup();
            $topupModel->user_id = $user->id;
            $topupModel->amount = $nominal;
            $topupModel->fee = 0;
            $topupModel->total = $nominal;
            $topupModel->status = 'PENDING';
            $topupModel->invoice = $invoice;
            $topupModel->payment_id = $result['id'];
            $topupModel->payment_method = $result['payment_method'];
            $topupModel->save();

            return response()->json([
                'success' => true,
                'message' => 'Topup invoice created successfully',
                'data' => $result,
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Xendit\XenditSdkException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function createOrderBalance(Request $request)
    {
        $telegram_id = $request->telegram_id;
        $invoice_id = $request->invoice_id;

        $order = Order::where('invoice', $invoice_id)->first();
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
        $product = Product::find($order->product_id);
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

        $user = User::where('telegram_id', $telegram_id)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if($user->balance < $product->price){
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak mencukupi, silahkan lakukan top-up saldo'
            ], 200);
        }

        
        \Xendit\Configuration::setXenditKey(env('XENDIT_API_KEY'));
        $apiInstance = new \Xendit\Invoice\InvoiceApi();
        $apiInstance->expireInvoice($order->payment_id);
        
        $order->payment_method = 'BALANCE';
          $order->status = 'PAID';
            $order->save();
            // Kurangi saldo user
            $user->balance -= $product->price;
            $user->save();
            /** mutation */
            \App\Models\Mutation::updateMutationOut(
                $user->id,
                $product->price,
                'Pembelian produk ' . $product->name . ' menggunakan saldo',
                $user->balance
            );

            $settings = json_decode(file_get_contents(storage_path('app/settings.json')), true);
            // Update order berdasarkan status dari Xendit
            $content = $product->getFirstAvailableKey();

          

            if ($content === null) {
                $message = " Maaf,Pembayaran berhasil namun produk " . $product->name . " sudah habis terjual. Silakan hubungi admin untuk informasi lebih lanjut.";
                $message .= "Hubungi admin : " . $settings['admin_telegram_id'] . "\n";
            } else {
                $message = "----[ Pembayaran Berhasil ]----\n\n" .
                    "Invoice : " . $order->invoice . "\n" .
                    "Produk : " . $order->product->name . "\n" .
                    "Status : ✅ LUNAS\n" .
                    "Total   : Rp " . number_format($order->total, 0, ',', '.') . "\n\n" .
                    "--------------------------------\n\n" .
                    "📦 Produk :\n<pre>
           " . strip_tags($content) . "
           </pre>\n\n" .
                    "" . Helper::clean_description($order?->product?->category?->description) . "\n" .
                    "Terimakasih telah berbelanja di Bstore.ID 🙏";


                $product->markAsUsed($content);
                $avKey = (count($product->getAvailableKeys()));
                if ($avKey < 1 && !$product->unlimited_stock) {
                    $product->status = 'sold';
                    $product->active = false;
                    $product->save();
                }
            }
            $url = "https://api.telegram.org/bot" . $settings['telegram_bot_token'] .
                "/sendMessage?chat_id=" . $order->user->telegram_id .
                "&text=" . urlencode($message) .
                "&parse_mode=HTML";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            Log::info('Telegram response: ' . $response);

        return response()->json([
            'success' => true,
            'balance' => $user->balance,
            'data' => $order
        ], 200);
    }
    
}