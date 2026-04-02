<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan to Pay</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-sm text-center">

    <h1 class="text-xl font-bold text-gray-800 mb-1">Scan to Pay</h1>
    <p class="text-gray-400 text-sm mb-4">Ref: {{ $payment->reference }}</p>

    {{-- Product Info --}}
    <div class="bg-gray-50 rounded-xl p-4 mb-4">
        <div class="text-5xl mb-2">{{ $product->image }}</div>
        <p class="font-semibold text-gray-700">{{ $product->name }}</p>
        <p class="text-2xl font-bold text-red-600 mt-1">
            ${{ number_format($payment->amount, 2) }}
        </p>
    </div>

    {{-- QR Code --}}
    <div id="qrcode" class="flex justify-center mb-4"></div>

    {{-- Countdown Timer --}}
    <p class="text-gray-400 text-xs mb-2">QR expires in <span id="countdown" class="font-bold text-gray-600">10:00</span></p>

    {{-- Status --}}
    <div id="status-box" class="rounded-lg px-4 py-3 text-sm font-medium bg-yellow-100 text-yellow-800 mb-4">
        ⏳ Waiting for payment...
    </div>

    <input type="hidden" id="md5" value="{{ $payment->md5 }}" />

    <a href="{{ route('products.index') }}" class="text-sm text-red-500 underline hover:text-red-700">
        ← Back to products
    </a>
</div>

<script>
    // Render QR
    new QRCode(document.getElementById("qrcode"), {
        text: "{{ $payment->qr }}",
        width: 220,
        height: 220,
    });

    const md5 = document.getElementById('md5').value;
    const statusBox = document.getElementById('status-box');
    const countdownEl = document.getElementById('countdown');

    // Countdown 10 minutes = 600 seconds
    let timeLeft = 600;
    const countdownTimer = setInterval(() => {
        timeLeft--;
        const mins = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const secs = (timeLeft % 60).toString().padStart(2, '0');
        countdownEl.textContent = `${mins}:${secs}`;

        if (timeLeft <= 0) {
            clearInterval(countdownTimer);
            clearInterval(poll);
            statusBox.className = 'rounded-lg px-4 py-3 text-sm font-medium bg-red-100 text-red-700 mb-4';
            statusBox.textContent = '❌ QR expired. Please go back and try again.';
        }
    }, 1000);

    // Poll every 5 seconds
    const poll = setInterval(async () => {
        try {
            const res = await fetch("{{ route('payment.check') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ md5 })
            });

            const json = await res.json();
            console.log('Poll result:', json); // debug in browser console

            if (json.paid) {
                clearInterval(poll);
                clearInterval(countdownTimer);
                statusBox.className = 'rounded-lg px-4 py-3 text-sm font-medium bg-green-100 text-green-700 mb-4';
                statusBox.textContent = '✅ Payment confirmed!';

                setTimeout(() => {
                    window.location.href = `/payment/${json.reference}/success`;
                }, 1500);
            }

        } catch (e) {
            console.error('Poll error:', e);
        }

    }, 5000); // poll every 5 seconds
</script>

</body>
</html>