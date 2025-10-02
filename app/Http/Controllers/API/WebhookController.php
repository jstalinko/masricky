<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

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

        // Cari order berdasarkan external_id (invoice)
        $order = Order::where('invoice', $payload['external_id'])->first();

        if (!$order) {
            Log::warning('Order not found for invoice: ' . $payload['external_id']);
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Update order berdasarkan status dari Xendit
        $order->update([
            'status'         => $payload['status'],          // contoh: PAID, SETTLED, EXPIRED
            'payment_id'     => $payload['id'] ?? null,      // ID dari Xendit
            'payment_method' => $payload['payment_method'] ?? null,
            'amount'         => $payload['amount'] ?? $order->amount,
            'fee'            => $payload['fees_paid_amount'] ?? $order->fee,
            'total'          => $payload['paid_amount'] ?? $order->total,
        ]);

        return response()->json(['message' => 'OK'], 200);
    }
}
