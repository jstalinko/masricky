<?php

namespace App\Http\Controllers\API;

use App\Helper;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class WebhookController extends Controller
{
    public function webhookInvoice(Request $request)
    {
        // Ambil data JSON dari body request
        $json = $request->getContent();

        Log::info('callback_tripay : ' . $json);

        // Ambil callback signature dari header
        $callbackSignature = $request->header('X-Callback-Signature', '');

        // Private key Tripay kamu
        $privateKey = env('TRIPAY_PRIVATE_KEY');

        // Generate signature untuk verifikasi
        $signature = hash_hmac('sha256', $json, $privateKey);

        // Validasi signature
        if ($callbackSignature !== $signature) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 401);
        }

        // Decode JSON
        $data = json_decode($json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data sent by payment gateway',
            ], 400);
        }

        // Validasi event callback
        if ($request->header('X-Callback-Event') !== 'payment_status') {
            return response()->json([
                'success' => false,
                'message' => 'Unrecognized callback event: ' . $request->header('X-Callback-Event'),
            ], 400);
        }

        // Ambil data penting
        $invoiceId = $data->merchant_ref ?? null;
        $tripayReference = $data->reference ?? null;
        $status = strtoupper((string) ($data->status ?? ''));
        $isClosedPayment = $data->is_closed_payment ?? 0;

        if ($isClosedPayment == 1) {
            $invoice = Order::where('invoice', $invoiceId)->where('status', '!=', 'PAID')->first();

            if (! $invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found or already paid: ' . $invoiceId,
                ], 404);
            }
            $invoice->payment_id = $tripayReference;
            $invoice->payment_method = $data->payment_method_code;
            $invoice->amount = $data->amount_received;
            $invoice->fee = $data->total_fee;
            $invoice->total = $data->total_amount;
            $invoice->status = $status;
            $invoice->save();
            if ($invoice->status == 'PAID') {
                $product = Product::find($invoice->product_id);
                $settings = json_decode(file_get_contents(storage_path('app/settings.json')), true);

                $content = $product->getFirstAvailableKey();
                if ($content === null) {
                    $message = " Maaf,Pembayaran berhasil namun produk " . $product->name . " sudah habis terjual. Silakan hubungi admin untuk informasi lebih lanjut.";
                    $message .= "Hubungi admin : " . $settings['admin_telegram_id'] . "\n";
                } else {
                    $message = "----[ Pembayaran Berhasil ]----\n\n" .
                        "Invoice : " . $invoice->invoice . "\n" .
                        "Produk : " . $invoice->product->name . "\n" .
                        "Status : ✅ LUNAS\n" .
                        "Total   : Rp " . number_format($invoice->total, 0, ',', '.') . "\n\n" .
                        "--------------------------------\n\n" .
                        "📦 Produk :\n\n<pre>" . strip_tags($content) . "</pre>\n\n" .
                         Helper::clean_description($invoice?->product?->category?->description) . "\n" .
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
                    "/sendMessage?chat_id=" . $invoice->user->telegram_id .
                    "&text=" . urlencode($message) .
                    "&parse_mode=HTML";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
            }


            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid payment type (open payment not supported)',
        ], 400);
    }
}
