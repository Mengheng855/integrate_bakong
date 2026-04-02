<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">

<h1 class="text-2xl font-bold text-center text-gray-800 mb-8">🛒 Select a Product to Pay</h1>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
    @foreach($products as $product)
    <div class="bg-white rounded-2xl shadow p-6 text-center hover:shadow-lg transition">

        <div class="text-6xl mb-3">{{ $product->image }}</div>
        <h2 class="text-lg font-bold text-gray-800">{{ $product->name }}</h2>
        <p class="text-gray-400 text-sm mb-3">{{ $product->description }}</p>
        <p class="text-2xl font-bold text-red-600 mb-4">
            ${{ number_format($product->price, 2) }}
        </p>

        <form method="POST" action="{{ route('payment.generate', $product) }}">
            @csrf
            <button
                type="submit"
                class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-xl transition"
            >
                Pay with Bakong
            </button>
        </form>

    </div>
    @endforeach
</div>

</body>
</html>