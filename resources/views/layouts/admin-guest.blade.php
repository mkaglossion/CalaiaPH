<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin Login')</title>
  <script src="https://cdn.tailwindcss.com"></script>
  @vite(['resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 font-sans">
  @yield('content')
</body>
</html>
