<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Add your custom styles here */
        /* You can include additional custom styles if needed */
        .dark {
            background-color: black; /* Set your dark mode background color */
            color: white; /* Set your dark mode text color */
        }
    </style>
</head>
<body class="font-sans antialiased dark:bg-black dark:text-white/50">
<div class="container mx-auto px-4 py-8">
    <h1 class="text-center text-3xl font-bold mb-4">GainHQ Total Sales</h1>
    <div class="w-3/4 mx-auto">
        <table class="w-full bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden text-center">
            <thead class="bg-gray-200 dark:bg-gray-700">
            <tr>
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Codecanyon Sales</th>
                <th class="px-4 py-2">Actual Sales</th>
                <th class="px-4 py-2">New Sales</th>
                <th class="px-4 py-2">Refunds</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($salesByDate as $sale)
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600">
                    <td class="px-4 py-2 text-blue-600 hover:underline cursor-pointer" onclick="navigateTo('{{ route('project-wise-data', ['date' => $sale['date']]) }}')">{{ $sale['date'] }}</td>
                    <td class="px-4 py-2">{{ $sale['total_sale'] }}</td>
                    <td class="px-4 py-2">{{ $sale['actual_sale'] }}</td>
                    <td class="px-4 py-2 text-blue-600 hover:underline cursor-pointer" onclick="navigateTo('{{ route('latest-sold-product', ['date' => $sale['date']]) }}')">{{ $sale['new_sales_count'] }}</td>
                    <td class="px-4 py-2 text-blue-600 hover:underline cursor-pointer" onclick="navigateTo('{{ route('latest-refund-product', ['date' => $sale['date']]) }}')">{{ $sale['refund_count'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <!-- Refresh Button -->
        <div class="flex justify-end mt-4">
            <button onclick="navigateTo('{{ route('sync-data') }}')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <svg class="h-6 w-6 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10c2.34 0 4.47-.82 6.16-2.19l-1.39-1.39C15.02 20.17 13.14 21 12 21c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8h-3l4 4 4-4h-3c0-5.52-4.48-10-10-10z"/>
                </svg>
            </button>
        </div>
    </div>
</div>
</body>
<script>
    function navigateTo(url) {
        window.location.href = url;
    }
</script>
</html>
