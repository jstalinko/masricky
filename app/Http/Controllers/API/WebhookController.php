<?php

namespace App\Http\Controllers\API;

use App\Helper;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class WebhookController extends Controller
{
    public function webhookInvoice(Request $request)
    {
        $payload = $request->all();

        // Log data webhook (buat debug)
        Log::info('Xendit Webhook Invoice:', $payload);

        if (!isset($payload['external_id']) || !isset($payload['status'])) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        if(preg_match('/SLD/', $payload['external_id'])) {
            // Handle top-up webhook
            Log::info('Received top-up webhook for invoice: ' . $payload['external_id']);
            // Implement top-up handling logic here
            $topupModel =  \App\Models\Topup::where('invoice', $payload['external_id'])->first();
            if (!$topupModel) {
                Log::warning('Top-up not found for invoice: ' . $payload['external_id']);
                return response()->json(['message' => 'Top-up not found'], 404);
            }
            $nominal = $topupModel->amount;
            $telegram_id = $topupModel->user->telegram_id;


            $user = \App\Models\User::find($topupModel->user_id);
            if (!$user) {
                Log::warning('User not found for telegram_id: ' . $telegram_id);
                return response()->json(['message' => 'User not found'], 404);
            }

            $user->balance += $nominal;
            $user->save();
            $topupModel->status = 'PAID';
            $topupModel->payment_id = $payload['id'] ?? null;
            $topupModel->payment_method = $payload['payment_method'] ?? null;
            $topupModel->total = $payload['paid_amount'] ?? $topupModel->total;
            $topupModel->save();


            /** mutation */
            \App\Models\Mutation::updateMutationIn(
                $user->id,
                $nominal,
                'Top-up saldo via ' . ($payload['payment_method'] ?? 'Unknown'),
                $user->balance
            );
            
            Log::info('Top-up successful for user: ' . $telegram_id . ' Amount: ' . $nominal);

            return response()->json(['message' => 'Top-up webhook received'], 200);
        }



        // Cari order berdasarkan external_id (invoice)
        $order = Order::where('invoice', $payload['external_id'])->with('product')->with('user')->first();

        if (!$order) {
            Log::warning('Order not found for invoice: ' . $payload['external_id']);
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($payload['status'] === 'PAID') {

            $product = Product::find($order->product_id);

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


                      $order->update([
                'status'         => $payload['status'],          // contoh: PAID, SETTLED, EXPIRED
                'payment_id'     => $payload['id'] ?? null,      // ID dari Xendit
                'payment_method' => $payload['payment_method'] ?? null,
                'amount'         => $payload['amount'] ?? $order->amount,
                'fee'            => $payload['fees_paid_amount'] ?? $order->fee,
                'total'          => $payload['paid_amount'] ?? $order->total,
                'product_content' =>  $content
            ]);
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
        }




        return response()->json(['message' => 'OK'], 200);
    }
}