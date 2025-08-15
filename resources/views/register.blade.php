<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Include Tailwind CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.1/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="flex items-center justify-center h-screen bg-gray-200">
<div class="w-full max-w-xs">
    <form class="px-8 pt-6 pb-8 mb-4 bg-white rounded shadow-md">
        <div class="mb-4">
            <label class="block mb-2 text-sm font-bold text-gray-700" for="username">Username</label>
            <input class="w-full px-3 py-2 text-sm leading-tight text-gray-700 border rounded shadow-md focus:outline-none focus:shadow-outline"
                   id="username" type="text" placeholder="Username">
        </div>
        <div class="mb-6">
            <label class="block mb-2 text-sm font-bold text-gray-700" for="password">Password</label>
            <input class="w-full px-3 py-2 text-sm leading-tight text-gray-700 border rounded shadow-md focus:outline-none focus:shadow-outline" id="password"
                   type="password" placeholder="************">
        </div>
        <div class="flex items-center justify-between">
            <button class="px-4 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700 focus:outline-none focus:shadow-outline"
                    type="button">Log in</button>
            <a class="inline-block text-sm font-bold text-blue-500 hover:text-blue-800" href="#">Forgot Password?</a>
        </div>
    </form>
</div>
</body>
</html>
