<?php

namespace App\Http\Controllers\Api;

use App\Jobs\ProcessMidtransWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MidtransWebhookController extends BaseApiController
{
    #[OA\Post(
        path: '/api/v1/webhooks/midtrans',
        summary: 'Midtrans webhook notification handler (called by Midtrans server)',
        tags: ['Payment'],
        responses: [
            new OA\Response(response: 200, description: 'Notification received'),
            new OA\Response(response: 401, description: 'Invalid signature'),
        ]
    )]
    public function notify(Request $request): JsonResponse
    {
        $payload = $request->all();

        // ── Verify Signature ──────────────────────────────────────────────────
        // Formula: SHA512(order_id + status_code + gross_amount + server_key)
        $orderId     = $payload['order_id']     ?? '';
        $statusCode  = $payload['status_code']  ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey   = config('payment.midtrans.server_key');

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        $incomingSignature = $payload['signature_key'] ?? '';

        if (! hash_equals($expectedSignature, $incomingSignature)) {
            return $this->errorResponse('Invalid signature.', 401);
        }
        // ─────────────────────────────────────────────────────────────────────

        // Dispatch job async — response ke Midtrans harus cepat (< 5 detik)
        ProcessMidtransWebhook::dispatch($payload);

        return $this->messageResponse('OK');
    }
}
