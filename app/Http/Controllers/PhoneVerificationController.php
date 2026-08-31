<?php

namespace App\Http\Controllers;

use App\Services\PhoneVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Send/verify a WhatsApp one-time code for a phone number. Deliberately
 * outside the 'auth' middleware group — Registration needs this before an
 * account exists — but still rate-limited both here (route middleware)
 * and per-phone (PhoneVerificationService), since it's an unauthenticated
 * endpoint that triggers an outbound message.
 */
class PhoneVerificationController extends Controller
{
    public function __construct(private readonly PhoneVerificationService $verification) {}

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $result = $this->verification->sendCode($data['phone']);

        return response()->json($result, $result['sent'] ? 200 : 422);
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $result = $this->verification->verifyCode($data['phone'], $data['code']);

        return response()->json($result, $result['verified'] ? 200 : 422);
    }
}
