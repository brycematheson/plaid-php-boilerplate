<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>Plaid Test</title>

        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }

            #wrapper {
                width: 100%;
                height: 400px;
            }

            button {
                height: 20px;
                position: relative;
                margin: -20px -50px;
                width: 100px;
                top: 50%;
                left: 50%;
            }

        </style>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    </head>
    <body>
        <div id="wrapper">
            <button type="button" class="link-account">Link Account</button>
        </div>
    </body>
</html>
