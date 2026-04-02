<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Success</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md text-center">
    <div class="text-6xl mb-4">✅</div>
    <h1 class="text-2xl font-bold text-green-600 mb-2">Payment Successful!</h1>
    <p class="text-gray-500 text-sm mb-6">Thank you for your payment.</p>

    <div class="bg-gray-50 rounded-lg p-4 text-left text-sm space-y-2 mb-6">
        <div class="flex justify-between">
            <span class="text-gray-500">Reference</span>
            <span class="font-medium">{{ $payment->reference }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Amount</span>
            <span class="font-medium">{{ $payment->amount }} {{ $payment->currency }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Paid by</span>
            <span class="font-medium">{{ $payment->payer_account ?? 'N/A' }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Paid at</span>
            <span class="font-medium">{{ $payment->paid_at->format('d M Y, H:i') }}</span>
        </div>
    </div>

    <a href="{{ route('products.index') }}"
       class="block w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-lg transition">
        Make Another Payment
    </a>
</div>

</body>
</html>
