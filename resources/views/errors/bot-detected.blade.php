<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Restringido | Bot Protection</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center p-4 font-sans">
    <div class="max-w-md w-full bg-gray-900 border border-gray-800 rounded-2xl p-8 text-center shadow-2xl">
        <div class="w-16 h-16 bg-red-500/10 border border-red-500/20 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
            🛡️
        </div>
        <h1 class="text-2xl font-bold mb-2">Acceso No Autorizado</h1>
        <p class="text-sm text-gray-400 mb-6">
            {{ $reason ?? 'Su solicitud fue identificada como automatizada o sospechosa.' }}
        </p>
        <div class="text-xs text-gray-500 border-t border-gray-800 pt-4">
            Protected by <span class="text-gray-300 font-semibold">Laravel BotProtection</span>
        </div>
    </div>
</body>
</html>
