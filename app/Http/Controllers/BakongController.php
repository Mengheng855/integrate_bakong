<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;

class BakongController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    public function generate(Request $request, Product $product)
    {
        $accountId    = config('bakong.account_id');
        $merchantName = config('bakong.merchant_name');
        $merchantCity = config('bakong.merchant_city');

        if (!$accountId || !$merchantName || !$merchantCity) {
            return back()->with('error', 'Bakong config is missing. Please check your .env file.');
        }

        $currencyCode = $product->currency === 'KHR'
            ? KHQRData::CURRENCY_KHR
            : KHQRData::CURRENCY_USD;

        
        $expirationTimestamp = (time() + 600) * 1000;

        $info = new IndividualInfo(
            bakongAccountID: $accountId,
            merchantName: $merchantName,
            merchantCity: $merchantCity,
            currency: $currencyCode,
            amount: (float) $product->price,
            expirationTimestamp: $expirationTimestamp
        );

        $result = BakongKHQR::generateIndividual($info);

        \Log::info('Generate result: ' . json_encode($result));

        $qr  = null;
        $md5 = null;

        if (is_array($result->data)) {
            $qr  = $result->data['qr']  ?? null;
            $md5 = $result->data['md5'] ?? null;
        } elseif (is_object($result->data)) {
            $qr  = $result->data->qr  ?? null;
            $md5 = $result->data->md5 ?? null;
        }

        if (!$qr || !$md5) {
            return back()->with('error', 'Failed to generate QR: ' . json_encode($result));
        }

        $payment = Payment::create([
            'product_id' => $product->id,
            'reference'  => 'PAY-' . strtoupper(Str::random(10)),
            'md5'        => $md5,
            'qr'         => $qr,
            'amount'     => (float) $product->price,
            'currency'   => (string) $product->currency,
            'status'     => 'pending',
        ]);

        return view('payment.show', compact('payment', 'product'));
    }

    public function check(Request $request)
    {
        $request->validate(['md5' => 'required|string']);

        $payment = Payment::where('md5', $request->md5)->first();

        if (!$payment) {
            return response()->json(['paid' => false, 'message' => 'Payment not found.']);
        }

        if ($payment->status === 'paid') {
            return response()->json([
                'paid'      => true,
                'message'   => 'Payment confirmed!',
                'reference' => $payment->reference,
                'success_url' => route('payment.success', ['payment' => $payment->reference]),
            ]);
        }

        try {
            // New package — instantiate with token
            $bakong = new BakongKHQR(config('bakong.token'));
            $result = $bakong->checkTransactionByMD5($request->md5);

            \Log::info('Check result: ' . json_encode($result));

            $paid = isset($result['responseCode']) && $result['responseCode'] === 0;

            if ($paid) {
                $payment->update([
                    'status'        => 'paid',
                    'payer_account' => $result['data']['fromAccountId'] ?? null,
                    'paid_at'       => now(),
                ]);
            }

            return response()->json([
                'paid'      => $paid,
                'message'   => $paid ? 'Payment confirmed!' : 'Waiting...',
                'reference' => $payment->reference,
                'success_url' => route('payment.success', ['payment' => $payment->reference]),
            ]);
        } catch (\Exception $e) {
            \Log::error('Bakong error: ' . $e->getMessage());
            return response()->json([
                'paid'    => false,
                'message' => 'Checking...',
            ]);
        }
    }

    public function success(Payment $payment)
    {
        return view('payment.success', compact('payment'));
    }
}
