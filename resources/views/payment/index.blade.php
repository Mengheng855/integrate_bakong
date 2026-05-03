<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay with Bakong</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md">

    <h1 class="text-2xl font-bold text-center text-red-600 mb-6">🏦 Bakong KHQR Payment</h1>

    @if(!isset($payment))

        {{-- Step 1: Enter Amount --}}
        <form method="POST" action="{{ route('payment.generate') }}">
            @csrf

            @if($errors->any())
                <div class="bg-red-100 text-red-700 rounded-lg p-3 mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                <input
                    type="number"
                    name="amount"
                    step="0.01"
                    min="0.01"
                    value="{{ old('amount') }}"
                    placeholder="0.00"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-400"
                    required
                />
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                <select
                    name="currency"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-400"
                >
                    <option value="USD">USD ($)</option>
                    <option value="KHR">KHR (៛)</option>
                </select>
            </div>

            <button
                type="submit"
                class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-lg transition"
            >
                Generate QR Code
            </button>
        </form>

    @else

        {{-- Step 2: Show QR Code --}}
        <div class="text-center">

            <p class="text-gray-500 text-sm mb-1">Ref: {{ $payment->reference }}</p>
            <p class="text-xl font-bold text-gray-800 mb-4">
                {{ $payment->amount }} {{ $payment->currency }}
            </p>

            <div id="qrcode" class="flex justify-center mb-4"></div>

            <div id="status-box" class="rounded-lg px-4 py-3 text-sm font-medium bg-yellow-100 text-yellow-800 mb-4">
                ⏳ Waiting for payment...
            </div>

            <input type="hidden" id="md5" value="{{ $payment->md5 }}" />

            <a href="{{ route('products.index') }}" class="text-sm text-red-500 underline hover:text-red-700">
                ← Start over
            </a>
        </div>

        <script>
            new QRCode(document.getElementById("qrcode"), {
                text: "{{ $payment->qr }}",
                width: 220,
                height: 220,
            });

            const md5 = document.getElementById('md5').value;
            const statusBox = document.getElementById('status-box');
            let pollCount = 0;
            const maxPolls = 200;

            const poll = setInterval(async () => {
                pollCount++;

                if (pollCount >= maxPolls) {
                    clearInterval(poll);
                    statusBox.className = 'rounded-lg px-4 py-3 text-sm font-medium bg-red-100 text-red-700 mb-4';
                    statusBox.textContent = '❌ QR expired. Please generate a new one.';
                    return;
                }

                const res = await fetch("{{ route('payment.check') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ md5 })
                });

                if (!res.ok) {
                    throw new Error(`Payment check failed with HTTP ${res.status}`);
                }

                const json = await res.json();

                if (json.paid) {
                    clearInterval(poll);
                    statusBox.className = 'rounded-lg px-4 py-3 text-sm font-medium bg-green-100 text-green-700 mb-4';
                    statusBox.textContent = '✅ Payment confirmed!';

                    setTimeout(() => {
                        window.location.href = json.success_url;
                    }, 1500);
                }

            }, 3000);
        </script>

    @endif

</div>

</body>
</html>
