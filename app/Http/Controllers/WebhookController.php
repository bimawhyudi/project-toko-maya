<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(private MidtransService $midtrans) {}

    /**
     * Webhook Midtrans (tanpa CSRF). Verifikasi signature lalu terapkan
     * status: lunas -> diproses + potong stok (idempoten), kedaluwarsa ->
     * batal. Selalu balas 200 supaya Midtrans tidak mengulang terus.
     */
    public function terima(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (! $this->midtrans->signatureValid($payload)) {
            return response()->json(['pesan' => 'signature tidak valid'], 403);
        }

        $this->midtrans->terapkanStatus($payload);

        return response()->json(['pesan' => 'ok']);
    }
}
