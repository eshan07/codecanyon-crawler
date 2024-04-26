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
    <style>
        /* Add your custom styles here */
        /* You can include additional custom styles if needed */
        body {
            font-family: 'figtree', sans-serif;
            background-color: white; /* Set your default background color */
            color: black; /* Set your default text color */
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
            padding: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .table tfoot {
            font-weight: bold;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background-color: #45a049;
        }
        .dark {
            background-color: black; /* Set your dark mode background color */
            color: white; /* Set your dark mode text color */
        }
    </style>
</head>
<body class="font-sans antialiased dark:bg-black dark:text-white/50">
<div class="container">
    <div style="display: flex;align-items: center;gap: 17px;">
        <a href="{{ url()->previous() }}" class="back-btn">← Back</a>
        <span style="font-size: xx-large;color: lightgray;">|</span>
        <h1>Project-wise Sales for {{ $date }}</h1>
    </div>
    <table class="table">
        <thead>
        <tr>
            <th>Project Name</th>
            <th>Price ($)</th>
            <th>Sales Count</th>
        </tr>
        </thead>
        <tbody>
        @php
            $totalPrice = 0; // Initialize total price variable
        @endphp
        @foreach ($result as $sale)
            <tr>
                <td>{{ $sale['project'] }}</td>
                <td>{{ $sale['price'] }}</td>
                <td>{{ $sale['quantity_sold'] }}</td>
                @php
                    $totalPrice += $sale['price']; // Add the price to the total price
                @endphp
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td>Total </td>
            <td>{{ $totalPrice }}</td>
            <td>{{ collect($result)->sum('quantity_sold') }}</td>
        </tr>
        </tfoot>
    </table>
</div>
</body>
</html>
